<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonStructure(['timestamp']);
    }

    /**
     * @test
     */
    public function api_info_endpoint_returns_documentation(): void
    {
        $response = $this->getJson('/api/v1/');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'version',
                'documentation',
            ]);
    }

    /**
     * @test
     */
    public function requests_without_token_are_rejected(): void
    {
        $response = $this->getJson('/api/v1/links');

        $response
            ->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
                'message' => 'API token is required.',
            ]);
    }

    /**
     * @test
     */
    public function requests_with_invalid_token_are_rejected(): void
    {
        $response = $this
            ->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/v1/links');

        $response
            ->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired API token.',
            ]);
    }

    /**
     * @test
     */
    public function requests_with_valid_token_are_accepted(): void
    {
        $user = User::factory()->create();
        $token = ApiToken::factory()->create(['user_id' => $user->id]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token->token)
            ->getJson('/api/v1/user');

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /**
     * @test
     */
    public function expired_tokens_are_rejected(): void
    {
        $user = User::factory()->create();
        $token = ApiToken::factory()->create([
            'user_id' => $user->id,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token->token)
            ->getJson('/api/v1/links');

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function token_cannot_be_passed_as_query_parameter_for_security(): void
    {
        $user = User::factory()->create();
        $token = ApiToken::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/user?api_key=' . $token->token);

        $response
            ->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
                'message' => 'API token is required.',
            ]);
    }

    /**
     * @test
     */
    public function last_used_timestamp_is_updated(): void
    {
        $user = User::factory()->create();
        $token = ApiToken::factory()->create([
            'user_id' => $user->id,
            'last_used_at' => null,
        ]);

        $this
            ->withHeader('Authorization', 'Bearer ' . $token->token)
            ->getJson('/api/v1/user');

        $token->refresh();
        $this->assertNotNull($token->last_used_at);
    }
}
