<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Jobs;

use App\Jobs\SyncAffiliateEarningsJob;
use App\Models\Affiliate;
use App\Models\AffiliateCountryRate;
use App\Models\AffiliateTier;
use App\Models\AffiliateVisit;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncAffiliateEarningsJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Link $link;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->link = Link::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_syncs_all_active_affiliates()
    {
        $tier1 = AffiliateTier::factory()->create([
            'view_rate' => 0.01,
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        $tier2 = AffiliateTier::factory()->create([
            'view_rate' => 0.02,
            'view_multiplier' => 200,
            'is_active' => true,
        ]);

        $affiliate1 = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $affiliate2 = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        // Create visits for affiliate1
        AffiliateVisit::factory()->count(50)->create([
            'affiliate_id' => $affiliate1->id,
            'affiliate_tier_id' => $tier1->id,
        ]);

        // Create visits for affiliate2
        AffiliateVisit::factory()->count(100)->create([
            'affiliate_id' => $affiliate2->id,
            'affiliate_tier_id' => $tier2->id,
        ]);

        $job = new SyncAffiliateEarningsJob();
        $job->handle();

        $affiliate1->refresh();
        $affiliate2->refresh();

        // affiliate1: 50 visits / 100 * 0.01 = 0.005
        $this->assertEquals(50, $affiliate1->total_visits);
        $this->assertEquals(0.005, $affiliate1->total_earnings);
        $this->assertEquals(0.005, $affiliate1->pending_earnings);

        // affiliate2: 100 visits / 200 * 0.02 = 0.01
        $this->assertEquals(100, $affiliate2->total_visits);
        $this->assertEquals(0.01, $affiliate2->total_earnings);
        $this->assertEquals(0.01, $affiliate2->pending_earnings);
    }

    /** @test */
    public function it_syncs_specific_affiliate()
    {
        $tier = AffiliateTier::factory()->create([
            'view_rate' => 0.01,
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        $affiliate1 = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $affiliate2 = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        // Create visits for both affiliates
        AffiliateVisit::factory()->count(50)->create([
            'affiliate_id' => $affiliate1->id,
            'affiliate_tier_id' => $tier->id,
        ]);

        AffiliateVisit::factory()->count(100)->create([
            'affiliate_id' => $affiliate2->id,
            'affiliate_tier_id' => $tier->id,
        ]);

        $job = new SyncAffiliateEarningsJob($affiliate1->id);
        $job->handle();

        $affiliate1->refresh();
        $affiliate2->refresh();

        // Only affiliate1 should be synced
        $this->assertEquals(50, $affiliate1->total_visits);
        $this->assertEquals(0.005, $affiliate1->total_earnings);

        // affiliate2 should remain at 0
        $this->assertEquals(0, $affiliate2->total_visits);
        $this->assertEquals(0, $affiliate2->total_earnings);
    }

    /** @test */
    public function it_handles_inactive_affiliates()
    {
        $tier = AffiliateTier::factory()->create([
            'view_rate' => 0.01,
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        $activeAffiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $inactiveAffiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        // Create visits for both affiliates
        AffiliateVisit::factory()->count(50)->create([
            'affiliate_id' => $activeAffiliate->id,
            'affiliate_tier_id' => $tier->id,
        ]);

        AffiliateVisit::factory()->count(100)->create([
            'affiliate_id' => $inactiveAffiliate->id,
            'affiliate_tier_id' => $tier->id,
        ]);

        $job = new SyncAffiliateEarningsJob();
        $job->handle();

        $activeAffiliate->refresh();
        $inactiveAffiliate->refresh();

        // Active affiliate should be synced
        $this->assertEquals(50, $activeAffiliate->total_visits);
        $this->assertEquals(0.005, $activeAffiliate->total_earnings);

        // Inactive affiliate should remain at 0
        $this->assertEquals(0, $inactiveAffiliate->total_visits);
        $this->assertEquals(0, $inactiveAffiliate->total_earnings);
    }

    /** @test */
    public function it_handles_visits_without_tier()
    {
        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        // Create visits without tier
        AffiliateVisit::factory()->count(50)->create([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => null,
        ]);

        $job = new SyncAffiliateEarningsJob();
        $job->handle();

        $affiliate->refresh();

        $this->assertEquals(50, $affiliate->total_visits);
        $this->assertEquals(0, $affiliate->total_earnings);
        $this->assertEquals(0, $affiliate->pending_earnings);
    }

    /** @test */
    public function it_handles_inactive_tiers()
    {
        $activeTier = AffiliateTier::factory()->create([
            'view_rate' => 0.01,
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        $inactiveTier = AffiliateTier::factory()->create([
            'view_rate' => 0.02,
            'view_multiplier' => 200,
            'is_active' => false,
        ]);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        // Create visits for both tiers
        AffiliateVisit::factory()->count(50)->create([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $activeTier->id,
        ]);

        AffiliateVisit::factory()->count(100)->create([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $inactiveTier->id,
        ]);

        $job = new SyncAffiliateEarningsJob();
        $job->handle();

        $affiliate->refresh();

        // Only active tier should count
        $this->assertEquals(150, $affiliate->total_visits); // All visits counted
        $this->assertEquals(0.005, $affiliate->total_earnings); // Only from active tier
    }

    /** @test */
    public function it_handles_paid_earnings_correctly()
    {
        $tier = AffiliateTier::factory()->create([
            'view_rate' => 0.01,
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'paid_earnings' => 0.002,
        ]);

        AffiliateVisit::factory()->count(50)->create([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $tier->id,
        ]);

        $job = new SyncAffiliateEarningsJob();
        $job->handle();

        $affiliate->refresh();

        $this->assertEquals(50, $affiliate->total_visits);
        $this->assertEquals(0.005, $affiliate->total_earnings);
        $this->assertEquals(0.003, $affiliate->pending_earnings); // 0.005 - 0.002
    }

    /** @test */
    public function it_handles_multiple_tiers_for_same_affiliate()
    {
        $tier1 = AffiliateTier::factory()->create([
            'view_rate' => 0.01,
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        $tier2 = AffiliateTier::factory()->create([
            'view_rate' => 0.02,
            'view_multiplier' => 200,
            'is_active' => true,
        ]);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        // Create visits for tier1
        AffiliateVisit::factory()->count(50)->create([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $tier1->id,
        ]);

        // Create visits for tier2
        AffiliateVisit::factory()->count(100)->create([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $tier2->id,
        ]);

        $job = new SyncAffiliateEarningsJob();
        $job->handle();

        $affiliate->refresh();

        // tier1: 50 visits / 100 * 0.01 = 0.005
        // tier2: 100 visits / 200 * 0.02 = 0.01
        // total: 0.005 + 0.01 = 0.015
        $this->assertEquals(150, $affiliate->total_visits);
        $this->assertEquals(0.015, $affiliate->total_earnings);
        $this->assertEquals(0.015, $affiliate->pending_earnings);
    }

    /** @test */
    public function it_handles_missing_affiliate_gracefully()
    {
        $job = new SyncAffiliateEarningsJob(999);
        $job->handle();

        $this->assertTrue(true); // Should not throw any errors
    }

    /** @test */
    public function it_handles_no_visits()
    {
        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $job = new SyncAffiliateEarningsJob();
        $job->handle();

        $affiliate->refresh();

        $this->assertEquals(0, $affiliate->total_visits);
        $this->assertEquals(0, $affiliate->total_earnings);
        $this->assertEquals(0, $affiliate->pending_earnings);
    }

    /** @test */
    public function it_rounds_earnings_correctly()
    {
        $tier = AffiliateTier::factory()->create([
            'view_rate' => 0.003333, // Creates repeating decimal
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        AffiliateVisit::factory()->count(3)->create([
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $tier->id,
        ]);

        $job = new SyncAffiliateEarningsJob();
        $job->handle();

        $affiliate->refresh();

        // 3 visits / 100 * 0.003333 = 0.00009999, should be rounded to 4 decimal places
        $this->assertEquals(0.0001, $affiliate->total_earnings);
        $this->assertEquals(0.0001, $affiliate->pending_earnings);
    }
}
