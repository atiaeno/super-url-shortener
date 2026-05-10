<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Clear cache first
Cache::forget('app_settings');

DB::table('settings')->updateOrInsert(
    ['key' => 'redirect_captcha'],
    ['value' => json_encode(true), 'group' => 'security']
);

DB::table('settings')->updateOrInsert(
    ['key' => 'captcha_site_key'],
    ['value' => json_encode('6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'), 'group' => 'security']
);

// Clear cache again after update
Cache::forget('app_settings');

echo "CAPTCHA settings updated and cache cleared!\n";
