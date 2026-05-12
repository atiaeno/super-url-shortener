<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $apiToken = ApiToken::factory()->create([
            'user_id' => $this->user->id,
            'token' => 'test-token-' . uniqid(),
        ]);
        $this->token = $apiToken->token;
    }

    /**
     * Test that the middleware is actually being called.
     */
    public function test_middleware_is_called(): void
    {
        // Directly test the middleware
        $middleware = new \App\Http\Middleware\DynamicRateLimit();

        $request = \Illuminate\Http\Request::create('/api/v1/user', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $this->token);
        $request->setUserResolver(fn() => $this->user);

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['test' => 'success']);
        }, 'api');

        // Should have rate limit headers
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
        $this->assertTrue($response->headers->has('X-RateLimit-Reset'));
    }

    /**
     * Test the route with rate limiting middleware applied globally.
     */
    public function test_route_with_manual_middleware(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/v1/user');

        // Should be successful with rate limit headers
        $response->assertStatus(200);

        // Should have rate limit headers
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
        $response->assertHeader('X-RateLimit-Reset');

        // Verify header values are reasonable
        $limit = $response->headers->get('X-RateLimit-Limit');
        $remaining = $response->headers->get('X-RateLimit-Remaining');

        $this->assertEquals('100', $limit);
        $this->assertLessThanOrEqual(100, (int) $remaining);
        $this->assertGreaterThanOrEqual(0, (int) $remaining);
    }

    /**
     * Test rate limit exceeded response.
     */
    public function test_rate_limit_exceeded(): void
    {
        // Create a separate user and token for this test
        $user = User::factory()->create();
        $apiToken = ApiToken::factory()->create([
            'user_id' => $user->id,
            'token' => 'rate-limit-test-' . uniqid(),
        ]);

        // Temporarily set a very low rate limit for testing
        // We'll modify the middleware to use a test limit
        $middleware = new \App\Http\Middleware\DynamicRateLimit();

        // Create a custom request that will hit the rate limit
        $request = \Illuminate\Http\Request::create('/api/v1/user', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $apiToken->token);
        $request->setUserResolver(fn() => $user);

        // Hit the rate limit by making multiple requests with the same key
        $key = sha1($user->id . '|' . $request->ip() . '|api');

        // Manually hit the rate limit to test the 429 response
        for ($i = 0; $i < 101; $i++) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, 3600);
        }

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['test' => 'success']);
        }, 'api');

        // Should return 429 with rate limit headers
        $this->assertEquals(429, $response->getStatusCode());
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
        $this->assertTrue($response->headers->has('X-RateLimit-Reset'));
        $this->assertTrue($response->headers->has('Retry-After'));

        // Check that remaining is 0
        $this->assertEquals('0', $response->headers->get('X-RateLimit-Remaining'));

        // Check response body
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Too many requests.', $data['error']);
    }
}
