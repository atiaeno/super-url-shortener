<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word(),
            'value' => fake()->sentence(),
            'group' => fake()->randomElement(['general', 'ads', 'affiliate', 'indexing', 'security']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
