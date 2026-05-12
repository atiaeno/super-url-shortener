<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Jobs\LogClickJob;
use App\Models\Ad;
use App\Models\AliasDomain;
use App\Models\Link;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class RedirectController extends Controller
{
    /**
     * Handle short URL redirect.
     */
    public function __invoke(string $shortCode, Request $request)
    {
        // Get current domain
        $currentDomain = $request->getHost();

        // Try to find domain in our alias domains
        $domain = AliasDomain::where('domain', $currentDomain)->active()->first();

        // Look up the link with domain support
        $domainId = $domain?->id;
        $query = Link::where(function ($q) use ($shortCode) {
            $q
                ->where('short_code', $shortCode)
                ->orWhere('custom_alias', $shortCode);
        });

        // If domain exists in our system, filter by domain
        if ($domain) {
            $query->where('domain_id', $domainId);
        } else {
            // If domain not found, look for links without domain_id (backward compatibility)
            $query->whereNull('domain_id');
        }

        $link = $query->first();

        // Get promotions for all states
        $promotions = $this->getPromotions();

        // Not found at all
        if (!$link) {
            return response()->view('redirect', array_merge([
                'state' => 'not-found',
                'shortCode' => $shortCode,
                'link' => null,
            ], $promotions), 404);
        }

        // Expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            return response()->view('redirect', array_merge([
                'state' => 'expired',
                'expiresAt' => $link->expires_at,
                'link' => $link,
            ], $promotions), 410);
        }

        // Deactivated
        if (!$link->is_active) {
            return response()->view('redirect', array_merge([
                'state' => 'not-found',
                'shortCode' => $shortCode,
                'link' => $link,
            ], $promotions), 404);
        }

        // Password protected — show password gate
        if ($link->visibility === 'private' && $link->password) {
            if ($request->isMethod('post')) {
                if (password_verify($request->input('password', ''), $link->password)) {
                    session(["unlocked:{$link->id}" => true]);
                } else {
                    return response()->view('redirect', array_merge([
                        'state' => 'password',
                        'shortCode' => $shortCode,
                        'error' => true,
                        'link' => $link,
                    ], $promotions), 403);
                }
            } else {
                if (!session("unlocked:{$link->id}")) {
                    return response()->view('redirect', array_merge([
                        'state' => 'password',
                        'shortCode' => $shortCode,
                        'error' => false,
                        'link' => $link,
                    ], $promotions), 403);
                }
            }
        }

        $destinationUrl = $link->destination_url;

        // Cache destination for next time (24h TTL) — domain-scoped to prevent cross-domain collisions
        Cache::put("redirect:{$domainId}:{$shortCode}", $destinationUrl, now()->addHours(24));

        // Serve OG meta page for social crawlers (instant redirect via meta refresh)
        if ($this->isSocialCrawler($request->userAgent() ?? '')) {
            return response($this->buildOgPage($link, $destinationUrl), 200)
                ->header('Content-Type', 'text/html; charset=utf-8');
        }

        // Check if page is cached — key includes domain to prevent cross-domain collisions
        $cacheKey = "redirect:page:{$domainId}:{$shortCode}";
        $cacheDays = (int) Setting::get('redirect_cache_days', 7);
        $cachedHtml = Cache::get($cacheKey);

        if ($cachedHtml) {
            // Serve cached page - inject shortCode for tracking (JSON-encoded to prevent XSS)
            $safeShortCode = json_encode($shortCode);
            $cachedHtml = str_replace(
                "var shortCode = '';",
                "var shortCode = {$safeShortCode};",
                $cachedHtml
            );
            // Still dispatch click tracking even for cached pages
            LogClickJob::dispatch($link->id, $this->getClickData($request))->onQueue('default');
            return response($cachedHtml, 200)->header('Content-Type', 'text/html; charset=utf-8');
        }

        // Read redirect page settings
        $redirectCountdown = (int) Setting::get('redirect_countdown', 5);
        $redirectMode = Setting::get('redirect_mode', 'auto');
        $redirectCaptcha = Setting::get('redirect_captcha', false);

        // Get CAPTCHA provider and site key
        $captcha = app(\App\Services\CaptchaService::class);
        $captchaProvider = $captcha->getProviderType();
        $captchaSiteKey = $captcha->siteKey();

        // Check for ad to display on the redirect page
        $ad = $this->getAdForLink($link, $request);
        $adContent = '';
        $countdown = $redirectCountdown;

        if ($ad && $ad->format === 'interstitial' && !$this->hasSeenAd($request, $link->id)) {
            $adContent = $ad->content ?? '';
            // Use ad's countdown if set, otherwise use global setting
            $countdown = (int) $ad->countdown_seconds ?: $redirectCountdown;
            $this->markAdSeen($request, $link->id);
        }

        // Build view data once — used for both caching and the response
        $viewData = array_merge([
            'state' => 'redirect',
            'shortCode' => $shortCode,
            'destination' => $destinationUrl,
            'countdown' => $countdown,
            'redirectMode' => $redirectMode,
            'redirectCaptcha' => filter_var($redirectCaptcha, FILTER_VALIDATE_BOOLEAN),
            'captchaSiteKey' => $captchaSiteKey,
            'captchaProvider' => $captchaProvider,
            'adContent' => $adContent,
            'adPlacement' => $ad ? $ad->placement : null,
            'adFormat' => $ad ? $ad->format : null,
            'title' => $link->og_title ?? 'Redirecting…',
            'ogTitle' => $link->og_title,
            'ogDescription' => $link->og_description,
            'ogUrl' => $link->short_url,
            'link' => $link,
            'ogImage' => $link->og_image,
        ], $promotions);

        // Cache rendered HTML for subsequent requests (render once here, serve raw on cache hits)
        $html = view('redirect', $viewData)->render();
        Cache::put($cacheKey, $html, now()->addDays($cacheDays));

        // Return a view response so tests can use assertViewHas()
        return response()->view('redirect', $viewData);
    }

    /**
     * Detect social media crawlers by user-agent.
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
     * Build a minimal HTML page with OG/Schema meta tags for social crawlers.
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

        // JSON-LD values must be JSON-escaped (not just HTML-escaped) to prevent broken JSON
        $jsonTitle = json_encode($link->og_title ?? 'Short Link', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $jsonDesc = json_encode($link->og_description ?? "Visit {$destinationUrl}", JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $jsonUrl = json_encode($link->short_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $jsonAppName = json_encode(config('app.name', 'ShortLink'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

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
            {"@context":"https://schema.org","@type":"WebPage","name":{$jsonTitle},"url":{$jsonUrl},"description":{$jsonDesc},"publisher":{"@type":"Organization","name":{$jsonAppName}}}
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
     * Priority: CloudFlare header -> Free API fallback
     */
    private function getCountryCode(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        return Cache::remember("geo:{$ip}", now()->addHours(24), function () use ($ip) {
            // 1. Try CloudFlare header first
            $cfCountry = request()->header('CF-IPCountry');
            if ($cfCountry && $cfCountry !== 'XX' && $cfCountry !== 'T1') {
                return $cfCountry;
            }

            // 2. Fallback to free ip-api.com (45 requests/min)
            try {
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=countryCode");
                if ($response->successful()) {
                    return $response->json('countryCode');
                }
            } catch (\Throwable) {
                // Fail silently - return null
            }

            return null;
        });
    }

    /**
     * Get appropriate ad for link based on override settings.
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

    /**
     * Get all promotions for all placements.
     */
    private function getPromotions(): array
    {
        return [
            'headerPromotion' => Ad::getCachedForPlacement('header'),
            'footerPromotion' => Ad::getCachedForPlacement('footer'),
            'leftSidePromotion' => Ad::getCachedForPlacement('left_side'),
            'rightSidePromotion' => Ad::getCachedForPlacement('right_side'),
            'beforeCounterPromotion' => Ad::getCachedForPlacement('before_counter'),
            'underCounterPromotion' => Ad::getCachedForPlacement('under_counter'),
            'aboveButtonPromotion' => Ad::getCachedForPlacement('above_button'),
            'underButtonPromotion' => Ad::getCachedForPlacement('under_button'),
            'popupPromotion' => Ad::getCachedForPlacement('popup'),
        ];
    }
}
