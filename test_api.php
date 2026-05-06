<?php
// Test script to verify API outputs

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create test user and token
$user = App\Models\User::first() ?? App\Models\User::factory()->create();
$token = App\Models\ApiToken::create([
    'user_id' => $user->id,
    'name' => 'test2',
    'token' => bin2hex(random_bytes(32)),
    'expires_at' => now()->addYear(),
]);

$headers = ['Authorization' => 'Bearer ' . $token->token, 'Accept' => 'application/json'];
$baseUrl = 'http://127.0.0.1:8001';

// Get links
echo "=== GET /api/v1/links ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->get($baseUrl . '/api/v1/links?per_page=2');
echo $response->body() . "\n\n";

// Create link
echo "=== POST /api/v1/links ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->post($baseUrl . '/api/v1/links', [
    'url' => 'https://example.com/new-link',
]);
echo $response->body() . "\n\n";

// Get single link by short code
echo "=== GET /api/v1/links/mQ16We ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->get($baseUrl . '/api/v1/links/mQ16We');
echo $response->body() . "\n\n";

// Test QR code (public - no auth)
echo "=== GET /qr/Fw8XRL/svg (public) ===\n";
$qrResponse = Illuminate\Support\Facades\Http::get($baseUrl . '/qr/Fw8XRL/svg');
echo 'Status: ' . $qrResponse->status() . ', Content-Type: ' . $qrResponse->header('Content-Type') . "\n\n";

// Create private link with password
echo "=== POST /api/v1/links (private with password) ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->post($baseUrl . '/api/v1/links', [
    'url' => 'https://example.com/private',
    'visibility' => 'private',
    'password' => 'secret123',
]);
echo $response->body() . "\n";
