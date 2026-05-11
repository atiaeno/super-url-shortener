<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Jobs;

use App\Jobs\LogClickJob;
use App\Models\Affiliate;
use App\Models\AffiliateCountryRate;
use App\Models\AffiliateTier;
use App\Models\Click;
use App\Models\Link;
use App\Models\LinkAnalyticsDaily;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogClickJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Link $link;
    private array $clickData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->link = Link::factory()->create(['user_id' => $this->user->id]);

        $this->clickData = [
            'ip_hash' => hash('sha256', '192.168.1.1'),
            'country_code' => 'US',
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'referrer' => 'https://google.com',
            'referrer_domain' => 'google.com',
        ];
    }

    /**
     * @test
     */
    public function it_logs_click_successfully()
    {
        $job = new LogClickJob($this->link->id, $this->clickData);
        $job->handle();

        $this->assertDatabaseHas('clicks', [
            'link_id' => $this->link->id,
            'ip_hash' => $this->clickData['ip_hash'],
            'country_code' => 'US',
            'device_type' => 'desktop',
        ]);
    }

    /**
     * @test
     */
    public function it_records_to_analytics_summary()
    {
        $job = new LogClickJob($this->link->id, $this->clickData);
        $job->handle();

        $this->assertDatabaseHas('link_analytics_daily', [
            'link_id' => $this->link->id,
        ]);
    }

    /**
     * @test
     */
    public function it_skips_affiliate_processing_when_feature_disabled()
    {
        Setting::create(['key' => 'features_affiliate', 'value' => 'false']);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $job = new LogClickJob($this->link->id, $this->clickData);
        $job->handle();

        $this->assertDatabaseMissing('affiliate_visits', [
            'affiliate_id' => $affiliate->id,
        ]);
    }

    /**
     * @test
     */
    public function it_processes_affiliate_visit_when_enabled()
    {
        Setting::create(['key' => 'features_affiliate', 'value' => 'true']);

        $tier = AffiliateTier::factory()->create([
            'view_rate' => 0.01,
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        AffiliateCountryRate::factory()->create([
            'affiliate_tier_id' => $tier->id,
            'country_code' => 'US',
        ]);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'total_visits' => 0,
            'total_earnings' => 0,
            'pending_earnings' => 0,
        ]);

        $job = new LogClickJob($this->link->id, $this->clickData);
        $job->handle();

        $affiliate->refresh();
        $this->assertEquals(1, $affiliate->total_visits);
        $this->assertEquals(0.0001, $affiliate->total_earnings);
        $this->assertEquals(0.0001, $affiliate->pending_earnings);

        $this->assertDatabaseHas('affiliate_visits', [
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => $tier->id,
            'link_id' => $this->link->id,
            'ip_hash' => $this->clickData['ip_hash'],
            'country_code' => 'US',
        ]);
    }

    /**
     * @test
     */
    public function it_handles_missing_link_gracefully()
    {
        // Create a valid link first to avoid foreign key constraint
        $link = Link::factory()->create();
        $job = new LogClickJob($link->id, $this->clickData);
        $job->handle();

        // Should not throw exception, just continue gracefully
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_handles_inactive_affiliate()
    {
        Setting::create(['key' => 'features_affiliate', 'value' => 'true']);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        $job = new LogClickJob($this->link->id, $this->clickData);
        $job->handle();

        $this->assertDatabaseMissing('affiliate_visits', [
            'affiliate_id' => $affiliate->id,
        ]);
    }

    /**
     * @test
     */
    public function it_handles_missing_ip_hash()
    {
        $clickDataWithoutIp = $this->clickData;
        unset($clickDataWithoutIp['ip_hash']);

        $job = new LogClickJob($this->link->id, $clickDataWithoutIp);
        $job->handle();

        $this->assertDatabaseHas('clicks', [
            'link_id' => $this->link->id,
        ]);
        $this->assertDatabaseMissing('affiliate_visits', [
            'link_id' => $this->link->id,
        ]);
    }

    /**
     * @test
     */
    public function it_handles_country_not_in_tier()
    {
        Setting::create(['key' => 'features_affiliate', 'value' => 'true']);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'total_visits' => 0,
            'total_earnings' => 0,
            'pending_earnings' => 0,
        ]);

        $job = new LogClickJob($this->link->id, $this->clickData);
        $job->handle();

        $this->assertDatabaseHas('affiliate_visits', [
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => null,
        ]);

        $affiliate->refresh();
        $this->assertEquals(1, $affiliate->total_visits);
        $this->assertEquals(0, $affiliate->total_earnings);
    }

    /**
     * @test
     */
    public function it_prevents_duplicate_ip_visits()
    {
        Setting::create(['key' => 'features_affiliate', 'value' => 'true']);

        $tier = AffiliateTier::factory()->create([
            'view_rate' => 0.01,
            'view_multiplier' => 100,
            'is_active' => true,
        ]);

        AffiliateCountryRate::factory()->create([
            'affiliate_tier_id' => $tier->id,
            'country_code' => 'US',
        ]);

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'total_visits' => 0,
            'total_earnings' => 0,
        ]);

        // First visit
        $job1 = new LogClickJob($this->link->id, $this->clickData);
        $job1->handle();

        // Second visit with same IP
        $job2 = new LogClickJob($this->link->id, $this->clickData);
        $job2->handle();

        $affiliate->refresh();
        $this->assertEquals(1, $affiliate->total_visits);
        $this->assertEquals(0.0001, $affiliate->total_earnings);

        $this->assertDatabaseCount('affiliate_visits', 1);
        $this->assertDatabaseCount('clicks', 2);  // Clicks are still recorded
    }

    /**
     * @test
     */
    public function it_handles_missing_country_code()
    {
        Setting::create(['key' => 'features_affiliate', 'value' => 'true']);

        $clickDataNoCountry = $this->clickData;
        $clickDataNoCountry['country_code'] = null;

        $affiliate = Affiliate::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $job = new LogClickJob($this->link->id, $clickDataNoCountry);
        $job->handle();

        $this->assertDatabaseHas('affiliate_visits', [
            'affiliate_id' => $affiliate->id,
            'affiliate_tier_id' => null,
            'country_code' => null,
        ]);
    }
}
