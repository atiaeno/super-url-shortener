<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\IndexerSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IndexerSetting>
 */
class IndexerSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = IndexerSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enabled' => fake()->boolean(),
            'google_service_account_json' => fake()->optional()->sentence(),
            'google_daily_limit' => fake()->numberBetween(100, 1000),
            'google_links_sent_today' => fake()->numberBetween(0, 100),
            'google_links_sent_date' => fake()->date(),
            'indexnow_enabled' => fake()->boolean(),
            'indexnow_key' => fake()->optional()->sha256(),
            'xml_ping_enabled' => fake()->boolean(),
            'ping_services' => json_encode(fake()->randomElements(['google', 'bing', 'yandex', 'yahoo', 'duckduckgo', 'baidu', 'naver'], 3)),
            'links_per_batch' => fake()->numberBetween(10, 100),
            'last_offset' => fake()->numberBetween(0, 1000),
            'last_run' => fake()->optional()->dateTime(),
            'next_run' => fake()->optional()->dateTime(),
        ];
    }
}
