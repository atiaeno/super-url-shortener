<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\IndexerLog;
use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

class IndexerLogFactory extends Factory
{
    protected $model = IndexerLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'service' => fake()->randomElement(['google', 'bing', 'yandex', 'yahoo', 'duckduckgo', 'baidu']),
            'type' => fake()->randomElement(['auto', 'manual', 'indexnow']),
            'url' => fake()->url(),
            'response_status' => fake()->randomElement(['success', 'failed', 'timeout']),
            'response_message' => fake()->optional()->sentence(),
            'request_url' => fake()->url(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
