<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\Payout;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayoutFactory extends Factory
{
    protected $model = Payout::class;

    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => $this->faker->randomElement([
                Payout::STATUS_PENDING,
                Payout::STATUS_APPROVED,
                Payout::STATUS_REJECTED,
                Payout::STATUS_PAID,
            ]),
            'paypal_email' => $this->faker->safeEmail(),
            'admin_note' => null,
            'processed_by' => null,
            'processed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Payout::STATUS_PENDING,
        ]);
    }
}
