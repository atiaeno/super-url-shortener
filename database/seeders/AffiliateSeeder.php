<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\AffiliateCountryRate;
use App\Models\AffiliateTier;
use App\Models\Payout;
use App\Models\PayoutAuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AffiliateSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data (delete in reverse order due to FK constraints)
        PayoutAuditLog::query()->delete();
        Payout::query()->delete();
        AffiliateCountryRate::query()->delete();
        Affiliate::query()->delete();
        AffiliateTier::query()->delete();

        // Create view-based affiliate tiers
        // Low Tier: $1 per 10,000 unique views
        $lowTier = AffiliateTier::create([
            'name' => 'Starter',
            'visit_threshold' => 0,
            'commission_rate' => null,  // Not used for view-based
            'view_rate' => 1.0,
            'view_multiplier' => 10000,
            'is_active' => true,
        ]);

        // Middle Tier: $2 per 10,000 unique views
        $midTier = AffiliateTier::create([
            'name' => 'Pro',
            'visit_threshold' => 5000,
            'commission_rate' => null,
            'view_rate' => 2.0,
            'view_multiplier' => 10000,
            'is_active' => true,
        ]);

        // Top Tier: $3 per 10,000 unique views
        $topTier = AffiliateTier::create([
            'name' => 'Elite',
            'visit_threshold' => 50000,
            'commission_rate' => null,
            'view_rate' => 3.0,
            'view_multiplier' => 10000,
            'is_active' => true,
        ]);

        // High-paying countries (Tier 1) - 1.5x rate
        $tier1Countries = ['US', 'CA', 'GB', 'AU', 'DE', 'FR', 'NL', 'SE', 'NO', 'DK', 'FI', 'CH', 'AT', 'BE', 'IE', 'NZ', 'JP', 'SG', 'HK'];

        // Medium-paying countries (Tier 2) - 1.2x rate
        $tier2Countries = ['ES', 'IT', 'PT', 'PL', 'CZ', 'SK', 'HU', 'SI', 'HR', 'LT', 'LV', 'EE', 'BG', 'RO', 'GR', 'MT', 'CY', 'LU', 'IS', 'LI'];

        // Create country rates for all tiers
        foreach ([$lowTier, $midTier, $topTier] as $tier) {
            // Tier 1 countries get 1.5x the base rate
            foreach ($tier1Countries as $country) {
                AffiliateCountryRate::create([
                    'affiliate_tier_id' => $tier->id,
                    'country_code' => $country,
                    'commission_rate' => round($tier->view_rate * 1.5, 2),
                ]);
            }

            // Tier 2 countries get 1.2x the base rate
            foreach ($tier2Countries as $country) {
                AffiliateCountryRate::create([
                    'affiliate_tier_id' => $tier->id,
                    'country_code' => $country,
                    'commission_rate' => round($tier->view_rate * 1.2, 2),
                ]);
            }

            // Rest of world gets base rate (no entry needed, falls back to tier default)
        }

        $this->command->info('Created 3 affiliate tiers with view-based rates:');
        $this->command->info('- Starter: $1.00 per 10k views ($1.50 Tier1, $1.20 Tier2)');
        $this->command->info('- Pro: $2.00 per 10k views ($3.00 Tier1, $2.40 Tier2)');
        $this->command->info('- Elite: $3.00 per 10k views ($4.50 Tier1, $3.60 Tier2)');
        $this->command->info('Country rates created for ' . (count($tier1Countries) + count($tier2Countries)) . ' countries per tier');

        // Create affiliates
        $affiliateUser = User::where('email', 'affiliate@example.com')->first();
        $regularUser = User::where('email', 'john@example.com')->first();

        Affiliate::create([
            'user_id' => $affiliateUser->id,
            'tier_id' => $topTier->id,
            'referral_code' => 'AFF001',
            'total_earnings' => 1250.5,
            'pending_earnings' => 150.0,
            'total_visits' => 50000,
            'is_active' => true,
        ]);

        Affiliate::create([
            'user_id' => $regularUser->id,
            'tier_id' => $lowTier->id,
            'referral_code' => 'AFF002',
            'total_earnings' => 45.0,
            'pending_earnings' => 12.5,
            'total_visits' => 800,
            'is_active' => true,
        ]);

        // Create affiliate records first to get IDs
        $aff1 = Affiliate::where('referral_code', 'AFF001')->first();
        $aff2 = Affiliate::where('referral_code', 'AFF002')->first();

        // Create payout requests
        $payout = Payout::create([
            'affiliate_id' => $aff1->id,
            'amount' => 250.0,
            'status' => 'completed',
            'paypal_email' => 'affiliate@paypal.com',
            'processed_at' => now()->subDays(10),
            'processed_by' => 1,
        ]);

        // Add audit log for payout
        PayoutAuditLog::create([
            'payout_id' => $payout->id,
            'old_status' => 'pending',
            'new_status' => 'completed',
            'actor_id' => 1,
        ]);

        // Pending payout
        Payout::create([
            'affiliate_id' => $aff2->id,
            'amount' => 45.0,
            'status' => 'pending',
            'paypal_email' => 'john@paypal.com',
        ]);

        // Rejected payout
        Payout::create([
            'affiliate_id' => $aff1->id,
            'amount' => 500.0,
            'status' => 'rejected',
            'paypal_email' => 'invalid@email.com',
            'admin_note' => 'Invalid PayPal email address',
            'processed_at' => now()->subDays(5),
            'processed_by' => 1,
        ]);
    }
}
