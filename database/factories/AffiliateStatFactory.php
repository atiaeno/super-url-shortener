<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\AffiliateStat;
use App\Models\AffiliateTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffiliateStatFactory extends Factory
{
    protected $model = AffiliateStat::class;

    public function definition(): array
    {
        return [
            'affiliate_id' => 1,
            'affiliate_tier_id' => 1,
            'visits' => $this->faker->numberBetween(0, 1000),
            'earnings' => $this->faker->randomFloat(4, 0, 100),
        ];
    }
}
