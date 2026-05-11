<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $captcha = app(CaptchaService::class);

        $toasts = [];
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            $message = $request->session()->get($type);
            if ($message) {
                $toasts[] = ['id' => uniqid(), 'type' => $type, 'message' => $message];
            }
        }

        // Add validation errors as error toasts
        $errors = $request->session()->get('errors');
        if ($errors && $errors->any()) {
            foreach ($errors->all() as $message) {
                $toasts[] = ['id' => uniqid(), 'type' => 'error', 'message' => $message];
            }
        }

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
            'features_affiliate',
            'features_ads',
            'features_gdpr',
            // Per-page SEO
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
            'api_rate_limit_per_hour',
            'api_token_rate_limit_per_hour',
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
            'features_affiliate' => 'true',
            'features_ads' => 'false',
            'features_gdpr' => 'false',
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
            'api_rate_limit_per_hour' => '100',
            'api_token_rate_limit_per_hour' => '10',
        ];

        $settings = array_merge($defaults, $settings);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'recaptchaEnabled' => $captcha->isEnabled(),
            'recaptchaSiteKey' => $captcha->isEnabled() ? $captcha->siteKey() : '',
            'flash' => [
                'toast' => $toasts,
                'new_token' => $request->session()->get('new_token'),
                'new_token_name' => $request->session()->get('new_token_name'),
            ],
            'settings' => $settings,
            'appName' => $settings['app_name'] ?? 'Super Url Shortener',
        ];
    }
}
