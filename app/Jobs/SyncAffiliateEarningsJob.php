<?php
// © Atia Hegazy — atiaeno.com

namespace App\Jobs;

use App\Models\Affiliate;
use App\Models\AffiliateCountryRate;
use App\Models\AffiliateTier;
use App\Models\AffiliateVisit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncAffiliateEarningsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private ?int $affiliateId = null
    ) {}

    public function handle(): void
    {
        $query = Affiliate::where('is_active', true);

        if ($this->affiliateId) {
            $query->where('id', $this->affiliateId);
        }

        $query->each(function (Affiliate $affiliate) {
            $this->syncForAffiliate($affiliate);
        });
    }

    private function syncForAffiliate(Affiliate $affiliate): void
    {
        // Group unique visits by tier
        $visitRows = AffiliateVisit::where('affiliate_id', $affiliate->id)
            ->selectRaw('affiliate_tier_id, COUNT(*) as visits')
            ->groupBy('affiliate_tier_id')
            ->get();

        $totalEarnings = 0;

        foreach ($visitRows as $row) {
            if (! $row->affiliate_tier_id) {
                continue; // No tier = no earnings
            }

            $tier = AffiliateTier::find($row->affiliate_tier_id);
            if (! $tier || ! $tier->is_active) {
                continue;
            }

            $earned = ($row->visits / $tier->view_multiplier) * $tier->view_rate;
            $totalEarnings += $earned;
        }

        $totalVisits = AffiliateVisit::where('affiliate_id', $affiliate->id)->count();

        // Update totals (keep paid_earnings intact, recalc pending from total - paid)
        $affiliate->total_visits  = $totalVisits;
        $affiliate->total_earnings = round($totalEarnings, 4);
        $affiliate->pending_earnings = round(max(0, $totalEarnings - (float) $affiliate->paid_earnings), 4);
        $affiliate->save();
    }
}
