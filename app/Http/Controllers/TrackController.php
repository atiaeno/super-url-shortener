<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Jobs\LogClickJob;
use App\Models\Link;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class TrackController extends Controller
{
    /**
     * Track a click via JavaScript beacon.
     * Lightweight endpoint - just queues the job and returns 204.
     */
    public function track(string $shortCode, Request $request)
    {
        // Find link
        $link = Link::where(function ($q) use ($shortCode) {
            $q
                ->where('short_code', $shortCode)
                ->orWhere('custom_alias', $shortCode);
        })->first();

        // Silently ignore invalid links (don't leak info)
        if (!$link || !$link->is_active) {
            return response()->noContent();
        }

        // Skip if expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            return response()->noContent();
        }

        // Get click data from request
        $clickData = $this->getClickData($request);

        // Get analytics counting mode from settings
        $countMode = \App\Models\Setting::get('analytics_count_mode') ?? 'unique';
        $shouldCount = true;

        // If unique mode, check for existing click from same IP hash in last 24 hours
        if ($countMode === 'unique') {
            $ipHash = $clickData['ip_hash'] ?? null;
            $existingClick = null;

            if ($ipHash) {
                $existingClick = \App\Models\Click::where('link_id', $link->id)
                    ->where('ip_hash', $ipHash)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->first();
            }

            $shouldCount = !$existingClick;
        }

        // Only queue click logging and increment count if should count
        if ($shouldCount) {
            // Queue click logging (async)
            LogClickJob::dispatch($link->id, $clickData);

            // Increment clicks count
            $link->incrementClicks();
        }

        return response()->noContent();
    }

    /**
     * Get click analytics data from request.
     */
    private function getClickData(Request $request): array
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent() ?? '');

        $referrer = $request->headers->get('referer');
        $referrerDomain = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;

        return [
            'ip_hash' => hash('sha256', $request->ip()),
            'country_code' => $this->getCountryCode($request),
            'device_type' => $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop'),
            'browser' => $agent->browser(),
            'os' => $agent->platform(),
            'referrer' => $referrer,
            'referrer_domain' => $referrerDomain,
        ];
    }

    /**
     * Get country code from request headers (CloudFlare) or IP.
     */
    private function getCountryCode(Request $request): ?string
    {
        $ip = $request->ip();
        if (!$ip) {
            return null;
        }

        // Try CloudFlare header first
        $cfCountry = $request->header('CF-IPCountry');
        if ($cfCountry && $cfCountry !== 'XX' && $cfCountry !== 'T1') {
            return $cfCountry;
        }

        // Fallback to free ip-api.com (45 requests/min)
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(2)
                ->get("http://ip-api.com/json/{$ip}?fields=countryCode");
            if ($response->successful()) {
                return $response->json('countryCode');
            }
        } catch (\Throwable $e) {
            // Fail silently - return null
        }

        return null;
    }
}
