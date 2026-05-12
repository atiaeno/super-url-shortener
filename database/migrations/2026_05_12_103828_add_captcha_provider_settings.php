<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add CAPTCHA provider setting
        \App\Models\Setting::create([
            'key' => 'captcha_provider',
            'value' => 'recaptcha',  // Default to recaptcha for backward compatibility
            'type' => 'string',
            'description' => 'CAPTCHA provider: recaptcha, turnstile, or disabled'
        ]);

        // Add Cloudflare Turnstile settings
        \App\Models\Setting::create([
            'key' => 'turnstile_site_key',
            'value' => '',
            'type' => 'string',
            'description' => 'Cloudflare Turnstile site key'
        ]);

        \App\Models\Setting::create([
            'key' => 'turnstile_secret_key',
            'value' => '',
            'type' => 'string',
            'description' => 'Cloudflare Turnstile secret key'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove CAPTCHA provider settings
        \App\Models\Setting::where('key', 'captcha_provider')->delete();
        \App\Models\Setting::where('key', 'turnstile_site_key')->delete();
        \App\Models\Setting::where('key', 'turnstile_secret_key')->delete();
    }
};
