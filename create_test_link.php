<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('links')->insert([
    'user_id' => 1,
    'short_code' => 'test',
    'destination_url' => 'https://example.com',
    'is_active' => 1,
    'visibility' => 'public',
    'clicks_count' => 0,
    'report_count' => 0,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Test link created: http://127.0.0.1:8000/test\n";
