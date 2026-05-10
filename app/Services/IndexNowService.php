<?php

// © Atia Hegazy — atiaeno.com

namespace App\Services;

use App\Models\IndexerLog;
use App\Models\IndexerSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    private const INDEXNOW_HOSTS = [
        'bing' => 'www.bing.com',
        'yandex' => 'yandex.com',
    ];

    private const INDEXNOW_ENDPOINT = 'https://api.indexnow.org/IndexNow';

    private IndexerSetting $settings;

    public function __construct()
    {
        $this->settings = IndexerSetting::getSettings();
    }

    public function isEnabled(): bool
    {
        return $this->settings->indexnow_enabled;
    }

    public function getHost(): string
    {
        return config('app.url', 'example.com');
    }

    public function submitUrl(string $url, string $host = 'bing'): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $targetHost = self::INDEXNOW_HOSTS[$host] ?? $host;

        return $this->submitToIndexNow($url, $this->getHost(), $targetHost);
    }

    public function submitBatch(array $urls, string $host = 'bing'): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => count($urls),
        ];

        foreach ($urls as $url) {
            if ($this->submitUrl($url, $host)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            usleep(100000);
        }

        return $results;
    }

    private function submitToIndexNow(string $url, string $host, string $targetHost): bool
    {
        try {
            $payload = [
                'host' => $host,
                'key' => $this->generateKey(),
                'urlList' => [$url],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(self::INDEXNOW_ENDPOINT, $payload);

            $status = $response->successful() ? 'success' : 'failed';

            Log::info('IndexNow: Submission result', [
                'url' => $url,
                'status' => $status,
                'response' => $response->body(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('IndexNow: Exception submitting URL', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private function generateKey(): string
    {
        return config('indexnow.key', config('app.key'));
    }

    public function submitToAll(string $url): array
    {
        $results = [];

        foreach (array_keys(self::INDEXNOW_HOSTS) as $host) {
            $results[$host] = $this->submitUrl($url, $host);
        }

        return $results;
    }
}
