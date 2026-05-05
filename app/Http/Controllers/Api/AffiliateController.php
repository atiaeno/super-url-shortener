<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateTier;
use App\Models\AffiliateVisit;
use App\Models\Payout;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{
    /**
     * Check if affiliate program is enabled.
     */
    private function isEnabled(): bool
    {
        return (bool) Setting::get('affiliate_enabled', true);
    }

    /**
     * GET /api/v1/affiliate - Get affiliate profile and stats
     */
    public function index(): JsonResponse
    {
        if (!$this->isEnabled()) {
            return response()->json([
                'error' => 'Affiliate program is disabled',
                'message' => 'The affiliate program is currently not available.',
            ], 403);
        }

        $user = Auth::user();
        $affiliate = Affiliate::with(['tier', 'payouts' => fn($q) => $q->latest()->limit(10)])
            ->where('user_id', $user->id)
            ->first();

        if (!$affiliate) {
            return response()->json([
                'enrolled' => false,
                'message' => 'Not enrolled in affiliate program',
                'tiers' => AffiliateTier::active()->with('countryRates')->orderBy('id')->get()->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'commission_rate' => $t->commission_rate,
                    'visit_threshold' => $t->visit_threshold,
                ]),
            ]);
        }

        $tiers = AffiliateTier::active()->orderBy('id')->get();
        $rows = AffiliateVisit::where('affiliate_id', $affiliate->id)
            ->selectRaw('affiliate_tier_id, COUNT(*) as visits')
            ->groupBy('affiliate_tier_id')
            ->get()
            ->keyBy('affiliate_tier_id');

        $visitsByTier = [];
        foreach ($tiers as $tier) {
            $visits = $rows->get($tier->id)?->visits ?? 0;
            $visitsByTier[] = [
                'tier_id' => $tier->id,
                'name' => $tier->name,
                'visits' => $visits,
                'rate' => $tier->view_rate,
                'multiplier' => $tier->view_multiplier,
                'earned' => round(($visits / $tier->view_multiplier) * $tier->view_rate, 4),
            ];
        }

        return response()->json([
            'enrolled' => true,
            'affiliate' => [
                'id' => $affiliate->id,
                'referral_code' => $affiliate->referral_code,
                'tier' => $affiliate->tier?->name,
                'total_earnings' => (float) $affiliate->total_earnings,
                'pending_earnings' => (float) $affiliate->pending_earnings,
                'paid_earnings' => (float) $affiliate->paid_earnings,
                'total_visits' => $affiliate->total_visits,
                'is_active' => $affiliate->is_active,
                'created_at' => $affiliate->created_at->toIso8601String(),
            ],
            'visits_by_tier' => $visitsByTier,
            'min_payout' => (float) Setting::get('affiliate_min_payout', 50),
            'payout_methods' => array_filter(array_map('trim', explode(',', Setting::get('affiliate_payout_methods', 'PayPal')))),
        ]);
    }

    /**
     * POST /api/v1/affiliate/enroll - Enroll in affiliate program
     */
    public function enroll(): JsonResponse
    {
        if (!$this->isEnabled()) {
            return response()->json([
                'error' => 'Affiliate program is disabled',
                'message' => 'The affiliate program is currently not available.',
            ], 403);
        }

        $user = Auth::user();

        if (Affiliate::where('user_id', $user->id)->exists()) {
            return response()->json([
                'error' => 'Already enrolled',
                'message' => 'You are already enrolled in the affiliate program.',
            ], 409);
        }

        $defaultTier = AffiliateTier::active()->orderBy('visit_threshold')->firstOrFail();

        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'tier_id' => $defaultTier->id,
            'referral_code' => Affiliate::generateReferralCode(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in affiliate program',
            'affiliate' => [
                'id' => $affiliate->id,
                'referral_code' => $affiliate->referral_code,
                'tier' => $defaultTier->name,
                'total_earnings' => 0,
                'pending_earnings' => 0,
                'paid_earnings' => 0,
                'total_visits' => 0,
            ],
        ], 201);
    }

    /**
     * POST /api/v1/affiliate/payout - Request payout
     */
    public function requestPayout(Request $request): JsonResponse
    {
        if (!$this->isEnabled()) {
            return response()->json([
                'error' => 'Affiliate program is disabled',
                'message' => 'The affiliate program is currently not available.',
            ], 403);
        }

        $affiliate = Affiliate::where('user_id', Auth::id())->first();

        if (!$affiliate) {
            return response()->json([
                'error' => 'Not enrolled',
                'message' => 'You must enroll in the affiliate program first.',
            ], 403);
        }

        $minPayout = (float) Setting::get('affiliate_min_payout', 50);

        if (!$affiliate->canRequestPayout($minPayout)) {
            return response()->json([
                'error' => 'Insufficient balance',
                'message' => "Minimum payout is \${$minPayout}. Current pending: \${$affiliate->pending_earnings}",
            ], 422);
        }

        if (Payout::where('affiliate_id', $affiliate->id)->pending()->exists()) {
            return response()->json([
                'error' => 'Pending payout exists',
                'message' => 'You already have a pending payout request.',
            ], 409);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:100'],
            'payment_email' => ['required', 'string', 'max:255'],
        ]);

        $payout = Payout::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $affiliate->pending_earnings,
            'status' => Payout::STATUS_PENDING,
            'payment_method' => $validated['payment_method'],
            'payment_email' => $validated['payment_email'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payout request submitted successfully',
            'payout' => [
                'id' => $payout->id,
                'amount' => (float) $payout->amount,
                'status' => $payout->status,
                'payment_method' => $payout->payment_method,
                'created_at' => $payout->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * GET /api/v1/affiliate/payouts - Get payout history
     */
    public function payouts(Request $request): JsonResponse
    {
        if (!$this->isEnabled()) {
            return response()->json([
                'error' => 'Affiliate program is disabled',
                'message' => 'The affiliate program is currently not available.',
            ], 403);
        }

        $affiliate = Affiliate::where('user_id', Auth::id())->first();

        if (!$affiliate) {
            return response()->json([
                'error' => 'Not enrolled',
                'message' => 'You must enroll in the affiliate program first.',
            ], 403);
        }

        $payouts = Payout::where('affiliate_id', $affiliate->id)
            ->latest()
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => $payouts->items(),
            'meta' => [
                'current_page' => $payouts->currentPage(),
                'total_pages' => $payouts->lastPage(),
                'total_count' => $payouts->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/affiliate/tiers - List available tiers
     */
    public function tiers(): JsonResponse
    {
        if (!$this->isEnabled()) {
            return response()->json([
                'error' => 'Affiliate program is disabled',
                'message' => 'The affiliate program is currently not available.',
            ], 403);
        }

        $tiers = AffiliateTier::active()->with('countryRates')->orderBy('id')->get();

        return response()->json([
            'tiers' => $tiers->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'commission_rate' => $t->commission_rate,
                'visit_threshold' => $t->visit_threshold,
                'view_rate' => $t->view_rate,
                'view_multiplier' => $t->view_multiplier,
                'countries' => $t->countryRates->map(fn($c) => [
                    'country_code' => $c->country_code,
                    'multiplier' => $c->multiplier,
                ]),
            ]),
        ]);
    }
}
