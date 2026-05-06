<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Jobs\SyncAffiliateEarningsJob;
use App\Models\Affiliate;
use App\Models\AffiliateStat;
use App\Models\AffiliateTier;
use App\Models\AffiliateVisit;
use App\Models\Payout;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateController extends Controller
{
    /**
     * Story 4.3: Affiliate enrollment + Story 4.4: Earnings dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $affiliate = Affiliate::with(['payouts' => fn($q) => $q->latest()->limit(10)])
            ->where('user_id', $user->id)
            ->first();

        $tiers = AffiliateTier::active()->with('countryRates')->orderBy('id')->get();

        // Per-tier visit breakdown for the enrolled affiliate
        $visitsByTier = [];
        if ($affiliate) {
            // Use summary table for fast queries
            $stats = AffiliateStat::where('affiliate_id', $affiliate->id)->get()->keyBy('affiliate_tier_id');

            foreach ($tiers as $tier) {
                $stat = $stats->get($tier->id);
                $visits = $stat?->visits ?? 0;
                $earned = $stat?->earnings ?? 0;
                $visitsByTier[] = [
                    'tier_id' => $tier->id,
                    'name' => $tier->name,
                    'visits' => $visits,
                    'rate' => $tier->view_rate,
                    'multiplier' => $tier->view_multiplier,
                    'earned' => round($earned, 4),
                ];
            }
        }

        $payoutMethods = Setting::get('affiliate_payout_methods', 'PayPal');
        $payoutMethods = array_filter(array_map('trim', explode(',', $payoutMethods)));

        return Inertia::render('Affiliate/Dashboard', [
            'affiliate' => $affiliate,
            'tiers' => $tiers,
            'visitsByTier' => $visitsByTier,
            'minPayout' => (float) Setting::get('affiliate_min_payout', 50),
            'payoutMethods' => $payoutMethods,
        ]);
    }

    /**
     * Story 4.3: Enroll user in affiliate program.
     */
    public function enroll(): RedirectResponse
    {
        $user = Auth::user();

        if (Affiliate::where('user_id', $user->id)->exists()) {
            return back()->with('error', 'You are already enrolled.');
        }

        $defaultTier = AffiliateTier::active()->orderBy('visit_threshold')->firstOrFail();

        Affiliate::create([
            'user_id' => $user->id,
            'tier_id' => $defaultTier->id,
            'referral_code' => Affiliate::generateReferralCode(),
        ]);

        return redirect()
            ->route('affiliate.index')
            ->with('success', 'Welcome to the affiliate program!');
    }

    /**
     * Story 4.5: Submit payout request.
     */
    public function requestPayout(Request $request): RedirectResponse
    {
        $affiliate = Affiliate::where('user_id', Auth::id())->firstOrFail();

        $minPayout = (float) Setting::get('affiliate_min_payout', 50);

        if (!$affiliate->canRequestPayout($minPayout)) {
            return back()->with('error', "Minimum payout is \${$minPayout}.");
        }

        if (Payout::where('affiliate_id', $affiliate->id)->pending()->exists()) {
            return back()->with('error', 'You already have a pending payout request.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:100'],
            'payment_email' => ['required', 'string', 'max:255'],
        ]);

        Payout::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $affiliate->pending_earnings,
            'status' => Payout::STATUS_PENDING,
            'payment_method' => $validated['payment_method'],
            'payment_email' => $validated['payment_email'],
        ]);

        // Deduct from pending_earnings
        $affiliate->update([
            'pending_earnings' => 0,
        ]);

        return back()->with('success', 'Payout request submitted. We will review it shortly.');
    }

    /**
     * Manually trigger earnings sync for the authenticated affiliate.
     */
    public function sync(): RedirectResponse
    {
        $affiliate = Affiliate::where('user_id', Auth::id())->firstOrFail();

        SyncAffiliateEarningsJob::dispatch($affiliate->id);

        return back()->with('success', 'Earnings sync queued. Refresh in a moment.');
    }

    /**
     * Story 4.6: Payout history.
     */
    public function payouts(): Response
    {
        $affiliate = Affiliate::where('user_id', Auth::id())->firstOrFail();

        $payouts = Payout::with('processedBy:id,name')
            ->where('affiliate_id', $affiliate->id)
            ->latest()
            ->paginate(15);

        return Inertia::render('Affiliate/Payouts', [
            'affiliate' => $affiliate,
            'payouts' => $payouts,
        ]);
    }
}
