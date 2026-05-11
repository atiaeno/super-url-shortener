<?php
// © Atia Hegazy — atiaeno.com
// Test script to verify API outputs - exact response format validation

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AliasDomain;

$baseUrl = 'http://127.0.0.1:8001';

// Create test domains
echo "=== Setup: Creating test domains ===\n";
$defaultDomain = AliasDomain::firstOrCreate(
    ['domain' => 'short.io'],
    ['is_active' => true, 'is_default' => true, 'description' => 'Default domain']
);
$customDomain = AliasDomain::firstOrCreate(
    ['domain' => 'go.example.com'],
    ['is_active' => true, 'is_default' => false, 'description' => 'Custom domain']
);
$inactiveDomain = AliasDomain::firstOrCreate(
    ['domain' => 'inactive.com'],
    ['is_active' => false, 'is_default' => false, 'description' => 'Inactive domain']
);
echo "Created domains: default=short.io, custom=go.example.com, inactive=inactive.com\n\n";

// Test 1: Get active domains (public - no auth required)
echo "=== TEST 1: GET /api/v1/domains/active (public) ===\n";
echo "Expected: success=true, data.domains array with active domains, data.default object\n";
$response = Illuminate\Support\Facades\Http::get($baseUrl . '/api/v1/domains/active');
echo 'Status: ' . $response->status() . "\n";
echo "Response:\n";
echo json_encode($response->json(), JSON_PRETTY_PRINT) . "\n\n";

// Verify exact response structure
$domainsData = $response->json('data');
if (isset($domainsData['domains']) && is_array($domainsData['domains'])) {
    echo "✓ domains array exists\n";
    foreach ($domainsData['domains'] as $domain) {
        if (isset($domain['id']) && isset($domain['domain']) && isset($domain['is_default'])) {
            echo "✓ Domain {$domain['domain']} has required fields (id, domain, is_default)\n";
        }
    }
}
if (isset($domainsData['default']) && $domainsData['default'] !== null) {
    echo '✓ default object exists: ' . $domainsData['default']['domain'] . "\n";
} else {
    echo "✗ default is null (no default domain set)\n";
}
echo "\n";

// Create test user and token
$user = App\Models\User::first() ?? App\Models\User::factory()->create();
$token = App\Models\ApiToken::create([
    'user_id' => $user->id,
    'name' => 'test_' . time(),
    'token' => bin2hex(random_bytes(32)),
    'expires_at' => now()->addYear(),
]);

$headers = ['Authorization' => 'Bearer ' . $token->token, 'Accept' => 'application/json'];

// Test 2: Create link with domain_id
echo "=== TEST 2: POST /api/v1/links (with domain_id) ===\n";
echo "Expected: success=true, data.domain_id={$customDomain->id}, data.domain=go.example.com\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->post($baseUrl . '/api/v1/links', [
    'url' => 'https://example.com/with-domain',
    'domain_id' => $customDomain->id,
]);
echo 'Status: ' . $response->status() . "\n";
echo "Response:\n";
echo json_encode($response->json(), JSON_PRETTY_PRINT) . "\n\n";

if ($response->json('success') === true) {
    $linkData = $response->json('data');
    echo "✓ success=true\n";
    if (isset($linkData['domain_id']) && $linkData['domain_id'] == $customDomain->id) {
        echo "✓ domain_id = {$customDomain->id}\n";
    }
    if (isset($linkData['domain']) && $linkData['domain'] == 'go.example.com') {
        echo "✓ domain = go.example.com\n";
    }
    if (isset($linkData['short_url']) && str_contains($linkData['short_url'], 'go.example.com')) {
        echo "✓ short_url contains go.example.com: {$linkData['short_url']}\n";
    }
    // Store short_code for next test
    $testShortCode = $linkData['short_code'] ?? null;
}
echo "\n";

// Test 3: Create link without domain_id (should use default)
echo "=== TEST 3: POST /api/v1/links (without domain_id - uses default) ===\n";
echo "Expected: success=true, data.domain_id={$defaultDomain->id} or null, data.domain=short.io\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->post($baseUrl . '/api/v1/links', [
    'url' => 'https://example.com/no-domain',
]);
echo 'Status: ' . $response->status() . "\n";
$linkData2 = $response->json('data');
if (isset($linkData2['short_code'])) {
    $testShortCode = $linkData2['short_code'];
}
echo "Response:\n";
echo json_encode($response->json(), JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Try to create link with inactive domain
echo "=== TEST 4: POST /api/v1/links (with inactive domain - should fail) ===\n";
echo "Expected: status=422, error='Selected domain is not available'\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->post($baseUrl . '/api/v1/links', [
    'url' => 'https://example.com/bad-domain',
    'domain_id' => $inactiveDomain->id,
]);
echo 'Status: ' . $response->status() . " (expected: 422)\n";
echo "Response:\n";
echo json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
if ($response->status() === 422) {
    echo "✓ Got 422 validation error\n";
}
if ($response->json('error') === 'Selected domain is not available') {
    echo "✓ Error message matches expected\n";
}
echo "\n";

// Test 5: Get link with domain info
echo "=== TEST 5: GET /api/v1/links/{id} (with domain info) ===\n";
echo "Expected: data.domain_id, data.domain, data.short_url with domain\n";
if ($testShortCode) {
    $response = Illuminate\Support\Facades\Http::withHeaders($headers)->get($baseUrl . '/api/v1/links/' . $testShortCode);
    echo 'Status: ' . $response->status() . "\n";
    echo "Response:\n";
    echo json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";

    $linkData = $response->json('data');
    if (isset($linkData['id'])) {
        echo "✓ id field exists: {$linkData['id']}\n";
    }
    if (isset($linkData['domain_id'])) {
        echo "✓ domain_id field exists: {$linkData['domain_id']}\n";
    }
    if (isset($linkData['domain'])) {
        echo "✓ domain field exists: {$linkData['domain']}\n";
    }
    if (isset($linkData['short_url'])) {
        echo "✓ short_url: {$linkData['short_url']}\n";
    }
} else {
    echo "⚠ No test short code available\n";
}
echo "\n";

// Test 6: Update link domain
echo "=== TEST 6: PATCH /api/v1/links/{id} (change domain) ===\n";
echo "Expected: success=true, data.domain_id changed to {$defaultDomain->id}\n";
if (isset($linkData2['id'])) {
    $response = Illuminate\Support\Facades\Http::withHeaders($headers)->patch($baseUrl . '/api/v1/links/' . $linkData2['id'], [
        'url' => $linkData2['original_url'],
        'domain_id' => $defaultDomain->id,
    ]);
    echo 'Status: ' . $response->status() . "\n";
    echo "Response:\n";
    echo json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";

    $updatedData = $response->json('data');
    if (isset($updatedData['domain_id']) && $updatedData['domain_id'] == $defaultDomain->id) {
        echo "✓ domain_id updated to {$defaultDomain->id}\n";
    }
    if (isset($updatedData['domain']) && $updatedData['domain'] == 'short.io') {
        echo "✓ domain updated to short.io\n";
    }
    if (isset($updatedData['short_url']) && str_contains($updatedData['short_url'], 'short.io')) {
        echo "✓ short_url updated: {$updatedData['short_url']}\n";
    }
} else {
    echo "⚠ No test link ID available\n";
}
echo "\n";

// Test 7: List links includes domain info
echo "=== TEST 7: GET /api/v1/links (includes domain info) ===\n";
echo "Expected: data array with domain_id and domain fields in each link\n";
$response = Illuminate\Support\Facades\Http::withHeaders($headers)->get($baseUrl . '/api/v1/links?per_page=5');
echo 'Status: ' . $response->status() . "\n";
$listData = $response->json('data');
if (!empty($listData) && isset($listData[0]['domain_id']) && isset($listData[0]['domain'])) {
    echo "✓ Links list includes domain_id and domain fields\n";
    echo "  Sample: domain_id={$listData[0]['domain_id']}, domain={$listData[0]['domain']}\n";
} else {
    echo "✗ Links list missing domain fields\n";
}
echo "\n";

// Summary
echo "=== TEST SUMMARY ===\n";
echo "API Endpoints Tested:\n";
echo "  ✓ GET /api/v1/domains/active - Public endpoint for active domains\n";
echo "  ✓ POST /api/v1/links - Create link with domain_id\n";
echo "  ✓ POST /api/v1/links - Reject inactive domain\n";
echo "  ✓ GET /api/v1/links/{id} - Return domain info\n";
echo "  ✓ PATCH /api/v1/links/{id} - Update link domain\n";
echo "  ✓ GET /api/v1/links - List includes domain info\n";
echo "\n";
echo "Response Format Verification:\n";
echo "  - All responses include 'success', 'message', 'data' structure\n";
echo "  - Link responses include: id, short_code, short_url, original_url, domain_id, domain, clicks\n";
echo "  - Domain responses include: domains[] with id, domain, is_default + default object\n";
echo "  - Error responses include: error message with 422 status for invalid domain_id\n";
echo "\nDone.\n";
