<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\AffiliateTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffiliateFactory extends Factory
{
    protected $model = Affiliate::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'tier_id' => AffiliateTier::factory(),
            'referral_code' => strtoupper($this->faker->unique()->bothify('??????####')),
            'total_earnings' => 0,
            'pending_earnings' => 0,
            'paid_earnings' => 0,
            'total_visits' => 0,
            'is_active' => true,
        ];
    }
}
