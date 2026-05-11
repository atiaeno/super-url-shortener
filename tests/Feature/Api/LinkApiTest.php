<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature\Api;

use App\Models\AliasDomain;
use App\Models\ApiToken;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkApiTest extends TestCase
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

    public function test_can_list_links(): void
    {
        Link::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links');

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_link(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com/test-create',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'short_code',
                    'short_url',
                    'original_url',
                    'alias',
                    'domain_id',
                    'domain',
                    'clicks',
                    'created_at',
                    'qr_code',
                ],
            ]);
    }

    public function test_can_create_link_with_domain_id(): void
    {
        $domain = AliasDomain::factory()->create(['is_active' => true]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com/with-domain',
                'domain_id' => $domain->id,
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.domain_id', $domain->id)
            ->assertJsonPath('data.domain', $domain->domain);

        $this->assertDatabaseHas('links', [
            'user_id' => $this->user->id,
            'domain_id' => $domain->id,
        ]);
    }

    public function test_cannot_create_link_with_inactive_domain(): void
    {
        $domain = AliasDomain::factory()->create(['is_active' => false]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com/bad-domain',
                'domain_id' => $domain->id,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error', 'Selected domain is not available');
    }

    public function test_cannot_create_link_with_nonexistent_domain(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com/bad-domain',
                'domain_id' => 99999,
            ]);

        $response->assertStatus(422);
    }

    public function test_can_get_single_link(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $link->short_code);

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.short_code', $link->short_code);
    }

    public function test_can_update_link(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->putJson('/api/v1/links/' . $link->id, [
                'url' => $link->destination_url,
                'alias' => 'new-alias',
            ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.alias', 'new-alias');
    }

    public function test_can_update_link_domain(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);
        $newDomain = AliasDomain::factory()->create(['is_active' => true, 'domain' => 'new.example.com']);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->putJson('/api/v1/links/' . $link->id, [
                'url' => $link->destination_url,
                'domain_id' => $newDomain->id,
            ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.domain_id', $newDomain->id)
            ->assertJsonPath('data.domain', 'new.example.com');
    }

    public function test_link_response_includes_domain_info(): void
    {
        $domain = AliasDomain::factory()->create(['is_active' => true, 'domain' => 'test.example.com']);
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'domain_id' => $domain->id,
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $link->id);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.domain_id', $domain->id)
            ->assertJsonPath('data.domain', 'test.example.com');

        // Assert short_url contains the domain
        $shortUrl = $response->json('data.short_url');
        $this->assertStringContainsString('test.example.com', $shortUrl);
    }

    public function test_can_delete_link(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->deleteJson('/api/v1/links/' . $link->id);

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // Soft delete - check deleted_at is set
        $this->assertNotNull($link->fresh()->deleted_at);
    }

    public function test_cannot_access_other_users_link(): void
    {
        $otherUser = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $link->id);

        $response->assertStatus(404);
    }

    public function test_url_is_required(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', []);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.url.0', 'The url field is required.');
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/links');
        $response->assertStatus(401);
    }
}
