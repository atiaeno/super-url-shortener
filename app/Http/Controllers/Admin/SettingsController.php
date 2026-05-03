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

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_tagline' => 'nullable|string|max:255',
            'logo_url' => 'nullable|url',
            'favicon_url' => 'nullable|url',
            'footer_text' => 'nullable|string',
            'donation_enabled' => 'boolean',
            'donation_button_id' => 'nullable|string',
            'features_affiliate' => 'boolean',
            'features_ads' => 'boolean',
            'features_gdpr' => 'boolean',
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
        ]);

        foreach ($validated as $key => $value) {
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
