<?php

// © Atia Hegazy — atiaeno.com

namespace App\Services;

use App\Models\IndexerLog;
use App\Models\IndexerSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XmlPingService
{
    private const PING_SERVICES = [
        'google' => 'https://www.google.com/ping?sitemap=',
        'bing' => 'https://www.bing.com/indexnow?url=',
        'yandex' => 'https://webmaster.yandex.com/ping?sitemap=',
        'yahoo' => 'https://search.yahoo.com/sitemap/submit?url=',
        'duckduckgo' => 'https://duckduckgo.com/ping?url=',
        'baidu' => 'https://www.baidu.com/search/url_sitemap?url=',
        'naver' => 'https://search.naver.com/search.naver?sm=tab_htn&where=nexearch&query=site:',
    ];

    private IndexerSetting $settings;

    public function __construct()
    {
        $this->settings = IndexerSetting::getSettings();
    }

    public function isEnabled(): bool
    {
        return $this->settings->xml_ping_enabled;
    }

    public function ping(string $sitemapUrl): array
    {
        if (!$this->isEnabled()) {
            return ['enabled' => false];
        }

        $results = [];
        $services = $this->settings->ping_services ?? array_keys(self::PING_SERVICES);

        foreach ($services as $service) {
            if (isset(self::PING_SERVICES[$service])) {
                $results[$service] = $this->pingService($service, $sitemapUrl);
            }
        }

        return $results;
    }

    public function pingService(string $service, string $sitemapUrl): bool
    {
        $pingUrl = $this->buildPingUrl($service, $sitemapUrl);

        if (!$pingUrl) {
            return false;
        }

        try {
            $response = Http::timeout(10)->get($pingUrl);

            $status = $response->successful() ? 'success' : 'failed';

            Log::info("XML Ping: {$service} result", [
                'url' => $pingUrl,
                'status' => $status,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("XML Ping: {$service} exception", [
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private function buildPingUrl(string $service, string $sitemapUrl): ?string
    {
        $encodedUrl = urlencode($sitemapUrl);

        return match ($service) {
            'google' => self::PING_SERVICES['google'] . $encodedUrl,
            'bing' => self::PING_SERVICES['bing'] . $encodedUrl,
            'yandex' => self::PING_SERVICES['yandex'] . $encodedUrl,
            'yahoo' => self::PING_SERVICES['yahoo'] . $encodedUrl,
            'duckduckgo' => self::PING_SERVICES['duckduckgo'] . $encodedUrl,
            'baidu' => self::PING_SERVICES['baidu'] . $encodedUrl,
            'naver' => null,  // Naver requires different approach - just returns base URL
            default => null,
        };
    }

    public function getSitemapUrl(): string
    {
        return config('app.url') . '/sitemap.xml';
    }

    public function pingSitemap(): array
    {
        $sitemapUrl = $this->getSitemapUrl();

        // Ping sitemap URL
        $results = $this->ping($sitemapUrl);

        // Also submit all URLs from sitemap to search engines
        $this->submitAllUrlsFromSitemap($sitemapUrl);

        return $results;
    }

    private function submitAllUrlsFromSitemap(string $sitemapUrl): void
    {
        try {
            $response = Http::timeout(30)->get($sitemapUrl);

            if (!$response->successful()) {
                return;
            }

            $xml = simplexml_load_string($response->body());

            if (!$xml || !isset($xml->url)) {
                return;
            }

            $urls = [];
            foreach ($xml->url as $url) {
                if (isset($url->loc)) {
                    $urls[] = (string) $url->loc;
                }
            }

            if (empty($urls)) {
                return;
            }

            $services = $this->settings->ping_services ?? array_keys(self::PING_SERVICES);

            foreach ($services as $service) {
                if (in_array($service, ['google', 'bing'])) {
                    // Use IndexNow for Google and Bing
                    $indexNowService = new \App\Services\IndexNowService();

                    if ($service === 'bing' && $indexNowService->isEnabled()) {
                        $indexNowService->submitBatch($urls, 'bing');
                    }

                    // Google uses URL Inspection API - just ping the sitemap
                    if ($service === 'google') {
                        $this->pingService('google', $sitemapUrl);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('XML Ping: Failed to submit URLs from sitemap', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
