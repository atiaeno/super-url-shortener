<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Models;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserReferralTest extends TestCase
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
            'is_active' => true,
        ]);

        $this->referralAffiliate = Affiliate::factory()->create([
            'user_id' => $this->referralUser->id,
            'referral_code' => 'REFERRAL456',
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
    public function it_has_referred_by_relationship()
    {
        $referredBy = $this->referralUser->referredBy;

        $this->assertInstanceOf(Affiliate::class, $referredBy);
        $this->assertEquals($this->referrerAffiliate->id, $referredBy->id);
        $this->assertEquals('REFERRER123', $referredBy->referral_code);
    }

    /**
     * @test
     */
    public function referred_by_relationship_returns_null_when_no_referrer()
    {
        $userWithoutReferrer = User::factory()->create();

        $referredBy = $userWithoutReferrer->referredBy;

        $this->assertNull($referredBy);
    }

    /**
     * @test
     */
    public function it_has_referred_users_relationship()
    {
        $referredUsers = $this->referrerUser->referredUsers;

        $this->assertCount(1, $referredUsers);
        $this->assertEquals($this->referralUser->id, $referredUsers->first()->id);
    }

    /**
     * @test
     */
    public function referred_users_relationship_returns_empty_when_no_referrals()
    {
        $userWithoutReferrals = User::factory()->create();

        $referredUsers = $userWithoutReferrals->referredUsers;

        $this->assertCount(0, $referredUsers);
    }

    /**
     * @test
     */
    public function it_can_have_multiple_referred_users()
    {
        // Create additional referred users
        $additionalUser1 = User::factory()->create();
        $additionalUser2 = User::factory()->create();

        $additionalUser1->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);

        $additionalUser2->update([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);

        $referredUsers = $this->referrerUser->referredUsers;

        $this->assertCount(3, $referredUsers);
    }

    /**
     * @test
     */
    public function referred_by_affiliate_id_is_mass_assignable()
    {
        $user = User::factory()->create([
            'referred_by_affiliate_id' => $this->referrerAffiliate->id
        ]);

        $this->assertEquals($this->referrerAffiliate->id, $user->referred_by_affiliate_id);
    }

    /**
     * @test
     */
    public function it_can_check_if_user_was_referred()
    {
        $this->assertTrue($this->referralUser->referredBy !== null);
        $this->assertFalse($this->referrerUser->referredBy !== null);
    }

    /**
     * @test
     */
    public function it_can_get_referral_chain_depth()
    {
        // Create a 3-level referral chain
        $level3User = User::factory()->create();
        $level3Affiliate = Affiliate::factory()->create([
            'user_id' => $level3User->id,
            'referral_code' => 'LEVEL3',
            'is_active' => true,
        ]);

        $level3User->update([
            'referred_by_affiliate_id' => $this->referralAffiliate->id
        ]);

        // Verify the chain
        $this->assertEquals($this->referrerAffiliate->id, $this->referralUser->referred_by_affiliate_id);
        $this->assertEquals($this->referralAffiliate->id, $level3User->referred_by_affiliate_id);

        // Verify relationships work correctly
        $this->assertEquals($this->referrerAffiliate->id, $this->referralUser->referredBy->id);
        $this->assertEquals($this->referralAffiliate->id, $level3User->referredBy->id);
    }

    /**
     * @test
     */
    public function it_handles_null_referred_by_affiliate_id_gracefully()
    {
        $user = User::factory()->create([
            'referred_by_affiliate_id' => null
        ]);

        $this->assertNull($user->referred_by_affiliate_id);
        $this->assertNull($user->referredBy);
        $this->assertCount(0, $user->referredUsers);
    }
}
