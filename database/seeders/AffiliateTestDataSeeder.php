<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\AffiliateStat;
use App\Models\AffiliateTier;
use App\Models\User;
use Illuminate\Database\Seeder;

class AffiliateTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->info('No user found. Run users seeder first.');
            return;
        }

        $tiers = AffiliateTier::all();
        if ($tiers->isEmpty()) {
            $this->command->info('No tiers found. Run tiers seeder first.');
            return;
        }

        $affiliate = Affiliate::where('user_id', $user->id)->first();
        if (!$affiliate) {
            $affiliate = Affiliate::create([
                'user_id' => $user->id,
                'tier_id' => $tiers->first()->id,
                'referral_code' => 'TEST123',
                'total_earnings' => 0,
                'pending_earnings' => 0,
                'paid_earnings' => 0,
                'total_visits' => 0,
                'is_active' => true,
            ]);
        }

        $affiliate->update([
            'total_earnings' => 250.50,
            'pending_earnings' => 75.25,
            'paid_earnings' => 175.25,
            'total_visits' => 1500,
        ]);

        foreach ($tiers as $tier) {
            $visits = match ($tier->name) {
                'Tier 1' => 800,
                'Tier 2' => 500,
                'Tier 3' => 200,
                default => 100,
            };

            $earnings = match ($tier->name) {
                'Tier 1' => 150.00,
                'Tier 2' => 75.50,
                'Tier 3' => 25.00,
                default => 10.00,
            };

            AffiliateStat::updateOrCreate(
                ['affiliate_id' => $affiliate->id, 'affiliate_tier_id' => $tier->id],
                ['visits' => $visits, 'earnings' => $earnings]
            );
        }

        $this->command->info('Test affiliate data created!');
        $this->command->info("Affiliate ID: {$affiliate->id}");
        $this->command->info("Referral Code: {$affiliate->referral_code}");
        $this->command->info("Total Earnings: {$affiliate->total_earnings}");
        $this->command->info("Total Visits: {$affiliate->total_visits}");
    }
}
