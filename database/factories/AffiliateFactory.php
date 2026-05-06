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
            'total_earnings' => $this->faker->randomFloat(2, 0, 1000),
            'pending_earnings' => $this->faker->randomFloat(2, 0, 500),
            'paid_earnings' => $this->faker->randomFloat(2, 0, 500),
            'total_visits' => $this->faker->numberBetween(0, 10000),
            'is_active' => true,
        ];
    }
}
