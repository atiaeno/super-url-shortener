<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'short_code' => $this->faker->unique()->regexify('[a-z0-9]{8}'),
            'destination_url' => $this->faker->url(),
            'custom_alias' => null,
            'campaign_tag' => null,
            'og_title' => null,
            'og_description' => null,
            'og_image' => null,
            'is_active' => true,
            'expires_at' => null,
            'clicks_count' => 0,
            'report_count' => 0,
            'auto_suspended_at' => null,
            'ad_id' => null,
            'ad_override' => 'inherit',
            'visibility' => 'public',
            'password' => null,
            'domain_id' => null,
        ];
    }

    public function withAlias(string $alias): static
    {
        return $this->state(fn(array $attributes) => [
            'short_code' => $alias,
            'custom_alias' => $alias,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function withClicks(int $count): static
    {
        return $this->state(fn(array $attributes) => [
            'clicks_count' => $count,
        ]);
    }
}
