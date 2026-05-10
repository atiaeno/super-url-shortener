<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    private const SETTING_KEYS = [
        'app_name',
        'app_tagline',
        'logo_url',
        'favicon_url',
        'footer_text',
        'meta_description',
        'meta_keywords',
        'og_image',
        'schema_json',
        // Per-page SEO (public pages)
        'seo_home_title',
        'seo_home_description',
        'seo_privacy_title',
        'seo_privacy_description',
        'seo_terms_title',
        'seo_terms_description',
        'seo_cookies_title',
        'seo_cookies_description',
        'seo_gdpr_title',
        'seo_gdpr_description',
        'seo_help_title',
        'seo_help_description',
        'seo_affiliate_title',
        'seo_affiliate_description',
        'seo_contact_title',
        'seo_contact_description',
        'seo_api_docs_title',
        'seo_api_docs_description',
        'donation_enabled',
        'donation_button_id',
        'features_affiliate',
        'features_ads',
        'features_gdpr',
        'cache_ttl_redirect',
        'cache_ttl_analytics',
        'maintenance_mode',
        'maintenance_message',
        'captcha_enabled',
        'captcha_site_key',
        'captcha_secret_key',
        'safe_browsing_enabled',
        'safe_browsing_api_key',
        'auto_suspend_threshold',
        'robots_txt',
        'sitemap_enabled',
        'redirect_countdown',
        'redirect_mode',
        'redirect_captcha',
        'affiliate_min_payout',
        'affiliate_payout_methods',
    ];

    public function index(): Response
    {
        $settings = Setting::whereIn('key', self::SETTING_KEYS)
            ->pluck('value', 'key')
            ->toArray();

        // Apply defaults for missing settings
        $defaults = [
            'app_name' => config('app.name'),
            'app_tagline' => 'Shorten URLs with style',
            'meta_description' => 'Shorten URLs with style and analytics. Track clicks, geographic data, and more.',
            'meta_keywords' => 'url shortener, link shortener, url tracker, analytics, qr code',
            'og_image' => '',
            'schema_json' => '{"@context":"https://schema.org","@type":"WebApplication","name":"' . config('app.name') . '","applicationCategory":"UtilitiesApplication"}',
            // Per-page SEO defaults
            'seo_home_title' => '',
            'seo_home_description' => '',
            'seo_privacy_title' => '',
            'seo_privacy_description' => '',
            'seo_terms_title' => '',
            'seo_terms_description' => '',
            'seo_cookies_title' => '',
            'seo_cookies_description' => '',
            'seo_gdpr_title' => '',
            'seo_gdpr_description' => '',
            'seo_help_title' => '',
            'seo_help_description' => '',
            'seo_affiliate_title' => '',
            'seo_affiliate_description' => '',
            'seo_contact_title' => '',
            'seo_contact_description' => '',
            'seo_api_docs_title' => '',
            'seo_api_docs_description' => '',
            'features_affiliate' => 'true',
            'features_ads' => 'false',
            'features_gdpr' => 'false',
            'cache_ttl_redirect' => '86400',
            'cache_ttl_analytics' => '3600',
            'maintenance_mode' => 'false',
            'maintenance_message' => 'We are performing scheduled maintenance. Please check back soon.',
            'captcha_enabled' => 'false',
            'safe_browsing_enabled' => 'false',
            'auto_suspend_threshold' => '3',
            'sitemap_enabled' => 'true',
            'robots_txt' => "User-agent: *\nAllow: /\nDisallow: /admin/\nSitemap: /sitemap.xml",
            'redirect_countdown' => '5',
            'redirect_mode' => 'auto',
            'redirect_captcha' => 'false',
            'affiliate_min_payout' => '50',
            'affiliate_payout_methods' => 'PayPal,Bank Transfer,Crypto',
        ];

        $settings = array_merge($defaults, $settings);

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'cacheStats' => [
                'redirect' => Cache::get('stats:cache:redirect', 0),
                'analytics' => Cache::get('stats:cache:analytics', 0),
            ],
        ]);
    }

    public function publicSettings()
    {
        $brandingKeys = [
            'app_name',
            'app_tagline',
            'logo_url',
            'favicon_url',
            'footer_text',
            'meta_description',
            'meta_keywords',
            'og_image',
            'schema_json',
            'donation_enabled',
            'donation_button_id',
            'robots_txt',
            'sitemap_enabled',
        ];

        $settings = Setting::whereIn('key', $brandingKeys)
            ->pluck('value', 'key')
            ->toArray();

        $defaults = [
            'app_name' => 'Super Url Shortener',
            'app_tagline' => 'Shorten URLs with style and analytics',
            'logo_url' => '',
            'favicon_url' => '',
            'footer_text' => '© ' . date('Y') . ' Super Url Shortener. All rights reserved.',
            'meta_description' => 'Shorten URLs with style and analytics. Track clicks, geographic data, and more.',
            'meta_keywords' => 'url shortener, link shortener, url tracker, analytics, qr code',
            'og_image' => '',
            'schema_json' => '{"@context":"https://schema.org","@type":"WebApplication","name":"Super Url Shortener","applicationCategory":"UtilitiesApplication"}',
            'donation_enabled' => 'false',
            'donation_button_id' => '',
            'robots_txt' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /dashboard/\nSitemap: /sitemap.xml",
            'sitemap_enabled' => 'true',
        ];

        return array_merge($defaults, $settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_tagline' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico,jpg,jpeg,svg|max:512',
            'logo_url' => 'nullable|string',
            'favicon_url' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'og_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'schema_json' => 'nullable|string',
            // Per-page SEO
            'seo_home_title' => 'nullable|string|max:255',
            'seo_home_description' => 'nullable|string|max:500',
            'seo_privacy_title' => 'nullable|string|max:255',
            'seo_privacy_description' => 'nullable|string|max:500',
            'seo_terms_title' => 'nullable|string|max:255',
            'seo_terms_description' => 'nullable|string|max:500',
            'seo_cookies_title' => 'nullable|string|max:255',
            'seo_cookies_description' => 'nullable|string|max:500',
            'seo_gdpr_title' => 'nullable|string|max:255',
            'seo_gdpr_description' => 'nullable|string|max:500',
            'seo_help_title' => 'nullable|string|max:255',
            'seo_help_description' => 'nullable|string|max:500',
            'seo_affiliate_title' => 'nullable|string|max:255',
            'seo_affiliate_description' => 'nullable|string|max:500',
            'seo_contact_title' => 'nullable|string|max:255',
            'seo_contact_description' => 'nullable|string|max:500',
            'seo_api_docs_title' => 'nullable|string|max:255',
            'seo_api_docs_description' => 'nullable|string|max:500',
            'donation_enabled' => 'boolean',
            'donation_button_id' => 'nullable|string',
            'features_affiliate' => 'nullable|boolean',
            'features_ads' => 'nullable|boolean',
            'features_gdpr' => 'nullable|boolean',
            'cache_ttl_redirect' => 'integer|min:60',
            'cache_ttl_analytics' => 'integer|min:60',
            'maintenance_mode' => 'boolean',
            'maintenance_message' => 'nullable|string',
            'captcha_enabled' => 'boolean',
            'captcha_site_key' => 'nullable|string',
            'captcha_secret_key' => 'nullable|string',
            'safe_browsing_enabled' => 'boolean',
            'safe_browsing_api_key' => 'nullable|string',
            'auto_suspend_threshold' => 'integer|min:1',
            'robots_txt' => 'nullable|string',
            'sitemap_enabled' => 'boolean',
            'redirect_countdown' => 'integer|min:0|max:60',
            'redirect_mode' => 'in:auto,click',
            'redirect_captcha' => 'boolean',
            'affiliate_min_payout' => 'nullable|numeric|min:1',
            'affiliate_payout_methods' => 'nullable|string|max:255',
        ]);

        // Handle file uploads
        $logoPath = $validated['logo_url'] ?? '';
        $faviconPath = $validated['favicon_url'] ?? '';
        $ogImagePath = '';

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('settings', 'public');
            $logoPath = '/storage/' . $logoPath;
        }
        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('settings', 'public');
            $faviconPath = '/storage/' . $faviconPath;
        }
        if ($request->hasFile('og_image')) {
            $ogImagePath = $request->file('og_image')->store('settings', 'public');
            $ogImagePath = '/storage/' . $ogImagePath;
        }

        // Save all settings
        $settingsToSave = [
            'app_name' => $validated['app_name'],
            'app_tagline' => $validated['app_tagline'],
            'logo_url' => $logoPath ?: ($validated['logo_url'] ?? ''),
            'favicon_url' => $faviconPath ?: ($validated['favicon_url'] ?? ''),
            'footer_text' => $validated['footer_text'],
            'meta_description' => $validated['meta_description'] ?? '',
            'meta_keywords' => $validated['meta_keywords'] ?? '',
            'og_image' => $ogImagePath ?: '',
            'schema_json' => $validated['schema_json'] ?? '',
            // Per-page SEO
            'seo_home_title' => $validated['seo_home_title'] ?? '',
            'seo_home_description' => $validated['seo_home_description'] ?? '',
            'seo_privacy_title' => $validated['seo_privacy_title'] ?? '',
            'seo_privacy_description' => $validated['seo_privacy_description'] ?? '',
            'seo_terms_title' => $validated['seo_terms_title'] ?? '',
            'seo_terms_description' => $validated['seo_terms_description'] ?? '',
            'seo_cookies_title' => $validated['seo_cookies_title'] ?? '',
            'seo_cookies_description' => $validated['seo_cookies_description'] ?? '',
            'seo_gdpr_title' => $validated['seo_gdpr_title'] ?? '',
            'seo_gdpr_description' => $validated['seo_gdpr_description'] ?? '',
            'seo_help_title' => $validated['seo_help_title'] ?? '',
            'seo_help_description' => $validated['seo_help_description'] ?? '',
            'seo_affiliate_title' => $validated['seo_affiliate_title'] ?? '',
            'seo_affiliate_description' => $validated['seo_affiliate_description'] ?? '',
            'seo_contact_title' => $validated['seo_contact_title'] ?? '',
            'seo_contact_description' => $validated['seo_contact_description'] ?? '',
            'seo_api_docs_title' => $validated['seo_api_docs_title'] ?? '',
            'seo_api_docs_description' => $validated['seo_api_docs_description'] ?? '',
            'donation_enabled' => $request->has('donation_enabled') ? 'true' : 'false',
            'donation_button_id' => $validated['donation_button_id'] ?? '',
            'features_affiliate' => $request->has('features_affiliate') ? 'true' : 'false',
            'features_ads' => $request->has('features_ads') ? 'true' : 'false',
            'features_gdpr' => $request->has('features_gdpr') ? 'true' : 'false',
            'cache_ttl_redirect' => $validated['cache_ttl_redirect'] ?? 86400,
            'cache_ttl_analytics' => $validated['cache_ttl_analytics'] ?? 3600,
            'maintenance_mode' => $request->has('maintenance_mode') ? 'true' : 'false',
            'maintenance_message' => $validated['maintenance_message'] ?? '',
            'captcha_enabled' => $request->has('captcha_enabled') ? 'true' : 'false',
            'captcha_site_key' => $validated['captcha_site_key'] ?? '',
            'captcha_secret_key' => $validated['captcha_secret_key'] ?? '',
            'safe_browsing_enabled' => $request->has('safe_browsing_enabled') ? 'true' : 'false',
            'safe_browsing_api_key' => $validated['safe_browsing_api_key'] ?? '',
            'auto_suspend_threshold' => $validated['auto_suspend_threshold'] ?? 3,
            'robots_txt' => $validated['robots_txt'] ?? '',
            'sitemap_enabled' => $request->has('sitemap_enabled') ? 'true' : 'false',
            'redirect_countdown' => $validated['redirect_countdown'] ?? 5,
            'redirect_mode' => $validated['redirect_mode'] ?? 'auto',
            'redirect_captcha' => $request->has('redirect_captcha') ? 'true' : 'false',
            'affiliate_min_payout' => $validated['affiliate_min_payout'] ?? 50,
            'affiliate_payout_methods' => $validated['affiliate_payout_methods'] ?? 'PayPal,Bank Transfer,Crypto',
        ];

        foreach ($settingsToSave as $key => $value) {
            Setting::set($key, $value);
        }

        // Update maintenance mode
        if ($validated['maintenance_mode'] ?? false) {
            Artisan::call('down', [
                '--message' => $validated['maintenance_message'] ?? 'Maintenance mode',
            ]);
        } else {
            Artisan::call('up');
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function purgeCache(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:redirect,analytics,all',
        ]);

        switch ($validated['type']) {
            case 'redirect':
                // Clear all redirect cache keys
                $this->clearCachePattern('redirect:*');
                break;
            case 'analytics':
                $this->clearCachePattern('analytics:*');
                break;
            case 'all':
                Cache::flush();
                break;
        }

        return redirect()->back()->with('success', 'Cache purged successfully.');
    }

    private function clearCachePattern(string $pattern): void
    {
        // Redis-specific pattern delete
        $redis = Cache::getStore();
        if (method_exists($redis, 'getRedis')) {
            $redisConnection = $redis->getRedis();
            foreach ($redisConnection->keys($pattern) as $key) {
                Cache::forget(str_replace(config('cache.prefix'), '', $key));
            }
        }
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $settings = Setting::all()->map(fn($s) => [
            'key' => $s->key,
            'value' => $s->value,
            'group' => $s->group,
        ])->toArray();

        $export = [
            'exported_at' => now()->toIso8601String(),
            'version' => '1.0',
            'settings' => $settings,
        ];

        $filename = 'settings-' . now()->format('Y-m-d-His') . '.json';
        $path = storage_path('app/' . $filename);
        file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT));

        return response()->download($path)->deleteFileAfterSend();
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $content = file_get_contents($validated['file']->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !isset($data['settings']) || !is_array($data['settings'])) {
            return redirect()->back()->withErrors(['file' => 'Invalid settings file format.']);
        }

        foreach ($data['settings'] as $setting) {
            if (isset($setting['key']) && isset($setting['value'])) {
                Setting::set($setting['key'], $setting['value'], $setting['group'] ?? 'general');
            }
        }

        return redirect()->back()->with('success', 'Settings imported successfully.');
    }

    public function backup()
    {
        $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql';
        $path = storage_path('app/' . $filename);

        // Get database credentials
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = sprintf(
            'mysqldump -h %s -u %s %s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            $pass ? '-p' . escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg($path)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($path)) {
            return redirect()->back()->withErrors(['backup' => 'Database backup failed.']);
        }

        return response()->download($path)->deleteFileAfterSend();
    }
}
