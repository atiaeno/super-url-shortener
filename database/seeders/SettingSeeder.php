<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Branding
            ['key' => 'app_name', 'value' => 'ShortLink Pro', 'group' => 'branding'],
            ['key' => 'app_tagline', 'value' => 'Shorten URLs with style and analytics', 'group' => 'branding'],
            ['key' => 'footer_text', 'value' => '© 2026 ShortLink Pro. All rights reserved.', 'group' => 'branding'],
            ['key' => 'donation_enabled', 'value' => 'true', 'group' => 'branding'],
            ['key' => 'donation_button_id', 'value' => 'DEMO_BUTTON_ID', 'group' => 'branding'],
            ['key' => 'sitemap_enabled', 'value' => 'true', 'group' => 'branding'],
            ['key' => 'robots_txt', 'value' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /dashboard/\nSitemap: /sitemap.xml", 'group' => 'branding'],
            // Features
            ['key' => 'features_affiliate', 'value' => 'true', 'group' => 'features'],
            ['key' => 'features_ads', 'value' => 'true', 'group' => 'features'],
            ['key' => 'features_gdpr', 'value' => 'false', 'group' => 'features'],
            ['key' => 'auto_suspend_threshold', 'value' => '3', 'group' => 'features'],
            ['key' => 'affiliate_min_payout', 'value' => '50', 'group' => 'features'],
            ['key' => 'affiliate_payout_methods', 'value' => 'PayPal,Bank Transfer,Crypto', 'group' => 'features'],
            // Cache
            ['key' => 'cache_ttl_redirect', 'value' => '86400', 'group' => 'cache'],
            ['key' => 'cache_ttl_analytics', 'value' => '3600', 'group' => 'cache'],
            // Security
            ['key' => 'captcha_enabled', 'value' => 'false', 'group' => 'security'],
            ['key' => 'safe_browsing_enabled', 'value' => 'false', 'group' => 'security'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'security'],
            ['key' => 'maintenance_message', 'value' => 'We are performing scheduled maintenance. Please check back soon.', 'group' => 'security'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
