<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\AliasDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

class AliasDomainFactory extends Factory
{
    protected $model = AliasDomain::class;

    public function definition(): array
    {
        return [
            'domain' => $this->faker->domainName(),
            'is_active' => true,
            'is_default' => false,
            'description' => $this->faker->sentence(),
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function default(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
