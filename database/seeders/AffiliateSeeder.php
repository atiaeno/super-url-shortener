<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\AffiliateCountryRate;
use App\Models\AffiliateTier;
use App\Models\AffiliateVisit;
use App\Models\Link;
use App\Models\Payout;
use App\Models\PayoutAuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AffiliateSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data (delete in reverse order due to FK constraints)
        PayoutAuditLog::query()->delete();
        Payout::query()->delete();
        AffiliateVisit::query()->delete();
        AffiliateCountryRate::query()->delete();
        Affiliate::query()->delete();
        AffiliateTier::query()->delete();

        // Create affiliate tiers (by country, not by user tier)
        // Tier 1: High-paying countries - $3 per 10k visits
        $tier1 = AffiliateTier::create([
            'name' => 'Tier 1',
            'visit_threshold' => 0,
            'commission_rate' => null,
            'view_rate' => 3.0,
            'view_multiplier' => 10000,
            'is_active' => true,
        ]);

        // Tier 2: Medium-paying countries - $1 per 10k visits
        $tier2 = AffiliateTier::create([
            'name' => 'Tier 2',
            'visit_threshold' => 0,
            'commission_rate' => null,
            'view_rate' => 1.0,
            'view_multiplier' => 10000,
            'is_active' => true,
        ]);

        // Tier 3: Low-paying countries - $0.50 per 10k visits
        $tier3 = AffiliateTier::create([
            'name' => 'Tier 3',
            'visit_threshold' => 0,
            'commission_rate' => null,
            'view_rate' => 0.5,
            'view_multiplier' => 10000,
            'is_active' => true,
        ]);

        // Tier 1 countries (high-paying)
        $tier1Countries = ['US', 'CA', 'GB', 'AU', 'DE', 'FR', 'NL', 'SE', 'NO', 'DK', 'FI', 'CH', 'AT', 'BE', 'IE', 'NZ', 'JP', 'SG', 'HK', 'IT', 'ES'];
        foreach ($tier1Countries as $country) {
            AffiliateCountryRate::create([
                'affiliate_tier_id' => $tier1->id,
                'country_code' => $country,
                'commission_rate' => 3.0,
            ]);
        }

        // Tier 2 countries (medium-paying)
        $tier2Countries = ['BR', 'MX', 'IN', 'ID', 'PH', 'TH', 'VN', 'MY', 'AR', 'CO', 'CL', 'PE', 'KR', 'TW', 'RU', 'TR', 'PL', 'PT', 'GR', 'HU'];
        foreach ($tier2Countries as $country) {
            AffiliateCountryRate::create([
                'affiliate_tier_id' => $tier2->id,
                'country_code' => $country,
                'commission_rate' => 1.0,
            ]);
        }

        // Tier 3 = all other countries (no entry needed, falls back to tier default)

        $this->command->info('Created 3 affiliate tiers by country:');
        $this->command->info('- Tier 1: ' . count($tier1Countries) . ' countries @ $3/10k visits');
        $this->command->info('- Tier 2: ' . count($tier2Countries) . ' countries @ $1/10k visits');
        $this->command->info('- Tier 3: All other countries @ $0.50/10k visits');

        // Create affiliates
        $affiliateUser = User::where('email', 'affiliate@example.com')->first();
        $regularUser = User::where('email', 'john@example.com')->first();

        $aff1 = Affiliate::create([
            'user_id' => $affiliateUser->id,
            'referral_code' => 'AFF001',
            'total_earnings' => 1250.5,
            'pending_earnings' => 150.0,
            'paid_earnings' => 1100.5,
            'total_visits' => 50000,
            'is_active' => true,
        ]);

        $aff2 = Affiliate::create([
            'user_id' => $regularUser->id,
            'referral_code' => 'AFF002',
            'total_earnings' => 45.0,
            'pending_earnings' => 12.5,
            'paid_earnings' => 32.5,
            'total_visits' => 800,
            'is_active' => true,
        ]);

        // Create sample affiliate visits for aff1 (50k visits across tiers)
        $link = Link::where('user_id', $affiliateUser->id)->first();
        if ($link) {
            // Tier 1 visits: 20,000 (US, CA, GB, etc.)
            for ($i = 0; $i < 20000; $i++) {
                AffiliateVisit::create([
                    'affiliate_id' => $aff1->id,
                    'affiliate_tier_id' => $tier1->id,
                    'link_id' => $link->id,
                    'ip_hash' => Str::random(64),
                    'country_code' => $tier1Countries[array_rand($tier1Countries)],
                    'visit_date' => now()->subDays(rand(0, 30)),
                ]);
            }

            // Tier 2 visits: 15,000 (BR, MX, IN, etc.)
            for ($i = 0; $i < 15000; $i++) {
                AffiliateVisit::create([
                    'affiliate_id' => $aff1->id,
                    'affiliate_tier_id' => $tier2->id,
                    'link_id' => $link->id,
                    'ip_hash' => Str::random(64),
                    'country_code' => $tier2Countries[array_rand($tier2Countries)],
                    'visit_date' => now()->subDays(rand(0, 30)),
                ]);
            }

            // Tier 3 visits: 15,000 (other countries)
            for ($i = 0; $i < 15000; $i++) {
                AffiliateVisit::create([
                    'affiliate_id' => $aff1->id,
                    'affiliate_tier_id' => $tier3->id,
                    'link_id' => $link->id,
                    'ip_hash' => Str::random(64),
                    'country_code' => ['ZA', 'NG', 'EG', 'PK', 'BD'][array_rand(['ZA', 'NG', 'EG', 'PK', 'BD'])],
                    'visit_date' => now()->subDays(rand(0, 30)),
                ]);
            }
        }

        // Create sample visits for aff2 (800 visits)
        $link2 = Link::where('user_id', $regularUser->id)->first();
        if ($link2) {
            for ($i = 0; $i < 800; $i++) {
                $tier = [1 => $tier1, $tier2, $tier3][rand(1, 3)];
                AffiliateVisit::create([
                    'affiliate_id' => $aff2->id,
                    'affiliate_tier_id' => $tier->id,
                    'link_id' => $link2->id,
                    'ip_hash' => Str::random(64),
                    'country_code' => $tier->id == $tier1->id
                        ? $tier1Countries[array_rand($tier1Countries)]
                        : ($tier->id == $tier2->id ? $tier2Countries[array_rand($tier2Countries)] : 'PK'),
                    'visit_date' => now()->subDays(rand(0, 30)),
                ]);
            }
        }

        // Create payout requests
        $payout1 = Payout::create([
            'affiliate_id' => $aff1->id,
            'amount' => 500.0,
            'status' => 'completed',
            'paypal_email' => 'affiliate@paypal.com',
            'processed_at' => now()->subDays(10),
            'processed_by' => 1,
        ]);

        PayoutAuditLog::create([
            'payout_id' => $payout1->id,
            'old_status' => 'pending',
            'new_status' => 'completed',
            'actor_id' => 1,
        ]);

        $payout2 = Payout::create([
            'affiliate_id' => $aff1->id,
            'amount' => 600.5,
            'status' => 'completed',
            'paypal_email' => 'affiliate@paypal.com',
            'processed_at' => now()->subDays(5),
            'processed_by' => 1,
        ]);

        PayoutAuditLog::create([
            'payout_id' => $payout2->id,
            'old_status' => 'pending',
            'new_status' => 'completed',
            'actor_id' => 1,
        ]);

        // Pending payout
        Payout::create([
            'affiliate_id' => $aff2->id,
            'amount' => 12.5,
            'status' => 'pending',
            'paypal_email' => 'john@paypal.com',
        ]);

        // Rejected payout
        Payout::create([
            'affiliate_id' => $aff1->id,
            'amount' => 1000.0,
            'status' => 'rejected',
            'paypal_email' => 'invalid@email.com',
            'admin_note' => 'Invalid PayPal email address',
            'processed_at' => now()->subDays(3),
            'processed_by' => 1,
        ]);

        $this->command->info('Created 2 affiliates with sample visits and payouts');
    }
}
