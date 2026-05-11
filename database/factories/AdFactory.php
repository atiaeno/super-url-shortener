<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Ad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ad>
 */
class AdFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ad::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'format' => fake()->randomElement(['banner', 'interstitial']),
            'placement' => fake()->randomElement([
                'header', 'footer', 'left_side', 'right_side', 
                'before_counter', 'under_counter', 'above_button', 
                'under_button', 'popup', 'redirect'
            ]),
            'content' => fake()->paragraph(),
            'image_path' => fake()->optional()->imageUrl(),
            'target_url' => fake()->optional()->url(),
            'target_countries' => fake()->optional()->randomElements(['US', 'UK', 'CA', 'AU', 'DE', 'FR'], 3),
            'countdown_seconds' => fake()->numberBetween(5, 15),
            'is_active' => true,
        ];
    }
}
