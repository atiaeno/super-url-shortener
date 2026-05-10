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
            default => null,
        };
    }

    public function getSitemapUrl(): string
    {
        return config('app.url') . '/sitemap.xml';
    }

    public function pingSitemap(): array
    {
        return $this->ping($this->getSitemapUrl());
    }
}
