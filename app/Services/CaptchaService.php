<?php
// © Atia Hegazy — atiaeno.com

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class CaptchaService
{
    const PROVIDER_RECAPTCHA = 'recaptcha';
    const PROVIDER_TURNSTILE = 'turnstile';
    const PROVIDER_DISABLED = 'disabled';

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

        $globalEnabled = filter_var(Setting::get('captcha_enabled', 'false'), FILTER_VALIDATE_BOOLEAN);
        return $globalEnabled && $this->getProvider() !== self::PROVIDER_DISABLED;
    }

    /**
     * Returns the current CAPTCHA provider.
     */
    public function getProvider(): string
    {
        if (!$this->settingsTableExists()) {
            return self::PROVIDER_RECAPTCHA;
        }
        return Setting::get('captcha_provider', self::PROVIDER_RECAPTCHA);
    }

    /**
     * Returns the configured site key for the current provider.
     */
    public function siteKey(): string
    {
        $provider = $this->getProvider();

        if (!$this->settingsTableExists()) {
            return config("services.{$provider}.site_key", '');
        }

        switch ($provider) {
            case self::PROVIDER_TURNSTILE:
                return Setting::get('turnstile_site_key', config('services.turnstile.site_key', ''));
            case self::PROVIDER_RECAPTCHA:
            default:
                return Setting::get('captcha_site_key', config('services.recaptcha.site_key', ''));
        }
    }

    /**
     * Returns the provider type for frontend use.
     */
    public function getProviderType(): string
    {
        return $this->getProvider();
    }

    /**
     * Verifies a CAPTCHA token with the appropriate provider's API.
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

        $provider = $this->getProvider();

        switch ($provider) {
            case self::PROVIDER_TURNSTILE:
                return $this->verifyTurnstile($token, $ip);
            case self::PROVIDER_RECAPTCHA:
            default:
                return $this->verifyRecaptcha($token, $ip);
        }
    }

    /**
     * Verifies a Google reCAPTCHA v2 token.
     */
    private function verifyRecaptcha(string $token, string $ip): bool
    {
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
                        'remoteip' => $ip,
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

    /**
     * Verifies a Cloudflare Turnstile token.
     */
    private function verifyTurnstile(string $token, string $ip): bool
    {
        $secret = Setting::get('turnstile_secret_key');

        if (empty($secret)) {
            return true;
        }

        try {
            $response = Http::timeout(10)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->asForm()
                ->post(
                    'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                    [
                        'secret' => $secret,
                        'response' => $token,
                        'remoteip' => $ip,
                    ]
                );

            $result = $response->json();

            return (bool) ($result['success'] ?? false);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Turnstile verification error', [
                'message' => $e->getMessage()
            ]);
            return true;
        }
    }
}
