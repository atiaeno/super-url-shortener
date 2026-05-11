<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\AffiliateTier;
use App\Models\AffiliateVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffiliateVisitFactory extends Factory
{
    protected $model = AffiliateVisit::class;

    public function definition(): array
    {
        return [
            'affiliate_id' => null,
            'affiliate_tier_id' => null,
            'ip_hash' => fake()->unique()->sha256(),
            'country_code' => fake()->randomElement(['US', 'UK', 'CA', 'AU', 'DE', 'FR']),
            'link_id' => null,
            'visit_date' => fake()->date(),
        ];
    }
}
