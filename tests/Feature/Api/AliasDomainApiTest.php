<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature\Api;

use App\Models\AliasDomain;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AliasDomainApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ApiToken $token;
    private string $authHeader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = ApiToken::factory()->create(['user_id' => $this->user->id]);
        $this->authHeader = 'Bearer ' . $this->token->token;
    }

    public function test_can_get_active_domains(): void
    {
        AliasDomain::factory()->create(['domain' => 'short.io', 'is_active' => true, 'is_default' => true]);
        AliasDomain::factory()->create(['domain' => 'go.example.com', 'is_active' => true, 'is_default' => false]);
        AliasDomain::factory()->create(['domain' => 'inactive.com', 'is_active' => false]);

        $response = $this->getJson('/api/v1/domains/active');

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'domains',
                    'default',
                ],
            ])
            ->assertJsonCount(2, 'data.domains'); // Only active domains
    }

    public function test_active_domains_response_includes_default(): void
    {
        $defaultDomain = AliasDomain::factory()->create([
            'domain' => 'default.com',
            'is_active' => true,
            'is_default' => true,
        ]);

        $response = $this->getJson('/api/v1/domains/active');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.default.id', $defaultDomain->id)
            ->assertJsonPath('data.default.domain', 'default.com')
            ->assertJsonPath('data.default.is_default', true);
    }

    public function test_active_domains_no_auth_required(): void
    {
        AliasDomain::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/domains/active');

        $response->assertStatus(200);
    }

    public function test_active_domains_returns_domain_fields(): void
    {
        AliasDomain::factory()->create([
            'domain' => 'test.example.com',
            'is_active' => true,
            'is_default' => true,
            'description' => 'Test domain',
        ]);

        $response = $this->getJson('/api/v1/domains/active');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.domains.0.id', 1)
            ->assertJsonPath('data.domains.0.domain', 'test.example.com')
            ->assertJsonPath('data.domains.0.is_default', true);
    }

    public function test_inactive_domains_not_included(): void
    {
        AliasDomain::factory()->create(['domain' => 'active.com', 'is_active' => true]);
        AliasDomain::factory()->create(['domain' => 'inactive.com', 'is_active' => false]);

        $response = $this->getJson('/api/v1/domains/active');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.domains')
            ->assertJsonPath('data.domains.0.domain', 'active.com');
    }

    public function test_empty_domains_returns_empty_array(): void
    {
        $response = $this->getJson('/api/v1/domains/active');

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data.domains')
            ->assertJsonPath('data.default', null);
    }

    public function test_response_matches_expected_format(): void
    {
        AliasDomain::factory()->create([
            'domain' => 'short.io',
            'is_active' => true,
            'is_default' => true,
        ]);
        AliasDomain::factory()->create([
            'domain' => 'go.example.com',
            'is_active' => true,
            'is_default' => false,
        ]);

        $response = $this->getJson('/api/v1/domains/active');

        $expected = [
            'success' => true,
            'data' => [
                'domains' => [
                    [
                        'id' => 1,
                        'domain' => 'short.io',
                        'is_default' => true,
                    ],
                    [
                        'id' => 2,
                        'domain' => 'go.example.com',
                        'is_default' => false,
                    ],
                ],
                'default' => [
                    'id' => 1,
                    'domain' => 'short.io',
                    'is_default' => true,
                ],
            ],
        ];

        $response->assertJson($expected);
    }
}
