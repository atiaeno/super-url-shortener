<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\AffiliateTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffiliateTierFactory extends Factory
{
    protected $model = AffiliateTier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Bronze', 'Silver', 'Gold', 'Platinum']),
            'commission_rate' => $this->faker->randomFloat(2, 0.05, 0.30),
            'visit_threshold' => $this->faker->numberBetween(100, 5000),
            'view_rate' => $this->faker->randomFloat(4, 0.001, 0.01),
            'view_multiplier' => $this->faker->randomFloat(1, 1, 3),
            'is_active' => true,
        ];
    }
}
