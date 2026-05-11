<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\LinkAnalyticsDaily;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LinkAnalyticsDaily>
 */
class LinkAnalyticsDailyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LinkAnalyticsDaily::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_id' => 1,  // Will be overridden in tests
            'date' => fake()->date(),
            'total_clicks' => fake()->numberBetween(1, 1000),
            'unique_visitors' => fake()->numberBetween(1, 500),
            'by_country' => fake()->randomElements([
                'US' => fake()->numberBetween(1, 100),
                'UK' => fake()->numberBetween(1, 50),
                'CA' => fake()->numberBetween(1, 30),
                'AU' => fake()->numberBetween(1, 20),
            ], 3),
            'by_device' => [
                'desktop' => fake()->numberBetween(1, 100),
                'mobile' => fake()->numberBetween(1, 100),
                'tablet' => fake()->numberBetween(1, 50),
            ],
            'by_browser' => [
                'Chrome' => fake()->numberBetween(1, 100),
                'Firefox' => fake()->numberBetween(1, 50),
                'Safari' => fake()->numberBetween(1, 50),
                'Edge' => fake()->numberBetween(1, 30),
            ],
            'by_referrer' => [
                'google.com' => fake()->numberBetween(1, 100),
                'facebook.com' => fake()->numberBetween(1, 50),
                'twitter.com' => fake()->numberBetween(1, 30),
                'direct' => fake()->numberBetween(1, 50),
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
