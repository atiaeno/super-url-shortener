<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Jobs;

use App\Jobs\SyncReferralCommissionsJob;
use App\Models\Affiliate;
use App\Models\AffiliateTier;
use App\Models\ReferralCommission;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SyncReferralCommissionsJobTest extends TestCase
{
    use RefreshDatabase;

    protected $referrerAffiliate;
    protected $referralAffiliate;

    protected function setUp(): void
    {
        parent::setUp();

        // Set referral commission rate
        Setting::set('referral_commission_rate', '1.5');

        // Create a tier for affiliates
        $tier = AffiliateTier::factory()->create([
            'name' => 'Test Tier',
            'visit_threshold' => 0,
            'view_rate' => 1.0,
            'view_multiplier' => 1000,
            'is_active' => true,
        ]);

        // Create test users and affiliates
        $referrerUser = User::factory()->create();
        $referralUser = User::factory()->create();

        $this->referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'tier_id' => $tier->id,
            'referral_code' => 'REFERRER123',
            'total_earnings' => 100.0,
            'pending_earnings' => 50.0,
            'paid_earnings' => 50.0,
            'referral_earnings' => 0.0,
            'referral_pending_earnings' => 0.0,
            'referral_paid_earnings' => 0.0,
            'is_active' => true,
        ]);

        $this->referralAffiliate = Affiliate::factory()->create([
            'user_id' => $referralUser->id,
            'tier_id' => $tier->id,
            'referral_code' => 'REFERRAL456',
            'total_earnings' => 200.0,
            'pending_earnings' => 100.0,
            'paid_earnings' => 100.0,
            'referral_earnings' => 0.0,
            'referral_pending_earnings' => 0.0,
            'referral_paid_earnings' => 0.0,
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
    public function it_processes_referral_commissions_for_active_referrers()
    {
        $job = new SyncReferralCommissionsJob('2026-05-12');

        // Create visits for the referral
        $this->createMockVisits($this->referralAffiliate, 50, '2026-05-12');

        $job->handle();

        // Refresh the referrer affiliate
        $this->referrerAffiliate->refresh();

        // Check that referral earnings were calculated
        $this->assertGreaterThan(0, $this->referrerAffiliate->referral_earnings);
        $this->assertGreaterThan(0, $this->referrerAffiliate->referral_pending_earnings);

        // Check that commission record was created
        $commission = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals($this->referrerAffiliate->id, $commission->referrer_affiliate_id);
        $this->assertEquals($this->referralAffiliate->id, $commission->referral_affiliate_id);
        $this->assertEquals(1.5, $commission->commission_rate);
    }

    /**
     * @test
     */
    public function it_uses_configured_commission_rate()
    {
        Setting::set('referral_commission_rate', '2.0');

        $job = new SyncReferralCommissionsJob('2026-05-12');

        $this->createMockVisits($this->referralAffiliate, 100, '2026-05-12');

        $job->handle();

        $commission = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals(2.0, $commission->commission_rate);
    }

    /**
     * @test
     */
    public function it_skips_inactive_referrers()
    {
        // Make referrer inactive
        $this->referrerAffiliate->update(['is_active' => false]);

        $job = new SyncReferralCommissionsJob('2026-05-12');

        $this->createMockVisits($this->referralAffiliate, 50, '2026-05-12');

        $job->handle();

        // No commissions should be created for inactive referrer
        $commissions = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)
            ->where('commission_date', '2026-05-12')
            ->get();

        $this->assertCount(0, $commissions);
    }

    /**
     * @test
     */
    public function it_skips_inactive_referrals()
    {
        // Make referral inactive
        $this->referralAffiliate->update(['is_active' => false]);

        $job = new SyncReferralCommissionsJob('2026-05-12');

        $this->createMockVisits($this->referralAffiliate, 50, '2026-05-12');

        $job->handle();

        // No commissions should be created for inactive referral
        $commissions = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)
            ->where('commission_date', '2026-05-12')
            ->get();

        $this->assertCount(0, $commissions);
    }

    /**
     * @test
     */
    public function it_handles_zero_referral_earnings()
    {
        $job = new SyncReferralCommissionsJob('2026-05-12');

        // Don't create any visits (zero earnings)

        $job->handle();

        // No commissions should be created for zero earnings
        $commissions = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)
            ->where('commission_date', '2026-05-12')
            ->get();

        $this->assertCount(0, $commissions);
    }

    /**
     * @test
     */
    public function it_handles_invalid_commission_rate()
    {
        // Set invalid commission rate
        Setting::set('referral_commission_rate', '5.0');  // Above 2% limit

        $job = new SyncReferralCommissionsJob('2026-05-12');

        $this->createMockVisits($this->referralAffiliate, 50, '2026-05-12');

        $job->handle();

        // No commissions should be created for invalid rate
        $commissions = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)
            ->where('commission_date', '2026-05-12')
            ->get();

        $this->assertCount(0, $commissions);
    }

    /**
     * @test
     */
    public function it_accumulates_referral_earnings()
    {
        $job1 = new SyncReferralCommissionsJob('2026-05-12');
        $job2 = new SyncReferralCommissionsJob('2026-05-13');

        $this->createMockVisits($this->referralAffiliate, 50, '2026-05-12');
        $this->createMockVisits($this->referralAffiliate, 75, '2026-05-13');

        // Process first day
        $job1->handle();

        // Process second day
        $job2->handle();

        $this->referrerAffiliate->refresh();

        // Check that earnings accumulated
        $this->assertGreaterThan(0, $this->referrerAffiliate->referral_earnings);
        $this->assertGreaterThan(0, $this->referrerAffiliate->referral_pending_earnings);

        // Check that both days have commissions
        $commissions = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)->get();
        $this->assertCount(2, $commissions);

        // Verify dates are correct
        $dates = $commissions->pluck('commission_date')->map(fn($d) => $d->format('Y-m-d'))->toArray();
        $this->assertContains('2026-05-12', $dates);
        $this->assertContains('2026-05-13', $dates);
    }

    /**
     * @test
     */
    public function it_handles_default_date_when_not_provided()
    {
        $job = new SyncReferralCommissionsJob();  // No date provided

        $this->createMockVisits($this->referralAffiliate, 50, now()->toDateString());

        $job->handle();

        // Should use today's date
        $commission = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)
            ->first();

        $this->assertNotNull($commission);
        $this->assertEquals(now()->toDateString(), $commission->commission_date->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function it_does_not_duplicate_commissions_for_same_date()
    {
        $job = new SyncReferralCommissionsJob('2026-05-12');

        $this->createMockVisits($this->referralAffiliate, 50, '2026-05-12');

        // Run job twice for the same date
        $job->handle();
        $job->handle();

        // Should only have one commission record
        $commissionCount = ReferralCommission::where('referrer_affiliate_id', $this->referrerAffiliate->id)->count();
        $this->assertEquals(1, $commissionCount);
    }

    private function createMockVisits(Affiliate $affiliate, int $visitCount, string $date)
    {
        // Create actual AffiliateVisit records for the job to process
        $tier = $affiliate->tier;
        if (!$tier) {
            return;
        }

        $baseDate = Carbon::parse($date);

        for ($i = 0; $i < $visitCount; $i++) {
            // Create visits with incremental timestamps throughout the day
            $visitTime = $baseDate->copy()->addMinutes($i * 10);

            DB::table('affiliate_visits')->insert([
                'affiliate_id' => $affiliate->id,
                'affiliate_tier_id' => $tier->id,
                'ip_hash' => hash('sha256', "test-ip-{$i}-" . uniqid()),
                'country_code' => 'US',
                'created_at' => $visitTime,
                'updated_at' => $visitTime,
            ]);
        }
    }
}
