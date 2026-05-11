<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Jobs;

use App\Jobs\DeleteOldClicksJob;
use App\Models\Click;
use App\Models\Link;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOldClicksJobTest extends TestCase
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

    /**
     * @test
     */
    public function it_deletes_old_clicks_based_on_default_retention()
    {
        // Create recent click (should not be deleted)
        $recentClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(100),
        ]);

        // Create very old click (should be deleted)
        $oldClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(400),  // Older than default 365 days
        ]);

        $job = new DeleteOldClicksJob();
        $job->handle();

        $this->assertDatabaseHas('clicks', ['id' => $recentClick->id]);
        $this->assertDatabaseMissing('clicks', ['id' => $oldClick->id]);
    }

    /**
     * @test
     */
    public function it_respects_custom_retention_days_from_settings()
    {
        Setting::create(['key' => 'gdpr_data_retention_days', 'value' => '180']);

        // Create click 200 days old (should be deleted with 180-day retention)
        $oldClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(200),
        ]);

        // Create click 100 days old (should not be deleted with 180-day retention)
        $recentClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(100),
        ]);

        $job = new DeleteOldClicksJob();
        $job->handle();

        $this->assertDatabaseMissing('clicks', ['id' => $oldClick->id]);
        $this->assertDatabaseHas('clicks', ['id' => $recentClick->id]);
    }

    /**
     * @test
     */
    public function it_handles_zero_retention_days()
    {
        Setting::create(['key' => 'gdpr_data_retention_days', 'value' => '0']);

        // Create some clicks
        $clicks = Click::factory()->count(5)->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(100),
        ]);

        $job = new DeleteOldClicksJob();
        $job->handle();

        // With zero retention, no clicks should be deleted
        $clicks->each(function ($click) {
            $this->assertDatabaseHas('clicks', ['id' => $click->id]);
        });
    }

    /**
     * @test
     */
    public function it_handles_negative_retention_days()
    {
        Setting::create(['key' => 'gdpr_data_retention_days', 'value' => '-1']);

        // Create some clicks
        $clicks = Click::factory()->count(5)->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(100),
        ]);

        $job = new DeleteOldClicksJob();
        $job->handle();

        // With negative retention, no clicks should be deleted
        $clicks->each(function ($click) {
            $this->assertDatabaseHas('clicks', ['id' => $click->id]);
        });
    }

    /**
     * @test
     */
    public function it_processes_in_chunks()
    {
        // Create many old clicks to test chunking
        $clicks = Click::factory()->count(2500)->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(400),
        ]);

        $job = new DeleteOldClicksJob();
        $job->handle();

        // All old clicks should be deleted
        $clicks->each(function ($click) {
            $this->assertDatabaseMissing('clicks', ['id' => $click->id]);
        });
    }

    /**
     * @test
     */
    public function it_handles_empty_click_table()
    {
        $job = new DeleteOldClicksJob();
        $job->handle();

        $this->assertTrue(true);  // Should not throw any errors
    }

    /**
     * @test
     */
    public function it_only_deletes_clicks_older_than_cutoff()
    {
        $cutoffTime = now()->subDays(365);

        // Create click exactly at cutoff (should not be deleted)
        $cutoffClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => $cutoffTime,
        ]);

        // Create click just after cutoff (should not be deleted)
        $afterCutoffClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => $cutoffTime->copy()->addSecond(),
        ]);

        // Create click just before cutoff (should be deleted)
        $beforeCutoffClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => $cutoffTime->copy()->subSecond(),
        ]);

        $job = new DeleteOldClicksJob();
        $job->handle();

        $this->assertDatabaseHas('clicks', ['id' => $cutoffClick->id]);
        $this->assertDatabaseHas('clicks', ['id' => $afterCutoffClick->id]);
        $this->assertDatabaseMissing('clicks', ['id' => $beforeCutoffClick->id]);
    }

    /**
     * @test
     */
    public function it_handles_missing_setting_key()
    {
        // Don't create the setting, should use default of 365 days

        // Create click 400 days old (should be deleted)
        $oldClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(400),
        ]);

        // Create click 300 days old (should not be deleted)
        $recentClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(300),
        ]);

        $job = new DeleteOldClicksJob();
        $job->handle();

        $this->assertDatabaseMissing('clicks', ['id' => $oldClick->id]);
        $this->assertDatabaseHas('clicks', ['id' => $recentClick->id]);
    }

    /**
     * @test
     */
    public function it_handles_string_retention_days()
    {
        Setting::create(['key' => 'gdpr_data_retention_days', 'value' => '200']);

        // Create click 250 days old (should be deleted)
        $oldClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(250),
        ]);

        // Create click 150 days old (should not be deleted)
        $recentClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(150),
        ]);

        $job = new DeleteOldClicksJob();
        $job->handle();

        $this->assertDatabaseMissing('clicks', ['id' => $oldClick->id]);
        $this->assertDatabaseHas('clicks', ['id' => $recentClick->id]);
    }
}
