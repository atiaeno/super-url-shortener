<?php
// © Atia Hegazy — atiaeno.com

namespace App\Jobs;

use App\Models\Affiliate;
use App\Models\AffiliateCountryRate;
use App\Models\AffiliateTier;
use App\Models\AffiliateVisit;
use App\Models\Click;
use App\Models\Link;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class LogClickJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $linkId,
        private array $clickData,
    ) {}

    public function handle(): void
    {
        Click::create([
            'link_id' => $this->linkId,
            ...$this->clickData,
        ]);

        // Skip affiliate processing if feature is disabled
        $affiliateEnabled = (Setting::where('key', 'features_affiliate')->value('value') ?? 'true') === 'true';
        if (!$affiliateEnabled) {
            return;
        }

        $this->processAffiliateVisit();
    }

    private function processAffiliateVisit(): void
    {
        // Find the affiliate whose account owns this link
        $link = Link::select('user_id')->find($this->linkId);
        if (!$link) {
            return;
        }

        $affiliate = Affiliate::where('user_id', $link->user_id)
            ->where('is_active', true)
            ->first();

        if (!$affiliate) {
            return;
        }

        $ipHash = $this->clickData['ip_hash'] ?? null;
        $countryCode = $this->clickData['country_code'] ?? null;

        if (!$ipHash) {
            return;
        }

        // Find which tier this country belongs to
        $tier = $this->resolveTierForCountry($countryCode);

        // Record unique visit (ignore duplicate IP per affiliate)
        $inserted = DB::table('affiliate_visits')->insertOrIgnore([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $tier?->id,
            'link_id' => $this->linkId,
            'ip_hash' => $ipHash,
            'country_code' => $countryCode,
            'visit_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$inserted) {
            // Already counted this visitor, skip earnings
            return;
        }

        // Increment total visits and recalculate earnings for this tier
        $affiliate->increment('total_visits');

        if (!$tier) {
            // Country not in any tier — no reward
            return;
        }

        // Earn = 1 visit / view_multiplier * view_rate
        $earning = $tier->view_rate / $tier->view_multiplier;

        $affiliate->increment('total_earnings', $earning);
        $affiliate->increment('pending_earnings', $earning);
    }

    private function resolveTierForCountry(?string $countryCode): ?AffiliateTier
    {
        if (!$countryCode) {
            return null;
        }

        $rate = AffiliateCountryRate::with('tier')
            ->where('country_code', strtoupper($countryCode))
            ->whereHas('tier', fn($q) => $q->where('is_active', true))
            ->first();

        return $rate?->tier;
    }
}
