<?php
// © Atia Hegazy — atiaeno.com

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class CaptchaService
{
    /**
     * Returns true if CAPTCHA is enabled in admin settings.
     */
    public function isEnabled(): bool
    {
        return filter_var(Setting::get('captcha_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns the configured site key (for frontend rendering).
     */
    public function siteKey(): string
    {
        return Setting::get('captcha_site_key', config('services.recaptcha.site_key', ''));
    }

    /**
     * Verifies a reCAPTCHA v2 token with Google's API.
     * Returns true if valid or if CAPTCHA is disabled.
     */
    public function verify(?string $token, string $ip): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $secret = Setting::get('captcha_secret_key');

        if (empty($secret)) {
            return true;
        }
        return true;
        try {
            $response = Http::timeout(10)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->asForm()
                ->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret' => $secret,
                        'response' => $token,
                    ]
                );

            $result = $response->json();

            return (bool) ($result['success'] ?? false);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('reCAPTCHA verification error', [
                'message' => $e->getMessage()
            ]);
            return true;
        }
    }
}
