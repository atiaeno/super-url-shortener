<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature;

use App\Http\Controllers\AffiliateController;
use App\Models\Affiliate;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralPayoutTest extends TestCase
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
            'total_earnings' => 100.00,
            'pending_earnings' => 30.00,
            'paid_earnings' => 70.00,
            'referral_earnings' => 25.00,
            'referral_pending_earnings' => 15.00,
            'referral_paid_earnings' => 10.00,
            'is_active' => true,
        ]);
        
        $this->referralAffiliate = Affiliate::factory()->create([
            'user_id' => $referralUser->id,
            'referral_code' => 'REFERRER456',
            'total_earnings' => 80.00,
            'pending_earnings' => 20.00,
            'paid_earnings' => 60.00,
            'referral_earnings' => 0.00,
            'referral_pending_earnings' => 0.00,
            'referral_paid_earnings' => 0.00,
            'is_active' => true,
        ]);
        
        // Link referral to referrer
        $referralUser->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);
    }

    /** @test */
    public function it_includes_referral_earnings_in_payout_request()
    {
        $this->actingAs($this->referrerAffiliate->user);
        
        $payoutData = [
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ];

        $response = $this->post('/affiliate/payout/request', $payoutData);

        $response->assertRedirect();
        
        // Check that payout was created with total amount including referrals
        $payout = Payout::where('affiliate_id', $this->referrerAffiliate->id)->first();
        $this->assertNotNull($payout);
        $this->assertEquals(45.00, $payout->amount); // 30.00 direct + 15.00 referral
        
        // Check that both pending earnings were reset to 0
        $this->referrerAffiliate->refresh();
        $this->assertEquals(0, $this->referrerAffiliate->pending_earnings);
        $this->assertEquals(0, $this->referrerAffiliate->referral_pending_earnings);
    }

    /** @test */
    public function it_prevents_duplicate_payout_requests()
    {
        $this->actingAs($this->referrerAffiliate->user);
        
        // Create first payout request
        Payout::create([
            'affiliate_id' => $this->referrerAffiliate->id,
            'amount' => 45.00,
            'status' => Payout::STATUS_PENDING,
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ]);
        
        $payoutData = [
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal2@example.com',
        ];

        $response = $this->post('/affiliate/payout/request', $payoutData);

        $response->assertSessionHasErrors('error');
        
        // Should only have one payout
        $payouts = Payout::where('affiliate_id', $this->referrerAffiliate->id)->get();
        $this->assertCount(1, $payouts);
    }

    /** @test */
    public function it_checks_minimum_payout_with_referral_earnings()
    {
        $this->actingAs($this->referrerAffiliate->user);
        
        // Set minimum payout higher than total pending
        config(['affiliate.min_payout' => 100.00]);
        
        $payoutData = [
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ];

        $response = $this->post('/affiliate/payout/request', $payoutData);

        $response->assertSessionHasErrors('error');
        
        // No payout should be created
        $payout = Payout::where('affiliate_id', $this->referrerAffiliate->id)->first();
        $this->assertNull($payout);
    }

    /** @test */
    public function it_handles_payout_approval_with_referral_earnings()
    {
        // Create a pending payout
        $payout = Payout::create([
            'affiliate_id' => $this->referrerAffiliate->id,
            'amount' => 45.00, // 30.00 direct + 15.00 referral
            'status' => Payout::STATUS_PENDING,
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ]);

        // Simulate admin approval
        $payout->update([
            'status' => Payout::STATUS_APPROVED,
            'processed_by' => 1,
            'processed_at' => now(),
        ]);

        // Mark as paid
        $payout->update([
            'status' => Payout::STATUS_PAID,
            'processed_by' => 1,
            'processed_at' => now(),
        ]);

        $this->referrerAffiliate->refresh();
        
        // Check that paid_earnings increased by total amount
        $this->assertEquals(115.00, $this->referrerAffiliate->paid_earnings); // 70.00 + 45.00
    }

    /** @test */
    public function it_calculates_total_pending_correctly()
    {
        $this->actingAs($this->referrerAffiliate->user);
        
        // Test the calculation method
        $totalPending = $this->referrerAffiliate->getTotalPendingEarningsIncludingReferrals();
        $this->assertEquals(45.00, $totalPending); // 30.00 direct + 15.00 referral
        
        // Test payout eligibility
        $canPayout = $this->referrerAffiliate->canRequestPayoutWithReferrals(40.00);
        $this->assertTrue($canPayout);
        
        $cannotPayout = $this->referrerAffiliate->canRequestPayoutWithReferrals(50.00);
        $this->assertFalse($cannotPayout);
    }

    /** @test */
    public function it_handles_zero_referral_earnings()
    {
        // Create affiliate with no referral earnings
        $noReferralUser = User::factory()->create();
        $noReferralAffiliate = Affiliate::factory()->create([
            'user_id' => $noReferralUser->id,
            'referral_code' => 'NOREFERRAL',
            'total_earnings' => 50.00,
            'pending_earnings' => 25.00,
            'paid_earnings' => 25.00,
            'referral_earnings' => 0.00,
            'referral_pending_earnings' => 0.00,
            'referral_paid_earnings' => 0.00,
            'is_active' => true,
        ]);

        $this->actingAs($noReferralAffiliate->user);
        
        $payoutData = [
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ];

        $response = $this->post('/affiliate/payout/request', $payoutData);

        $response->assertRedirect();
        
        // Payout should only include direct earnings
        $payout = Payout::where('affiliate_id', $noReferralAffiliate->id)->first();
        $this->assertNotNull($payout);
        $this->assertEquals(25.00, $payout->amount); // Only direct earnings
    }

    /** @test */
    public function it_handles_only_referral_earnings()
    {
        // Create affiliate with only referral earnings
        $onlyReferralUser = User::factory()->create();
        $onlyReferralAffiliate = Affiliate::factory()->create([
            'user_id' => $onlyReferralUser->id,
            'referral_code' => 'ONLYREFERRAL',
            'total_earnings' => 0.00,
            'pending_earnings' => 0.00,
            'paid_earnings' => 0.00,
            'referral_earnings' => 20.00,
            'referral_pending_earnings' => 20.00,
            'referral_paid_earnings' => 0.00,
            'is_active' => true,
        ]);

        $this->actingAs($onlyReferralAffiliate->user);
        
        $payoutData = [
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ];

        $response = $this->post('/affiliate/payout/request', $payoutData);

        $response->assertRedirect();
        
        // Payout should only include referral earnings
        $payout = Payout::where('affiliate_id', $onlyReferralAffiliate->id)->first();
        $this->assertNotNull($payout);
        $this->assertEquals(20.00, $payout->amount); // Only referral earnings
    }

    /** @test */
    public function it_validates_payout_request_data()
    {
        $this->actingAs($this->referrerAffiliate->user);
        
        // Test missing payment method
        $response = $this->post('/affiliate/payout/request', [
            'payment_email' => 'paypal@example.com',
        ]);
        $response->assertSessionHasErrors('payment_method');
        
        // Test missing payment email
        $response = $this->post('/affiliate/payout/request', [
            'payment_method' => 'PayPal',
        ]);
        $response->assertSessionHasErrors('payment_email');
        
        // Test invalid payment method
        $response = $this->post('/affiliate/payout/request', [
            'payment_method' => '',
            'payment_email' => 'paypal@example.com',
        ]);
        $response->assertSessionHasErrors('payment_method');
    }

    /** @test */
    public function it_requires_authentication_for_payout_request()
    {
        $payoutData = [
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ];

        $response = $this->post('/affiliate/payout/request', $payoutData);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_requires_affiliate_account_for_payout_request()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $payoutData = [
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ];

        $response = $this->post('/affiliate/payout/request', $payoutData);

        $response->assertNotFound();
    }
}
