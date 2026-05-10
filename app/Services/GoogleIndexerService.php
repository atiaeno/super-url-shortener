<?php

// © Atia Hegazy — atiaeno.com

namespace App\Services;

use App\Models\IndexerLog;
use App\Models\IndexerSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleIndexerService
{
    private const INDEXING_API_URL = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
    private const API_VERSION = 'v3';

    private ?string $accessToken = null;
    private IndexerSetting $settings;

    public function __construct()
    {
        $this->settings = IndexerSetting::getSettings();
    }

    public function isEnabled(): bool
    {
        return $this->settings->enabled && !empty($this->settings->google_service_account_json);
    }

    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $credentials = json_decode($this->settings->google_service_account_json, true);

        if (!$credentials) {
            Log::error('Google Indexer: Invalid service account JSON');
            return null;
        }

        $jwt = $this->createJwt($credentials);
        $tokenResponse = $this->requestAccessToken($jwt, $credentials);

        if ($tokenResponse && isset($tokenResponse['access_token'])) {
            $this->accessToken = $tokenResponse['access_token'];
            return $this->accessToken;
        }

        return null;
    }

    private function createJwt(array $credentials): string
    {
        $now = time();
        $exp = $now + 3600;

        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $exp,
        ]));

        $privateKey = $credentials['private_key'];
        $signature = '';
        openssl_sign("$header.$payload", $signature, $privateKey, 'RSA-SHA256');
        $signature = base64_encode($signature);

        return "$header.$payload.$signature";
    }

    private function requestAccessToken(string $jwt, array $credentials): ?array
    {
        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google Indexer: Failed to get access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Google Indexer: Exception getting access token', [
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function submitUrl(string $url, ?int $linkId = null): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            $this->logResult($linkId, 'failed', 'Failed to get access token');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post(self::INDEXING_API_URL, [
                'url' => $url,
                'type' => 'URL_UPDATED',
            ]);

            $responseData = $response->json();
            $status = $response->successful() ? 'success' : 'failed';

            $this->logResult(
                $linkId,
                $status,
                $responseData['notificationDispatch'] ?? $response->body(),
                $url
            );

            if ($response->successful()) {
                Log::info('Google Indexer: URL submitted successfully', ['url' => $url]);
                return true;
            }

            Log::error('Google Indexer: Failed to submit URL', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Google Indexer: Exception submitting URL', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            $this->logResult($linkId, 'error', $e->getMessage(), $url);
        }

        return false;
    }

    public function submitBatch(array $urls, ?int $linkId = null): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => count($urls),
        ];

        foreach ($urls as $url) {
            if ($this->submitUrl($url, $linkId)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            // Rate limiting - wait between requests
            usleep(100000); // 100ms delay
        }

        return $results;
    }

    public function removeUrl(string $url, ?int $linkId = null): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post(self::INDEXING_API_URL, [
                'url' => $url,
                'type' => 'URL_DELETED',
            ]);

            if ($response->successful()) {
                $this->logResult($linkId, 'deleted', 'URL removed from index', $url);
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Google Indexer: Exception removing URL', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private function logResult(?int $linkId, string $status, string $message, ?string $url = null): void
    {
        if ($linkId) {
            IndexerLog::create([
                'link_id' => $linkId,
                'service' => 'google',
                'response_status' => $status,
                'response_message' => $message,
                'request_url' => $url,
            ]);
        }
    }
}
