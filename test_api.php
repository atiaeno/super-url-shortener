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

// Get affiliate info (with test data)
echo "=== GET /api/v1/affiliate ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->get('http://127.0.0.1:8000/api/v1/affiliate');
echo '<pre>' . json_encode(json_decode($response->body()), JSON_PRETTY_PRINT) . '</pre>' . "\n\n";

// Get tiers
echo "=== GET /api/v1/affiliate/tiers ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->get('http://127.0.0.1:8000/api/v1/affiliate/tiers');
echo '<pre>' . json_encode(json_decode($response->body()), JSON_PRETTY_PRINT) . '</pre>' . "\n\n";

// Get payouts
echo "=== GET /api/v1/affiliate/payouts ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->get('http://127.0.0.1:8000/api/v1/affiliate/payouts');
echo '<pre>' . json_encode(json_decode($response->body()), JSON_PRETTY_PRINT) . '</pre>' . "\n\n";

// Request payout
echo "=== POST /api/v1/affiliate/payout ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->post('http://127.0.0.1:8000/api/v1/affiliate/payout', [
    'payment_email' => 'test@example.com',
]);
echo '<pre>' . json_encode(json_decode($response->body()), JSON_PRETTY_PRINT) . '</pre>' . "\n\n";

// Get payouts after request
echo "=== GET /api/v1/affiliate/payouts (after request) ===\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->get('http://127.0.0.1:8000/api/v1/affiliate/payouts');
echo '<pre>' . json_encode(json_decode($response->body()), JSON_PRETTY_PRINT) . '</pre>' . "\n";
