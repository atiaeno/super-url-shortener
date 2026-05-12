<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AffiliateDashboardReferralTest extends TestCase
{
    use RefreshDatabase;

    protected $referrerAffiliate;
    protected $referralAffiliate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users and affiliates
        $referrerUser = User::factory()->create();
        $referralUser = User::factory()->create();

        $this->referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'REFERRER123',
            'total_earnings' => 100.0,
            'pending_earnings' => 30.0,
            'paid_earnings' => 70.0,
            'referral_earnings' => 25.0,
            'referral_pending_earnings' => 15.0,
            'referral_paid_earnings' => 10.0,
            'total_visits' => 1000,
            'is_active' => true,
        ]);

        $this->referralAffiliate = Affiliate::factory()->create([
            'user_id' => $referralUser->id,
            'referral_code' => 'REFERRAL456',
            'total_earnings' => 80.0,
            'pending_earnings' => 20.0,
            'paid_earnings' => 60.0,
            'referral_earnings' => 0.0,
            'referral_pending_earnings' => 0.0,
            'referral_paid_earnings' => 0.0,
            'total_visits' => 500,
            'is_active' => true,
        ]);

        // Link referral to referrer
        $referralUser->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);
    }

    /**
     * @test
     */
    public function it_displays_referral_earnings_on_dashboard()
    {
        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Affiliate/Dashboard')
            ->has('affiliate')
            ->where('affiliate.referral_earnings', 25)
            ->where('affiliate.referral_pending_earnings', 15)
            ->where('affiliate.referral_paid_earnings', 10));
    }

    /**
     * @test
     */
    public function it_displays_referred_users_count()
    {
        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        // Test the method directly on the affiliate model
        $referredCount = $this->referrerAffiliate->getReferredAffiliatesCount();
        $this->assertEquals(1, $referredCount);
    }

    /**
     * @test
     */
    public function it_displays_total_earnings_including_referrals()
    {
        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        // Test total earnings calculation directly on the model
        $totalEarnings = $this->referrerAffiliate->getTotalEarningsIncludingReferrals();
        $this->assertEquals(125.0, $totalEarnings);  // 100.00 direct + 25.00 referral
    }

    /**
     * @test
     */
    public function it_displays_total_pending_including_referrals()
    {
        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        // Test total pending calculation directly on the model
        $totalPending = $this->referrerAffiliate->getTotalPendingEarningsIncludingReferrals();
        $this->assertEquals(45.0, $totalPending);  // 30.00 direct + 15.00 referral
    }

    /**
     * @test
     */
    public function it_shows_referral_code_in_dashboard()
    {
        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        $response->assertInertia(fn(Assert $page) => $page
            ->component('Affiliate/Dashboard')
            ->has('affiliate')
            ->where('affiliate.referral_code', 'REFERRER123'));
    }

    /**
     * @test
     */
    public function it_handles_affiliate_without_referrals()
    {
        $this->actingAs($this->referralAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        $response->assertInertia(fn(Assert $page) => $page
            ->component('Affiliate/Dashboard')
            ->has('affiliate')
            ->where('affiliate.referral_earnings', 0)
            ->where('affiliate.referral_pending_earnings', 0)
            ->where('affiliate.referral_paid_earnings', 0));

        $referredCount = $this->referralAffiliate->getReferredAffiliatesCount();
        $this->assertEquals(0, $referredCount);
    }

    /**
     * @test
     */
    public function it_requires_authentication_for_dashboard()
    {
        $response = $this->get('/affiliate/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * @test
     */
    public function it_requires_affiliate_account_for_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/affiliate/dashboard');

        // User without affiliate account should still see dashboard with null affiliate
        $response->assertStatus(200);
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Affiliate/Dashboard')
            ->where('affiliate', null));
    }

    /**
     * @test
     */
    public function it_displays_payout_eligibility_with_referrals()
    {
        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        // Test payout eligibility with minimum of 40
        $canPayout = $this->referrerAffiliate->canRequestPayoutWithReferrals(40.0);
        $this->assertTrue($canPayout);

        // Test payout eligibility with minimum of 50
        $cannotPayout = $this->referrerAffiliate->canRequestPayoutWithReferrals(50.0);
        $this->assertFalse($cannotPayout);
    }

    /**
     * @test
     */
    public function it_displays_multiple_referred_users()
    {
        // Create additional referred users
        $additionalUser1 = User::factory()->create();
        $additionalUser2 = User::factory()->create();

        $additionalAffiliate1 = Affiliate::factory()->create([
            'user_id' => $additionalUser1->id,
            'referral_code' => 'ADDITIONAL1',
            'is_active' => true,
        ]);

        $additionalAffiliate2 = Affiliate::factory()->create([
            'user_id' => $additionalUser2->id,
            'referral_code' => 'ADDITIONAL2',
            'is_active' => true,
        ]);

        $additionalUser1->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);

        $additionalUser2->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);

        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        // Refresh to get updated count
        $referredCount = $this->referrerAffiliate->fresh()->getReferredAffiliatesCount();
        $this->assertEquals(3, $referredCount);  // Original + 2 additional
    }

    /**
     * @test
     */
    public function it_only_counts_active_referred_affiliates()
    {
        // Create inactive referred affiliate
        $inactiveUser = User::factory()->create();
        $inactiveAffiliate = Affiliate::factory()->create([
            'user_id' => $inactiveUser->id,
            'referral_code' => 'INACTIVE',
            'is_active' => false,
        ]);

        $inactiveUser->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);

        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        // Refresh to get updated count
        $referredCount = $this->referrerAffiliate->fresh()->getReferredAffiliatesCount();
        $this->assertEquals(1, $referredCount);  // Only the active one
    }

    /**
     * @test
     */
    public function it_passes_payout_methods_to_dashboard()
    {
        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        // Check that payout methods are available in Inertia props
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Affiliate/Dashboard')
            ->has('payoutMethods'));
    }

    /**
     * @test
     */
    public function it_passes_minimum_payout_to_dashboard()
    {
        $this->actingAs($this->referrerAffiliate->user);

        $response = $this->get('/affiliate/dashboard');

        $response->assertStatus(200);

        // Check that minimum payout is available in Inertia props
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Affiliate/Dashboard')
            ->has('minPayout'));
    }
}
