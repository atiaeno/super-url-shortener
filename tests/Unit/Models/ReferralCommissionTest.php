<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Models;

use App\Models\Affiliate;
use App\Models\ReferralCommission;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReferralCommissionTest extends TestCase
{
    use RefreshDatabase;

    protected $referrerAffiliate;
    protected $referralAffiliate;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test affiliates
        $referrerUser = User::factory()->create();
        $referralUser = User::factory()->create();
        
        $this->referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'REFERRER123',
            'is_active' => true,
        ]);
        
        $this->referralAffiliate = Affiliate::factory()->create([
            'user_id' => $referralUser->id,
            'referral_code' => 'REFERRAL456',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_create_referral_commission()
    {
        $commission = ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 100.00,
            'commission_amount' => 1.50,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        $this->assertInstanceOf(ReferralCommission::class, $commission);
        $this->assertEquals($this->referrerAffiliate->id, $commission->referrer_affiliate_id);
        $this->assertEquals($this->referralAffiliate->id, $commission->referral_affiliate_id);
        $this->assertEquals(100.00, $commission->referral_earnings);
        $this->assertEquals(1.50, $commission->commission_amount);
        $this->assertEquals(1.5, $commission->commission_rate);
        $this->assertEquals('2026-05-12', $commission->commission_date->format('Y-m-d'));
    }

    /** @test */
    public function it_has_referrer_relationship()
    {
        $commission = ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 100.00,
            'commission_amount' => 1.50,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        $referrer = $commission->referrer;
        
        $this->assertInstanceOf(Affiliate::class, $referrer);
        $this->assertEquals($this->referrerAffiliate->id, $referrer->id);
        $this->assertEquals('REFERRER123', $referrer->referral_code);
    }

    /** @test */
    public function it_has_referral_relationship()
    {
        $commission = ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 100.00,
            'commission_amount' => 1.50,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        $referral = $commission->referral;
        
        $this->assertInstanceOf(Affiliate::class, $referral);
        $this->assertEquals($this->referralAffiliate->id, $referral->id);
        $this->assertEquals('REFERRAL456', $referral->referral_code);
    }

    /** @test */
    public function it_can_scope_by_date_range()
    {
        // Create commissions for different dates
        ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 100.00,
            'commission_amount' => 1.50,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-10',
        ]);

        ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 200.00,
            'commission_amount' => 3.00,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 150.00,
            'commission_amount' => 2.25,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-15',
        ]);

        // Test date range scope
        $commissionsInRange = ReferralCommission::forDateRange('2026-05-11', '2026-05-13')->get();
        $this->assertCount(1, $commissionsInRange);
        $this->assertEquals('2026-05-12', $commissionsInRange->first()->commission_date->format('Y-m-d'));
    }

    /** @test */
    public function it_can_scope_by_referrer()
    {
        // Create another affiliate for testing
        $otherUser = User::factory()->create();
        $otherAffiliate = Affiliate::factory()->create([
            'user_id' => $otherUser->id,
            'referral_code' => 'OTHER123',
            'is_active' => true,
        ]);

        // Create commissions for different referrers
        ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 100.00,
            'commission_amount' => 1.50,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        ReferralCommission::create([
            'referrer_affiliate_id' => $otherAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 200.00,
            'commission_amount' => 3.00,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        $referrerCommissions = ReferralCommission::forReferrer($this->referrerAffiliate->id)->get();
        $this->assertCount(1, $referrerCommissions);
        $this->assertEquals($this->referrerAffiliate->id, $referrerCommissions->first()->referrer_affiliate_id);
    }

    /** @test */
    public function it_can_scope_by_referral()
    {
        // Create another affiliate for testing
        $otherUser = User::factory()->create();
        $otherAffiliate = Affiliate::factory()->create([
            'user_id' => $otherUser->id,
            'referral_code' => 'OTHER456',
            'is_active' => true,
        ]);

        // Create commissions for different referrals
        ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 100.00,
            'commission_amount' => 1.50,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $otherAffiliate->id,
            'referral_earnings' => 200.00,
            'commission_amount' => 3.00,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        $referralCommissions = ReferralCommission::forReferral($this->referralAffiliate->id)->get();
        $this->assertCount(1, $referralCommissions);
        $this->assertEquals($this->referralAffiliate->id, $referralCommissions->first()->referral_affiliate_id);
    }

    /** @test */
    public function monetary_fields_are_cast_to_decimal()
    {
        $commission = ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => '123.4567',
            'commission_amount' => '1.8535',
            'commission_rate' => '1.5',
            'commission_date' => '2026-05-12',
        ]);

        $this->assertIsFloat($commission->referral_earnings);
        $this->assertIsFloat($commission->commission_amount);
        $this->assertIsFloat($commission->commission_rate);
        
        $this->assertEquals(123.4567, $commission->referral_earnings);
        $this->assertEquals(1.8535, $commission->commission_amount);
        $this->assertEquals(1.5, $commission->commission_rate);
    }

    /** @test */
    public function commission_date_is_cast_to_date()
    {
        $commission = ReferralCommission::create([
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 100.00,
            'commission_amount' => 1.50,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $commission->commission_date);
        $this->assertEquals('2026-05-12', $commission->commission_date->format('Y-m-d'));
    }

    /** @test */
    public function all_fields_are_mass_assignable()
    {
        $data = [
            'referrer_affiliate_id' => $this->referrerAffiliate->id,
            'referral_affiliate_id' => $this->referralAffiliate->id,
            'referral_earnings' => 150.00,
            'commission_amount' => 2.25,
            'commission_rate' => 1.5,
            'commission_date' => '2026-05-12',
        ];
        
        $commission = ReferralCommission::create($data);
        
        foreach ($data as $key => $value) {
            if ($key === 'commission_date') {
                $this->assertEquals($value, $commission->$key->format('Y-m-d'));
            } else {
                $this->assertEquals($value, $commission->$key);
            }
        }
    }
}
