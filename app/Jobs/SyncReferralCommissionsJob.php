<?php
// © Atia Hegazy — atiaeno.com

namespace App\Jobs;

use App\Models\Affiliate;
use App\Models\ReferralCommission;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncReferralCommissionsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private ?string $commissionDate = null
    ) {
        $this->commissionDate = $commissionDate ?? now()->toDateString();
    }

    public function handle(): void
    {
        $commissionRate = (float) Setting::get('referral_commission_rate', 1.5);
        
        if ($commissionRate <= 0 || $commissionRate > 2) {
            Log::warning('Invalid referral commission rate: ' . $commissionRate);
            return;
        }

        // Get all affiliates who have referred users
        $referrers = Affiliate::where('is_active', true)
            ->whereHas('referredUsers')
            ->with(['referredUsers.affiliate'])
            ->get();

        foreach ($referrers as $referrer) {
            $this->processReferrerCommissions($referrer, $commissionRate);
        }

        Log::info('Processed referral commissions for date: ' . $this->commissionDate);
    }

    private function processReferrerCommissions(Affiliate $referrer, float $commissionRate): void
    {
        $totalReferralEarnings = 0;
        $totalCommissionAmount = 0;

        foreach ($referrer->referredUsers as $referredUser) {
            $referralAffiliate = $referredUser->affiliate;
            
            if (!$referralAffiliate || !$referralAffiliate->is_active) {
                continue;
            }

            // Calculate referral's earnings for the commission date
            $referralEarnings = $this->getReferralEarningsForDate($referralAffiliate, $this->commissionDate);
            
            if ($referralEarnings <= 0) {
                continue;
            }

            $commissionAmount = ($referralEarnings * $commissionRate) / 100;
            
            // Create commission record
            ReferralCommission::create([
                'referrer_affiliate_id' => $referrer->id,
                'referral_affiliate_id' => $referralAffiliate->id,
                'referral_earnings' => $referralEarnings,
                'commission_amount' => $commissionAmount,
                'commission_rate' => $commissionRate,
                'commission_date' => $this->commissionDate,
            ]);

            $totalReferralEarnings += $referralEarnings;
            $totalCommissionAmount += $commissionAmount;
        }

        // Update referrer's referral earnings
        if ($totalCommissionAmount > 0) {
            $referrer->referral_earnings += $totalCommissionAmount;
            $referrer->referral_pending_earnings += $totalCommissionAmount;
            $referrer->save();
        }
    }

    private function getReferralEarningsForDate(Affiliate $affiliate, string $date): float
    {
        // Check if we already have a commission record for this date
        $existingCommission = ReferralCommission::where('referral_affiliate_id', $affiliate->id)
            ->where('commission_date', $date)
            ->first();

        if ($existingCommission) {
            return $existingCommission->referral_earnings;
        }

        // Calculate earnings based on affiliate visits for the date
        // This is a simplified calculation - in production you might want to track daily earnings
        $dailyVisits = $affiliate->affiliateVisits()
            ->whereDate('created_at', $date)
            ->count();

        if ($dailyVisits === 0) {
            return 0;
        }

        // Use the affiliate's tier rate to calculate earnings
        $tier = $affiliate->tier;
        if (!$tier || !$tier->is_active) {
            return 0;
        }

        // Calculate earnings based on visits and tier rate
        $earnings = ($dailyVisits / $tier->view_multiplier) * $tier->view_rate;
        
        return round($earnings, 4);
    }
}
