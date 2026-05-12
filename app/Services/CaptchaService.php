<?php
// © Atia Hegazy — atiaeno.com

namespace App\Services;

 
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class CaptchaService
{
    /**
     * Check if settings table exists
     */
    private function settingsTableExists(): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        try {
            DB::select('SELECT 1 FROM settings LIMIT 1');
            $exists = true;
        } catch (\Exception $e) {
            $exists = false;
        }
        return $exists;
    }

    /**
     * Returns true if CAPTCHA is enabled in admin settings.
     */
    public function isEnabled(): bool
    {
        if (!$this->settingsTableExists()) {
            return false;
        }
        return filter_var(Setting::get('captcha_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns the configured site key (for frontend rendering).
     */
    public function siteKey(): string
    {
        if (!$this->settingsTableExists()) {
            return config('services.recaptcha.site_key', '');
        }
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

        if (!$this->settingsTableExists()) {
            return true;
        }

        $secret = Setting::get('captcha_secret_key');

        if (empty($secret)) {
            return true;
        }
        
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
