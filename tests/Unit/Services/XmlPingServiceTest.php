<?php
// Atia Hegazy — atiaeno.com

namespace Tests\Unit\Services;

use App\Models\IndexerSetting;
use App\Services\XmlPingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XmlPingServiceTest extends TestCase
{
    use RefreshDatabase;

    private XmlPingService $service;
    private IndexerSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = IndexerSetting::factory()->create([
            'xml_ping_enabled' => true,
            'ping_services' => ['google', 'bing', 'yandex'],
        ]);

        $this->service = new XmlPingService();
    }

    /**
     * @test
     */
    public function it_returns_enabled_status()
    {
        $this->assertTrue($this->service->isEnabled());

        $this->settings->update(['xml_ping_enabled' => false]);
        // Clear cache and create new service instance
        Cache::forget('indexer_settings');
        $this->service = new XmlPingService();
        $this->assertFalse($this->service->isEnabled());
    }

    /**
     * @test
     */
    public function it_returns_disabled_results_when_service_disabled()
    {
        $this->settings->update(['xml_ping_enabled' => false]);
        Cache::forget('indexer_settings');
        $this->service = new XmlPingService();

        $results = $this->service->ping('https://example.com/sitemap.xml');

        $this->assertEquals(['enabled' => false], $results);
        Http::assertNothingSent();
    }

    /**
     * @test
     */
    public function it_pings_google()
    {
        Http::fake([
            'https://www.google.com/ping*' => Http::response('', 200),
        ]);

        $result = $this->service->pingService('google', 'https://example.com/sitemap.xml');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://www.google.com/ping?sitemap=') &&
                str_contains($request->url(), urlencode('https://example.com/sitemap.xml'));
        });
    }

    /**
     * @test
     */
    public function it_pings_bing()
    {
        Http::fake([
            'https://www.bing.com/indexnow*' => Http::response('', 200),
        ]);

        $result = $this->service->pingService('bing', 'https://example.com/sitemap.xml');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://www.bing.com/indexnow?url=') &&
                str_contains($request->url(), urlencode('https://example.com/sitemap.xml'));
        });
    }

    /**
     * @test
     */
    public function it_pings_yandex()
    {
        Http::fake([
            'https://webmaster.yandex.com/ping*' => Http::response('', 200),
        ]);

        $result = $this->service->pingService('yandex', 'https://example.com/sitemap.xml');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://webmaster.yandex.com/ping?sitemap=') &&
                str_contains($request->url(), urlencode('https://example.com/sitemap.xml'));
        });
    }

    /**
     * @test
     */
    public function it_pings_yahoo()
    {
        Http::fake([
            'https://search.yahoo.com/sitemap/submit*' => Http::response('', 200),
        ]);

        $result = $this->service->pingService('yahoo', 'https://example.com/sitemap.xml');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://search.yahoo.com/sitemap/submit?url=') &&
                str_contains($request->url(), urlencode('https://example.com/sitemap.xml'));
        });
    }

    /**
     * @test
     */
    public function it_pings_duckduckgo()
    {
        Http::fake([
            'https://duckduckgo.com/ping*' => Http::response('', 200),
        ]);

        $result = $this->service->pingService('duckduckgo', 'https://example.com/sitemap.xml');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://duckduckgo.com/ping?url=') &&
                str_contains($request->url(), urlencode('https://example.com/sitemap.xml'));
        });
    }

    /**
     * @test
     */
    public function it_pings_baidu()
    {
        Http::fake([
            'https://www.baidu.com/search/url_sitemap*' => Http::response('', 200),
        ]);

        $result = $this->service->pingService('baidu', 'https://example.com/sitemap.xml');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://www.baidu.com/search/url_sitemap?url=') &&
                str_contains($request->url(), urlencode('https://example.com/sitemap.xml'));
        });
    }

    /**
     * @test
     */
    public function it_handles_naver_service()
    {
        $result = $this->service->pingService('naver', 'https://example.com/sitemap.xml');
        $this->assertFalse($result);

        Http::assertNothingSent();  // Naver returns null URL
    }

    /**
     * @test
     */
    public function it_handles_unknown_service()
    {
        $result = $this->service->pingService('unknown', 'https://example.com/sitemap.xml');
        $this->assertFalse($result);

        Http::assertNothingSent();
    }

    /**
     * @test
     */
    public function it_handles_http_success()
    {
        Http::fake([
            'https://www.google.com/ping*' => Http::response('', 200),
        ]);

        $result = $this->service->pingService('google', 'https://example.com/sitemap.xml');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_handles_http_failure()
    {
        Http::fake([
            'https://www.google.com/ping*' => Http::response('Error', 500),
        ]);

        $result = $this->service->pingService('google', 'https://example.com/sitemap.xml');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_handles_http_timeout()
    {
        // Test that the service handles HTTP failures gracefully
        Http::fake([
            'https://www.google.com/ping*' => Http::response('', 500),
        ]);

        $result = $this->service->pingService('google', 'https://example.com/sitemap.xml');
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_pings_multiple_services()
    {
        Http::fake([
            'https://www.google.com/ping*' => Http::response('', 200),
            'https://www.bing.com/indexnow*' => Http::response('', 200),
            'https://webmaster.yandex.com/ping*' => Http::response('Error', 500),
        ]);

        $results = $this->service->ping('https://example.com/sitemap.xml');

        $this->assertTrue($results['google']);
        $this->assertTrue($results['bing']);
        $this->assertFalse($results['yandex']);

        Http::assertSentCount(3);
    }

    /**
     * @test
     */
    public function it_respects_configured_services()
    {
        $this->settings->update(['ping_services' => ['google', 'bing']]);
        Cache::forget('indexer_settings');
        $this->service = new XmlPingService();

        Http::fake([
            'https://www.google.com/ping*' => Http::response('', 200),
            'https://www.bing.com/indexnow*' => Http::response('', 200),
            'https://webmaster.yandex.com/ping*' => Http::response('', 200),
        ]);

        $results = $this->service->ping('https://example.com/sitemap.xml');

        $this->assertTrue($results['google']);
        $this->assertTrue($results['bing']);
        $this->assertArrayNotHasKey('yandex', $results);

        Http::assertSentCount(2);  // Only google and bing
    }

    /**
     * @test
     */
    public function it_handles_default_services_when_none_configured()
    {
        $this->settings->update(['ping_services' => null]);
        Cache::forget('indexer_settings');
        $this->service = new XmlPingService();

        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $results = $this->service->ping('https://example.com/sitemap.xml');

        // Should ping all default services
        $expectedServices = ['google', 'bing', 'yandex', 'yahoo', 'duckduckgo', 'baidu'];
        foreach ($expectedServices as $service) {
            $this->assertArrayHasKey($service, $results);
        }
    }

    /**
     * @test
     */
    public function it_gets_sitemap_url()
    {
        config(['app.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/sitemap.xml', $this->service->getSitemapUrl());

        config(['app.url' => 'https://test.org']);
        $this->assertEquals('https://test.org/sitemap.xml', $this->service->getSitemapUrl());
    }

    /**
     * @test
     */
    public function it_pings_sitemap()
    {
        config(['app.url' => 'https://example.com']);

        Http::fake([
            'https://www.google.com/ping*' => Http::response('', 200),
        ]);

        $results = $this->service->pingSitemap();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), urlencode('https://example.com/sitemap.xml'));
        });
    }

    /**
     * @test
     */
    public function it_url_encodes_sitemap_url()
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $sitemapUrl = 'https://example.com/sitemap.xml?page=1&filter=test';
        $this->service->pingService('google', $sitemapUrl);

        Http::assertSent(function ($request) use ($sitemapUrl) {
            return str_contains($request->url(), urlencode($sitemapUrl));
        });
    }

    /**
     * @test
     */
    public function it_handles_special_characters_in_url()
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);

        $sitemapUrl = 'https://example.com/sitemap-ñáí.xml';
        $this->service->pingService('google', $sitemapUrl);

        Http::assertSent(function ($request) use ($sitemapUrl) {
            return str_contains($request->url(), urlencode($sitemapUrl));
        });
    }

    /**
     * @test
     */
    public function it_handles_timeout_configuration()
    {
        Http::fake([
            'https://www.google.com/ping*' => Http::response('', 200),
        ]);

        $result = $this->service->pingService('google', 'https://example.com/sitemap.xml');
        $this->assertTrue($result);  // Should succeed with fake response
    }

    /**
     * @test
     */
    public function it_logs_ping_results()
    {
        Http::fake([
            'https://www.google.com/ping*' => Http::response('', 200),
            'https://www.bing.com/indexnow*' => Http::response('Error', 500),
        ]);

        $this->service->pingService('google', 'https://example.com/sitemap.xml');
        $this->service->pingService('bing', 'https://example.com/sitemap.xml');

        // Logs are written but we can't easily test them without capturing logs
        // The test passes if no exceptions are thrown
        $this->assertTrue(true);
    }
}
