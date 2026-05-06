<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature\Api;

use App\Models\Affiliate;
use App\Models\AffiliateStat;
use App\Models\AffiliateTier;
use App\Models\ApiToken;
use App\Models\Payout;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ApiToken $token;
    private string $authHeader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = ApiToken::factory()->create(['user_id' => $this->user->id]);
        $this->authHeader = 'Bearer ' . $this->token->token;
    }

    /**
     * @test
     */
    public function can_get_affiliate_info_when_not_enrolled(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/affiliate');

        $response
            ->assertStatus(200)
            ->assertJsonPath('enrolled', false)
            ->assertJsonStructure([
                'enrolled',
                'message',
                'tiers' => [
                    '*' => ['id', 'name', 'commission_rate', 'visit_threshold'],
                ],
            ]);
    }

    /**
     * @test
     */
    public function can_enroll_in_affiliate_program(): void
    {
        AffiliateTier::factory()->create(['is_active' => true]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/affiliate/enroll');

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('enrolled', true)
            ->assertJsonStructure([
                'success',
                'message',
                'affiliate' => ['id', 'referral_code', 'tier', 'total_earnings'],
            ]);

        $this->assertDatabaseHas('affiliates', ['user_id' => $this->user->id]);
    }

    /**
     * @test
     */
    public function cannot_enroll_twice(): void
    {
        $tier = AffiliateTier::factory()->create(['is_active' => true]);
        Affiliate::factory()->create(['user_id' => $this->user->id, 'tier_id' => $tier->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/affiliate/enroll');

        $response
            ->assertStatus(409)
            ->assertJsonPath('error', 'Already enrolled');
    }

    /**
     * @test
     */
    public function can_get_affiliate_info_when_enrolled(): void
    {
        $tier = AffiliateTier::factory()->create(['is_active' => true]);
        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $tier->id,
            'total_earnings' => 100,
            'pending_earnings' => 50,
            'paid_earnings' => 50,
            'total_visits' => 500,
        ]);

        AffiliateStat::factory()->create([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $tier->id,
            'visits' => 500,
            'earnings' => 100,
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/affiliate');

        $response
            ->assertStatus(200)
            ->assertJsonPath('enrolled', true)
            ->assertJsonPath('affiliate.total_earnings', 100)
            ->assertJsonPath('affiliate.total_visits', 500)
            ->assertJsonStructure([
                'enrolled',
                'affiliate' => ['id', 'referral_code', 'tier', 'total_earnings', 'pending_earnings', 'paid_earnings', 'total_visits', 'is_active', 'created_at'],
                'visits_by_tier',
                'min_payout',
                'payout_methods',
            ]);
    }

    /**
     * @test
     */
    public function can_get_tiers(): void
    {
        AffiliateTier::factory()->count(3)->create(['is_active' => true]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/affiliate/tiers');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'tiers')
            ->assertJsonStructure([
                'tiers' => [
                    '*' => ['id', 'name', 'commission_rate', 'visit_threshold', 'view_rate', 'view_multiplier', 'countries'],
                ],
            ]);
    }

    /**
     * @test
     */
    public function can_request_payout(): void
    {
        $tier = AffiliateTier::factory()->create(['is_active' => true]);
        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $tier->id,
            'pending_earnings' => 100,
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/affiliate/payout', [
                'payment_email' => 'test@example.com',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'payout' => ['id', 'amount', 'status', 'payment_email', 'created_at'],
            ]);

        $this->assertDatabaseHas('payouts', [
            'affiliate_id' => $affiliate->id,
            'amount' => 100,
            'status' => Payout::STATUS_PENDING,
        ]);
    }

    /**
     * @test
     */
    public function cannot_request_payout_when_not_enrolled(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/affiliate/payout', [
                'payment_email' => 'test@example.com',
            ]);

        $response
            ->assertStatus(403)
            ->assertJsonPath('error', 'Not enrolled');
    }

    /**
     * @test
     */
    public function cannot_request_payout_below_minimum(): void
    {
        $tier = AffiliateTier::factory()->create(['is_active' => true]);
        Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $tier->id,
            'pending_earnings' => 10,
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/affiliate/payout', [
                'payment_email' => 'test@example.com',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error', 'Insufficient balance');
    }

    /**
     * @test
     */
    public function cannot_request_payout_when_pending_exists(): void
    {
        $tier = AffiliateTier::factory()->create(['is_active' => true]);
        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $tier->id,
            'pending_earnings' => 100,
        ]);

        Payout::factory()->create([
            'affiliate_id' => $affiliate->id,
            'status' => Payout::STATUS_PENDING,
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/affiliate/payout', [
                'payment_email' => 'test@example.com',
            ]);

        $response
            ->assertStatus(409)
            ->assertJsonPath('error', 'Pending payout exists');
    }

    /**
     * @test
     */
    public function can_get_payout_history(): void
    {
        $tier = AffiliateTier::factory()->create(['is_active' => true]);
        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'tier_id' => $tier->id,
        ]);

        Payout::factory()->count(3)->create(['affiliate_id' => $affiliate->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/affiliate/payouts');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total_pages', 'total_count'],
            ]);
    }

    /**
     * @test
     */
    public function cannot_get_payout_history_when_not_enrolled(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/affiliate/payouts');

        $response
            ->assertStatus(403)
            ->assertJsonPath('error', 'Not enrolled');
    }

    /**
     * @test
     */
    public function returns_403_when_affiliate_disabled(): void
    {
        Setting::set('affiliate_enabled', false);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/affiliate');

        $response
            ->assertStatus(403)
            ->assertJsonPath('error', 'Affiliate program is disabled');
    }

    /**
     * @test
     */
    public function requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/affiliate');
        $response->assertStatus(401);
    }
}
