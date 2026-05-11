<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders\Production;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Essential branding settings
            ['key' => 'app_name', 'value' => config('app.name', 'URL Shortener'), 'group' => 'branding'],
            ['key' => 'app_tagline', 'value' => 'Shorten URLs with analytics', 'group' => 'branding'],
            ['key' => 'footer_text', 'value' => '© ' . date('Y') . ' ' . config('app.name', 'URL Shortener') . '. All rights reserved.', 'group' => 'branding'],
            ['key' => 'meta_description', 'value' => 'Professional URL shortener with analytics and tracking.', 'group' => 'branding'],
            ['key' => 'meta_keywords', 'value' => 'url shortener, link shortener, analytics, qr code', 'group' => 'branding'],
            
            // SEO and robots
            ['key' => 'sitemap_enabled', 'value' => 'true', 'group' => 'branding'],
            ['key' => 'robots_txt', 'value' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /dashboard/\nDisallow: /api/\nSitemap: /sitemap.xml", 'group' => 'branding'],
            
            // Essential features
            ['key' => 'features_affiliate', 'value' => 'false', 'group' => 'features'],
            ['key' => 'features_ads', 'value' => 'false', 'group' => 'features'],
            ['key' => 'features_gdpr', 'value' => 'true', 'group' => 'features'],
            ['key' => 'auto_suspend_threshold', 'value' => '5', 'group' => 'features'],
            
            // Cache settings (optimized for production)
            ['key' => 'cache_ttl_redirect', 'value' => '86400', 'group' => 'cache'],
            ['key' => 'cache_ttl_analytics', 'value' => '3600', 'group' => 'cache'],
            
            // Security settings (disabled by default for production)
            ['key' => 'captcha_enabled', 'value' => 'false', 'group' => 'security'],
            ['key' => 'redirect_captcha', 'value' => 'false', 'group' => 'security'],
            ['key' => 'safe_browsing_enabled', 'value' => 'false', 'group' => 'security'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'security'],
            ['key' => 'maintenance_message', 'value' => 'System maintenance in progress. Please check back soon.', 'group' => 'security'],
            
            // Rate limiting (production defaults)
            ['key' => 'api_rate_limit_per_hour', 'value' => '1000', 'group' => 'api'],
            ['key' => 'api_token_rate_limit_per_hour', 'value' => '100', 'group' => 'api'],
            
            // Redirect settings
            ['key' => 'redirect_countdown', 'value' => '3', 'group' => 'redirect'],
            ['key' => 'redirect_mode', 'value' => 'auto', 'group' => 'redirect'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('✅ Production settings seeded successfully');
        $this->command->info('   - Rate limits: API 1000/hour, Tokens 100/hour');
        $this->command->info('   - Security: Captcha disabled, maintenance mode off');
        $this->command->info('   - Features: Affiliate/ads disabled, GDPR enabled');
    }
}
