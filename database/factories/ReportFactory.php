<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Link;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'reason' => fake()->randomElement(['spam', 'phishing', 'malware', 'violence', 'other']),
            'details' => fake()->optional()->sentence(),
            'reporter_ip_hash' => fake()->optional()->sha256(),
            'status' => fake()->randomElement(['pending', 'reviewed', 'dismissed', 'actioned']),
            'reviewed_by' => User::factory(),
            'reviewed_at' => fake()->optional()->dateTime(),
            'admin_notes' => fake()->optional()->sentence(),
        ];
    }
}
