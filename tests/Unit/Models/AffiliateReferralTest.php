<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Models;

use App\Models\Affiliate;
use App\Models\ReferralCommission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateReferralTest extends TestCase
{
    use RefreshDatabase;

    protected $referrerUser;
    protected $referralUser;
    protected $referrerAffiliate;
    protected $referralAffiliate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->referrerUser = User::factory()->create();
        $this->referralUser = User::factory()->create();

        $this->referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $this->referrerUser->id,
            'referral_code' => 'REFERRER123',
            'total_earnings' => 100.0,
            'pending_earnings' => 50.0,
            'paid_earnings' => 50.0,
            'referral_earnings' => 25.0,
            'referral_pending_earnings' => 15.0,
            'referral_paid_earnings' => 10.0,
            'is_active' => true,
        ]);

        $this->referralAffiliate = Affiliate::factory()->create([
            'user_id' => $this->referralUser->id,
            'referral_code' => 'REFERRAL456',
            'total_earnings' => 80.0,
            'pending_earnings' => 30.0,
            'paid_earnings' => 50.0,
            'referral_earnings' => 0.0,
            'referral_pending_earnings' => 0.0,
            'referral_paid_earnings' => 0.0,
            'is_active' => true,
        ]);

        // Link referral to referrer
        $this->referralUser->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);
    }

    /**
     * @test
     */
    public function it_can_get_total_earnings_including_referrals()
    {
        $total = $this->referrerAffiliate->getTotalEarningsIncludingReferrals();

        $this->assertEquals(125.0, $total);  // 100.00 direct + 25.00 referral
    }

    /**
     * @test
     */
    public function it_can_get_total_pending_earnings_including_referrals()
    {
        $total = $this->referrerAffiliate->getTotalPendingEarningsIncludingReferrals();

        $this->assertEquals(65.0, $total);  // 50.00 direct + 15.00 referral
    }

    /**
     * @test
     */
    public function it_can_check_payout_eligibility_with_referrals()
    {
        // Test with minimum payout of 60
        $this->assertTrue($this->referrerAffiliate->canRequestPayoutWithReferrals(60.0));

        // Test with minimum payout of 70
        $this->assertFalse($this->referrerAffiliate->canRequestPayoutWithReferrals(70.0));
    }

    /**
     * @test
     */
    public function it_can_count_referred_affiliates()
    {
        // Create another referred user with affiliate account
        $anotherUser = User::factory()->create();
        $anotherAffiliate = Affiliate::factory()->create([
            'user_id' => $anotherUser->id,
            'is_active' => true,
        ]);
        $anotherUser->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);

        $count = $this->referrerAffiliate->getReferredAffiliatesCount();
        $this->assertEquals(2, $count);
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
            'is_active' => false,
        ]);
        $inactiveUser->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);

        $count = $this->referrerAffiliate->getReferredAffiliatesCount();
        $this->assertEquals(1, $count);  // Only the active one
    }

    /**
     * @test
     */
    public function it_has_referred_users_relationship()
    {
        $referredUsers = $this->referrerAffiliate->referredUsers;

        $this->assertCount(1, $referredUsers);
        $this->assertEquals($this->referralUser->id, $referredUsers->first()->id);
    }

    /**
     * @test
     */
    public function it_has_referral_commissions_as_referrer_relationship()
    {
        // Create a referral commission
        ReferralCommission::factory()->create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'commission_amount' => 10.0,
        ]);

        $commissions = $this->referrerAffiliate->referralCommissionsAsReferrer;

        $this->assertCount(1, $commissions);
        $this->assertEquals(10.0, $commissions->first()->commission_amount);
    }

    /**
     * @test
     */
    public function it_has_referral_commissions_as_referral_relationship()
    {
        // Create a referral commission
        ReferralCommission::factory()->create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'commission_amount' => 10.0,
        ]);

        $commissions = $this->referralAffiliate->referralCommissionsAsReferral;

        $this->assertCount(1, $commissions);
        $this->assertEquals(10.0, $commissions->first()->commission_amount);
    }

    /**
     * @test
     */
    public function referral_earnings_are_cast_to_decimal()
    {
        $affiliate = Affiliate::factory()->create([
            'referral_earnings' => '12.3456',
            'referral_pending_earnings' => '6.7890',
            'referral_paid_earnings' => '5.5566',
        ]);

        $this->assertIsFloat($affiliate->referral_earnings);
        $this->assertIsFloat($affiliate->referral_pending_earnings);
        $this->assertIsFloat($affiliate->referral_paid_earnings);

        $this->assertEquals(12.3456, $affiliate->referral_earnings);
        $this->assertEquals(6.789, $affiliate->referral_pending_earnings);
        $this->assertEquals(5.5566, $affiliate->referral_paid_earnings);
    }

    /**
     * @test
     */
    public function referral_fields_are_mass_assignable()
    {
        $data = [
            'user_id' => User::factory()->create()->id,
            'tier_id' => 1,
            'referral_code' => 'TEST123',
            'total_earnings' => 100.0,
            'pending_earnings' => 50.0,
            'paid_earnings' => 50.0,
            'referral_earnings' => 25.0,
            'referral_pending_earnings' => 15.0,
            'referral_paid_earnings' => 10.0,
            'total_visits' => 1000,
            'is_active' => true,
        ];

        $affiliate = Affiliate::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $affiliate->$key);
        }
    }
}
