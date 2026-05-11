<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\AffiliateCountryRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AffiliateCountryRate>
 */
class AffiliateCountryRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AffiliateCountryRate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'affiliate_tier_id' => \App\Models\AffiliateTier::factory(),
            'country_code' => fake()->randomElement(['US', 'UK', 'CA', 'AU', 'DE', 'FR']),
            'commission_rate' => fake()->randomFloat(2, 0.01, 0.5),
        ];
    }
}
