<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Jobs;

use App\Jobs\AnonymizeIpJob;
use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymizeIpJobTest extends TestCase
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
    public function it_anonymizes_old_ip_hashes()
    {
        // Create recent click (should not be anonymized)
        $recentClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(10),
            'ip_hash' => hash('sha256', '192.168.1.1'),
        ]);

        // Create old click (should be anonymized)
        $oldClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(40),  // Older than default 30 days
            'ip_hash' => hash('sha256', '192.168.1.2'),
        ]);

        $job = new AnonymizeIpJob();
        $job->handle();

        $recentClick->refresh();
        $oldClick->refresh();

        // Recent click should remain unchanged
        $this->assertEquals(hash('sha256', '192.168.1.1'), $recentClick->ip_hash);

        // Old click should be anonymized
        $expectedAnonymized = hash('sha256', 'anonymized-' . $oldClick->id);
        $this->assertEquals($expectedAnonymized, $oldClick->ip_hash);
    }

    /**
     * @test
     */
    public function it_respects_custom_retention_days()
    {
        config(['app.ip_retention_days' => 15]);

        // Create click 20 days old (should be anonymized with 15-day retention)
        $oldClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(20),
            'ip_hash' => hash('sha256', '192.168.1.3'),
        ]);

        // Create click 10 days old (should not be anonymized with 15-day retention)
        $recentClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(10),
            'ip_hash' => hash('sha256', '192.168.1.4'),
        ]);

        $job = new AnonymizeIpJob();
        $job->handle();

        $oldClick->refresh();
        $recentClick->refresh();

        $this->assertNotEquals(hash('sha256', '192.168.1.3'), $oldClick->ip_hash);
        $this->assertEquals(hash('sha256', '192.168.1.4'), $recentClick->ip_hash);
    }

    /**
     * @test
     */
    public function it_handles_null_ip_hashes()
    {
        $clickWithNullIp = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(40),
            'ip_hash' => null,
        ]);

        $job = new AnonymizeIpJob();
        $job->handle();

        $clickWithNullIp->refresh();
        $this->assertNull($clickWithNullIp->ip_hash);
    }

    /**
     * @test
     */
    public function it_processes_in_chunks()
    {
        // Create many old clicks to test chunking
        $clicks = Click::factory()->count(1500)->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(40),
        ]);

        $originalHashes = $clicks->pluck('ip_hash');

        $job = new AnonymizeIpJob();
        $job->handle();

        $clicks->each(function ($click) use ($originalHashes) {
            $click->refresh();
            $originalHash = $originalHashes->firstWhere('id', $click->id);
            $this->assertNotEquals($originalHash, $click->ip_hash);

            $expectedAnonymized = hash('sha256', 'anonymized-' . $click->id);
            $this->assertEquals($expectedAnonymized, $click->ip_hash);
        });
    }

    /**
     * @test
     */
    public function it_handles_empty_click_table()
    {
        $job = new AnonymizeIpJob();
        $job->handle();

        $this->assertTrue(true);  // Should not throw any errors
    }

    /**
     * @test
     */
    public function it_only_anonymizes_clicks_older_than_cutoff()
    {
        $cutoffTime = now()->subDays(30);

        // Create click exactly at cutoff (should not be anonymized)
        $cutoffClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => $cutoffTime,
            'ip_hash' => hash('sha256', '192.168.1.5'),
        ]);

        // Create click just after cutoff (should not be anonymized)
        $afterCutoffClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => $cutoffTime->copy()->addSecond(),
            'ip_hash' => hash('sha256', '192.168.1.6'),
        ]);

        // Create click just before cutoff (should be anonymized)
        $beforeCutoffClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => $cutoffTime->copy()->subSecond(),
            'ip_hash' => hash('sha256', '192.168.1.7'),
        ]);

        $job = new AnonymizeIpJob();
        $job->handle();

        $cutoffClick->refresh();
        $afterCutoffClick->refresh();
        $beforeCutoffClick->refresh();

        $this->assertEquals(hash('sha256', '192.168.1.5'), $cutoffClick->ip_hash);
        $this->assertEquals(hash('sha256', '192.168.1.6'), $afterCutoffClick->ip_hash);

        $expectedAnonymized = hash('sha256', 'anonymized-' . $beforeCutoffClick->id);
        $this->assertEquals($expectedAnonymized, $beforeCutoffClick->ip_hash);
    }
}
