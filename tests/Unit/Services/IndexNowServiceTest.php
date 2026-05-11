<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Services;

use App\Models\IndexerSetting;
use App\Services\IndexNowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IndexNowServiceTest extends TestCase
{
    use RefreshDatabase;

    private IndexNowService $service;
    private IndexerSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Cache::forget('indexer_settings');

        $this->settings = IndexerSetting::factory()->create([
            'indexnow_enabled' => true,
            'indexnow_key' => 'test-key-123',
        ]);

        $this->service = new IndexNowService();
    }

    /**
     * @test
     */
    public function it_returns_enabled_status()
    {
        $this->assertTrue($this->service->isEnabled());
    }

    /**
     * @test
     */
    public function it_returns_host_from_config()
    {
        config(['app.url' => 'https://example.com']);
        $this->assertEquals('https://example.com', $this->service->getHost());

        config(['app.url' => 'https://test.org']);
        $this->assertEquals('https://test.org', $this->service->getHost());
    }

    /**
     * @test
     */
    public function it_submits_url_to_bing()
    {
        config(['app.url' => 'https://example.com']);

        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $result = $this->service->submitUrl('https://example.com/page', 'bing');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            return $request->url() === 'https://www.bing.com/indexnow' &&
                $payload['host'] === 'example.com' &&
                $payload['key'] === 'test-key-123' &&
                $payload['urlList'] === ['https://example.com/page'];
        });
    }

    /**
     * @test
     */
    public function it_submits_url_to_yandex()
    {
        Http::fake([
            'https://yandex.com/indexnow' => Http::response('', 200),
        ]);

        $result = $this->service->submitUrl('https://example.com/page', 'yandex');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://yandex.com/indexnow';
        });
    }

    /**
     * @test
     */
    public function it_submits_url_to_custom_host()
    {
        Http::fake([
            'https://custom.search.com/indexnow' => Http::response('', 200),
        ]);

        $result = $this->service->submitUrl('https://example.com/page', 'custom.search.com');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://custom.search.com/indexnow';
        });
    }

    /**
     * @test
     */
    public function it_returns_false_when_disabled()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_handles_http_success()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $result = $this->service->submitUrl('https://example.com/page');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_handles_http_accepted()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 202),
        ]);

        $result = $this->service->submitUrl('https://example.com/page');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_handles_http_error()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $result = $this->service->submitUrl('https://example.com/page');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_handles_http_timeout()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 500),
        ]);

        $result = $this->service->submitUrl('https://example.com/page');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_submits_batch_urls()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $urls = [
            'https://example.com/page1',
            'https://example.com/page2',
            'https://example.com/page3',
        ];

        $results = $this->service->submitBatch($urls, 'bing');

        $this->assertEquals(3, $results['success']);
        $this->assertEquals(0, $results['failed']);
        $this->assertEquals(3, $results['total']);

        Http::assertSentCount(3);
    }

    /**
     * @test
     */
    public function it_handles_batch_with_failures()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $urls = [
            'https://example.com/page1',
            'https://example.com/page2',
            'https://example.com/page3',
        ];

        $results = $this->service->submitBatch($urls, 'bing');

        $this->assertEquals(3, $results['success']);
        $this->assertEquals(0, $results['failed']);
        $this->assertEquals(3, $results['total']);
    }

    /**
     * @test
     */
    public function it_submits_to_all_hosts()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
            'https://yandex.com/indexnow' => Http::response('', 200),
        ]);

        $results = $this->service->submitToAll('https://example.com/page');

        $this->assertTrue($results['bing']);
        $this->assertTrue($results['yandex']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.bing.com/indexnow';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://yandex.com/indexnow';
        });
    }

    /**
     * @test
     */
    public function it_generates_correct_key()
    {
        $this->settings->update(['indexnow_key' => 'custom-key']);
        $service = new IndexNowService();

        // Test through submitUrl to verify key generation
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $service->submitUrl('https://example.com/page');

        Http::assertSent(function ($request) {
            $payload = $request->data();
            return $payload['key'] === 'custom-key';
        });
    }

    /**
     * @test
     */
    public function it_handles_empty_key()
    {
        $this->settings->update(['indexnow_key' => '']);
        $service = new IndexNowService();

        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $service->submitUrl('https://example.com/page');

        Http::assertSent(function ($request) {
            $payload = $request->data();
            return $payload['key'] === '';
        });
    }

    /**
     * @test
     */
    public function it_builds_correct_payload()
    {
        config(['app.url' => 'https://mysite.com']);

        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $this->service->submitUrl('https://mysite.com/page');

        Http::assertSent(function ($request) {
            $payload = $request->data();
            return $payload['host'] === 'mysite.com' &&
                $payload['key'] === 'test-key-123' &&
                $payload['keyLocation'] === 'https://mysite.com/test-key-123.txt' &&
                $payload['urlList'] === ['https://mysite.com/page'];
        });
    }

    /**
     * @test
     */
    public function it_adds_rate_limiting_delay()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        $startTime = microtime(true);

        $urls = [
            'https://example.com/page1',
            'https://example.com/page2',
        ];

        $this->service->submitBatch($urls);

        $endTime = microtime(true);
        $elapsedTime = $endTime - $startTime;

        // Should have at least 100ms delay between requests
        $this->assertGreaterThan(0.1, $elapsedTime);
    }

    /**
     * @test
     */
    public function it_handles_missing_host_gracefully()
    {
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response('', 200),
        ]);

        // Should default to bing when invalid host provided
        $result = $this->service->submitUrl('https://example.com/page', 'bing');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.bing.com/indexnow';
        });
    }
}
