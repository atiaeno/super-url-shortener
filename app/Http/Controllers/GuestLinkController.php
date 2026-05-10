<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Jobs\FetchOgTagsJob;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GuestLinkController extends Controller
{
    private const BLOCKED_DOMAINS_CACHE_KEY = 'blocked_domains';
    private const BLOCKED_DOMAINS_TTL = 3600;

    /**
     * Shorten a URL as a guest (no auth required).
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $url = $validated['url'];

        // Malicious URL check
        if ($this->isMalicious($url)) {
            return response()->json([
                'error' => 'This URL has been flagged as potentially harmful and cannot be shortened.',
            ], 422);
        }

        // Check if we already have a guest link for this URL (dedup)
        $existing = Link::whereNull('user_id')
            ->where('destination_url', $url)
            ->active()
            ->first();

        if ($existing) {
            return response()->json([
                'id' => $existing->id,
                'short_url' => $existing->short_url,
                'short_code' => $existing->short_code,
            ]);
        }

        $link = Link::create([
            'user_id' => null,
            'short_code' => $this->generateCode(),
            'destination_url' => $url,
            'is_active' => true,
        ]);

        // Fetch OG tags asynchronously
        FetchOgTagsJob::dispatch($link->id)->onQueue('default');

        return response()->json([
            'id' => $link->id,
            'short_url' => $link->short_url,
            'short_code' => $link->short_code,
        ]);
    }

    /**
     * Check URL against domain blocklist and Google Safe Browsing.
     */
    private function isMalicious(string $url): bool
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        // Strip www prefix for matching
        $domain = preg_replace('/^www\./', '', $host);

        // Check local blocklist cache
        $blocked = Cache::remember(
            self::BLOCKED_DOMAINS_CACHE_KEY,
            self::BLOCKED_DOMAINS_TTL,
            fn() => $this->getBlockedDomains()
        );

        if (in_array($domain, $blocked, true)) {
            return true;
        }

        // Google Safe Browsing API (FR48) — skip if key not configured
        $apiKey = config('services.google_safe_browsing.key');
        if ($apiKey) {
            try {
                $response = Http::timeout(5)->post(
                    "https://safebrowsing.googleapis.com/v4/threatMatches:find?key={$apiKey}",
                    [
                        'client' => ['clientId' => 'shortlink', 'clientVersion' => '1.0'],
                        'threatInfo' => [
                            'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE'],
                            'platformTypes' => ['ANY_PLATFORM'],
                            'threatEntryTypes' => ['URL'],
                            'threatEntries' => [['url' => $url]],
                        ],
                    ]
                );

                if ($response->successful() && !empty($response->json('matches'))) {
                    return true;
                }
            } catch (\Throwable) {
                // Fallback silently — don't block on API timeout
            }
        }

        return false;
    }

    /**
     * Return base blocked domain list (can be extended via DB settings).
     */
    private function getBlockedDomains(): array
    {
        return [
            'malware.com',
            'phishing.com',
            'bit.ly',
            'tinyurl.com',
        ];
    }

    private function generateCode(): string
    {
        do {
            $code = Str::random(7);
        } while (Link::where('short_code', $code)->exists());

        return $code;
    }
}
