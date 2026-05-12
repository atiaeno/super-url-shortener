<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\ReferralCommission;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralCommissionFactory extends Factory
{
    protected $model = ReferralCommission::class;

    public function definition(): array
    {
        return [
            'referrer_affiliate_id' => null,
            'referral_affiliate_id' => null,
            'referral_earnings' => fake()->randomFloat(4, 0, 1000),
            'commission_amount' => fake()->randomFloat(4, 0, 20),
            'commission_rate' => fake()->randomFloat(2, 0.5, 2.0),
            'commission_date' => fake()->date(),
        ];
    }
}
