<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Jobs\LogClickJob;
use App\Models\Ad;
use App\Models\Link;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class RedirectController extends Controller
{
    /**
     * Handle short URL redirect.
     */
    public function __invoke(string $shortCode, Request $request)
    {
        // Look up the link (including inactive/expired for proper error pages)
        $link = Link::where('short_code', $shortCode)
            ->orWhere('custom_alias', $shortCode)
            ->first();

        // Not found at all
        if (!$link) {
            return response()->view('redirect', [
                'state' => 'not-found',
                'shortCode' => $shortCode,
            ], 404);
        }

        // Expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            return response()->view('redirect', [
                'state' => 'expired',
                'expiresAt' => $link->expires_at,
            ], 410);
        }

        // Deactivated
        if (!$link->is_active) {
            return response()->view('redirect', [
                'state' => 'not-found',
                'shortCode' => $shortCode,
            ], 404);
        }

        // Password protected — show password gate
        if ($link->visibility === 'private' && $link->password) {
            if ($request->isMethod('post')) {
                if (password_verify($request->input('password', ''), $link->password)) {
                    session(["unlocked:{$link->id}" => true]);
                } else {
                    return response()->view('redirect', [
                        'state' => 'password',
                        'shortCode' => $shortCode,
                        'error' => true,
                    ], 403);
                }
            } else {
                if (!session("unlocked:{$link->id}")) {
                    return response()->view('redirect', [
                        'state' => 'password',
                        'shortCode' => $shortCode,
                        'error' => false,
                    ], 403);
                }
            }
        }

        $destinationUrl = $link->destination_url;

        // Cache destination for next time (24h TTL)
        Cache::put("redirect:{$shortCode}", $destinationUrl, now()->addHours(24));

        // Story 1.7: Serve OG meta page for social crawlers (instant redirect via meta refresh)
        if ($this->isSocialCrawler($request->userAgent() ?? '')) {
            return response($this->buildOgPage($link, $destinationUrl), 200)
                ->header('Content-Type', 'text/html; charset=utf-8');
        }

        // Queue click logging (async, doesn't block)
        LogClickJob::dispatch($link->id, $this->getClickData($request));
        $link->incrementClicks();

        // Read redirect page settings
        $redirectCountdown = (int) Setting::get('redirect_countdown', 5);
        $redirectMode = Setting::get('redirect_mode', 'auto');
        $redirectCaptcha = Setting::get('redirect_captcha', false);

        // Story 5.4: Check for ad to display on the redirect page
        $ad = $this->getAdForLink($link, $request);
        $adContent = '';
        $countdown = $redirectCountdown;

        if ($ad && $ad->format === 'interstitial' && !$this->hasSeenAd($request, $link->id)) {
            $adContent = $ad->content ?? '';
            // Use ad's countdown if set, otherwise use global setting
            $countdown = (int) $ad->countdown_seconds ?: $redirectCountdown;
            $this->markAdSeen($request, $link->id);
        }

        // Get promotions for all placements
        $headerPromotion = Ad::active()->forPlacement('header')->inRandomOrder()->first();
        $footerPromotion = Ad::active()->forPlacement('footer')->inRandomOrder()->first();
        $sidebarPromotion = Ad::active()->forPlacement('sidebar')->inRandomOrder()->first();

        // Always show the redirect page — user sees destination, timer, and optional ad
        return response()->view('redirect', [
            'state' => 'redirect',
            'destination' => $destinationUrl,
            'countdown' => $countdown,
            'redirectMode' => $redirectMode,
            'redirectCaptcha' => filter_var($redirectCaptcha, FILTER_VALIDATE_BOOLEAN),
            'captchaSiteKey' => Setting::get('captcha_site_key', ''),
            'adContent' => $adContent,
            'adPlacement' => $ad ? $ad->placement : null,
            'adFormat' => $ad ? $ad->format : null,
            'headerPromotion' => $headerPromotion,
            'footerPromotion' => $footerPromotion,
            'sidebarPromotion' => $sidebarPromotion,
            'title' => $link->og_title ?? 'Redirecting…',
            'ogTitle' => $link->og_title,
            'ogDescription' => $link->og_description,
            'ogUrl' => $link->short_url,
            'ogImage' => $link->og_image,
        ]);
    }

    /**
     * Story 1.7: Detect social media crawlers by user-agent.
     */
    private function isSocialCrawler(string $ua): bool
    {
        $crawlers = [
            'facebookexternalhit',
            'twitterbot',
            'linkedinbot',
            'whatsapp',
            'telegrambot',
            'slackbot',
            'discordbot',
            'googlebot',
            'bingbot',
        ];

        $ua = strtolower($ua);

        foreach ($crawlers as $bot) {
            if (str_contains($ua, $bot)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Story 1.7: Build a minimal HTML page with OG/Schema meta tags for social crawlers.
     * Kept as inline HTML — crawlers don't render CSS/JS, just need meta tags.
     */
    private function buildOgPage(Link $link, string $destinationUrl): string
    {
        $shortUrl = htmlspecialchars($link->short_url, ENT_QUOTES, 'UTF-8');
        $destination = htmlspecialchars($destinationUrl, ENT_QUOTES, 'UTF-8');
        $ogTitle = htmlspecialchars($link->og_title ?? 'Short Link', ENT_QUOTES, 'UTF-8');
        $ogDesc = htmlspecialchars($link->og_description ?? "Visit {$destinationUrl}", ENT_QUOTES, 'UTF-8');
        $ogImage = htmlspecialchars($link->og_image ?? '', ENT_QUOTES, 'UTF-8');
        $appName = htmlspecialchars(config('app.name', 'ShortLink'), ENT_QUOTES, 'UTF-8');
        $ogImageTag = $ogImage ? "<meta property=\"og:image\" content=\"{$ogImage}\">" : '';
        $twImageTag = $ogImage ? "<meta property=\"twitter:image\" content=\"{$ogImage}\">" : '';

        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
            <meta charset="UTF-8">
            <title>{$ogTitle}</title>
            <meta http-equiv="refresh" content="0;url={$destination}">
            <meta property="og:title" content="{$ogTitle}">
            <meta property="og:description" content="{$ogDesc}">
            <meta property="og:url" content="{$shortUrl}">
            <meta property="og:type" content="website">
            {$ogImageTag}
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{$ogTitle}">
            <meta name="twitter:description" content="{$ogDesc}">
            {$twImageTag}
            <script type="application/ld+json">
            {"@context":"https://schema.org","@type":"WebPage","name":"{$ogTitle}","url":"{$shortUrl}","description":"{$ogDesc}","publisher":{"@type":"Organization","name":"{$appName}"}}
            </script>
            </head>
            <body>Redirecting…</body>
            </html>
            HTML;
    }

    /**
     * Get click analytics data from request.
     */
    private function getClickData(Request $request): array
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $referrer = $request->headers->get('referer');
        $referrerDomain = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;

        return [
            'ip_hash' => hash('sha256', $request->ip()),
            'country_code' => $this->getCountryCode($request->ip()),
            'device_type' => $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop'),
            'browser' => $agent->browser(),
            'os' => $agent->platform(),
            'referrer' => $referrer,
            'referrer_domain' => $referrerDomain,
        ];
    }

    /**
     * Get country code from IP (using cache).
     */
    private function getCountryCode(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        return Cache::remember("geo:{$ip}", now()->addHours(24), function () use ($ip) {
            // TODO: Integrate with geolocation service (MaxMind, IP-API, etc.)
            return null;
        });
    }

    /**
     * Story 5.4: Get appropriate ad for link based on override settings.
     */
    private function getAdForLink(Link $link, Request $request): ?Ad
    {
        if ($link->ad_override === 'disable') {
            return null;
        }

        if ($link->ad_override === 'force' && $link->ad_id) {
            return Ad::active()->find($link->ad_id);
        }

        $countryCode = $this->getCountryCode($request->ip());

        return Ad::active()
            ->redirect()  // Only get ads for redirect placement
            ->when($countryCode, fn($q) => $q->forCountry($countryCode))
            ->inRandomOrder()
            ->first();
    }

    /**
     * Check if user has already seen an ad for this link in current session.
     */
    private function hasSeenAd(Request $request, int $linkId): bool
    {
        $seen = session('seen_ads', []);
        return in_array($linkId, $seen);
    }

    /**
     * Mark ad as seen for this link in current session.
     */
    private function markAdSeen(Request $request, int $linkId): void
    {
        $seen = session('seen_ads', []);
        $seen[] = $linkId;
        session(['seen_ads' => array_unique($seen)]);
    }

    /**
     * Get ads for specific placement with country targeting.
     */
    private function getAdsForPlacement(string $placement, Request $request): ?Ad
    {
        $countryCode = $this->getCountryCode($request->ip());

        return Ad::active()
            ->forPlacement($placement)
            ->when($countryCode, fn($q) => $q->forCountry($countryCode))
            ->inRandomOrder()
            ->first();
    }
}
