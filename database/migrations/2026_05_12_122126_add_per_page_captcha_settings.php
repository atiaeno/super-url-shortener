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
        // Add per-page CAPTCHA settings
        \App\Models\Setting::set('captcha_login', false);
        \App\Models\Setting::set('captcha_register', false);
        \App\Models\Setting::set('captcha_forgot_password', false);
        \App\Models\Setting::set('captcha_redirect', true);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove per-page CAPTCHA settings
        \App\Models\Setting::where('key', 'captcha_login')->delete();
        \App\Models\Setting::where('key', 'captcha_register')->delete();
        \App\Models\Setting::where('key', 'captcha_forgot_password')->delete();
        \App\Models\Setting::where('key', 'captcha_redirect')->delete();
    }
};
