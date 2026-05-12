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
            ['key' => 'meta_description', 'value' => 'Shorten URLs with analytics-grade insights and beautiful experiences.', 'group' => 'branding'],
            ['key' => 'meta_keywords', 'value' => 'url shortener, analytics, link management, qr codes', 'group' => 'branding'],
            ['key' => 'og_image', 'value' => '/storage/settings/og-default.png', 'group' => 'branding'],
            ['key' => 'schema_json', 'value' => '{"@context":"https://schema.org","@type":"WebApplication","name":"ShortLink Pro","applicationCategory":"UtilitiesApplication"}', 'group' => 'branding'],
            // Per-page SEO defaults
            ['key' => 'seo_home_title', 'value' => 'ShortLink Pro — Precision URL Shortening', 'group' => 'seo'],
            ['key' => 'seo_home_description', 'value' => 'Transform clunky links into brand statements with analytics, QR, and enterprise security.', 'group' => 'seo'],
            ['key' => 'seo_privacy_title', 'value' => 'Privacy Policy — ShortLink Pro', 'group' => 'seo'],
            ['key' => 'seo_privacy_description', 'value' => 'Understand how ShortLink Pro collects, stores, and protects your data.', 'group' => 'seo'],
            ['key' => 'seo_terms_title', 'value' => 'Terms of Service — ShortLink Pro', 'group' => 'seo'],
            ['key' => 'seo_terms_description', 'value' => 'Review the service terms governing ShortLink Pro accounts and APIs.', 'group' => 'seo'],
            ['key' => 'seo_cookies_title', 'value' => 'Cookie Policy — ShortLink Pro', 'group' => 'seo'],
            ['key' => 'seo_cookies_description', 'value' => 'Learn about the cookies ShortLink Pro uses for analytics and personalization.', 'group' => 'seo'],
            ['key' => 'seo_gdpr_title', 'value' => 'GDPR Compliance — ShortLink Pro', 'group' => 'seo'],
            ['key' => 'seo_gdpr_description', 'value' => 'Transparency on data processing, consent, and your rights under GDPR.', 'group' => 'seo'],
            ['key' => 'seo_help_title', 'value' => 'Help Center — ShortLink Pro', 'group' => 'seo'],
            ['key' => 'seo_help_description', 'value' => 'Guides, tutorials, and troubleshooting for mastering ShortLink Pro.', 'group' => 'seo'],
            ['key' => 'seo_affiliate_title', 'value' => 'Affiliate Program — ShortLink Pro', 'group' => 'seo'],
            ['key' => 'seo_affiliate_description', 'value' => 'Earn recurring revenue by sharing ShortLink Pro with your audience.', 'group' => 'seo'],
            ['key' => 'seo_contact_title', 'value' => 'Contact Us — ShortLink Pro', 'group' => 'seo'],
            ['key' => 'seo_contact_description', 'value' => 'Reach the ShortLink Pro team for support, partnerships, or press.', 'group' => 'seo'],
            ['key' => 'seo_api_docs_title', 'value' => 'API Documentation — ShortLink Pro', 'group' => 'seo'],
            ['key' => 'seo_api_docs_description', 'value' => 'Integrate ShortLink Pro into your stack with our REST & webhook APIs.', 'group' => 'seo'],
            // Features
            ['key' => 'features_affiliate', 'value' => 'true', 'group' => 'features'],
            ['key' => 'features_ads', 'value' => 'true', 'group' => 'features'],
            ['key' => 'features_gdpr', 'value' => 'false', 'group' => 'features'],
            ['key' => 'auto_suspend_threshold', 'value' => '3', 'group' => 'features'],
            ['key' => 'affiliate_min_payout', 'value' => '50', 'group' => 'features'],
            ['key' => 'affiliate_payout_methods', 'value' => 'PayPal,Bank Transfer,Crypto', 'group' => 'features'],
            // Cache
            ['key' => 'cache_ttl_redirect', 'value' => '7', 'group' => 'cache'],
            ['key' => 'cache_ttl_analytics', 'value' => '3600', 'group' => 'cache'],
            // Security
            ['key' => 'captcha_enabled', 'value' => 'false', 'group' => 'security'],
            ['key' => 'captcha_register', 'value' => 'false', 'group' => 'security'],
            ['key' => 'captcha_login', 'value' => 'false', 'group' => 'security'],
            ['key' => 'captcha_forgot_password', 'value' => 'false', 'group' => 'security'],
            ['key' => 'captcha_redirect', 'value' => 'false', 'group' => 'security'],
            ['key' => 'captcha_provider', 'value' => 'recaptcha', 'group' => 'security'],
            ['key' => 'captcha_site_key', 'value' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI', 'group' => 'security'],
            ['key' => 'captcha_secret_key', 'value' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI', 'group' => 'security'],
            ['key' => 'turnstile_site_key', 'value' => '', 'group' => 'security'],
            ['key' => 'turnstile_secret_key', 'value' => '', 'group' => 'security'],
            ['key' => 'safe_browsing_enabled', 'value' => 'false', 'group' => 'security'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'security'],
            ['key' => 'maintenance_message', 'value' => 'We are performing scheduled maintenance. Please check back soon.', 'group' => 'security'],
            // Referral System
            ['key' => 'referral_commission_rate', 'value' => '1.5', 'group' => 'referral'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
