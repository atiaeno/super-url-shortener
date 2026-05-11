<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature\Api;

use App\Models\AliasDomain;
use App\Models\ApiToken;
use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LinkControllerTest extends TestCase
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

    /**
     * @test
     */
    public function can_list_links(): void
    {
        Link::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => [
                    'current_page',
                    'total_pages',
                    'total_count',
                    'per_page',
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    /**
     * @test
     */
    public function links_are_paginated(): void
    {
        Link::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links');

        $response
            ->assertStatus(200)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total_count', 25)
            ->assertJsonPath('meta.total_pages', 2);
    }

    /**
     * @test
     */
    public function can_customize_per_page(): void
    {
        Link::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links?per_page=3');

        $response
            ->assertStatus(200)
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonCount(3, 'data');
    }

    /**
     * @test
     */
    public function per_page_is_capped_at_100(): void
    {
        Link::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links?per_page=200');

        $response
            ->assertStatus(200)
            ->assertJsonPath('meta.per_page', 100);
    }

    /**
     * @test
     */
    public function users_can_only_see_their_own_links(): void
    {
        $otherUser = User::factory()->create();
        Link::factory()->create(['user_id' => $otherUser->id]);
        Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * @test
     */
    public function can_create_link(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com/long/path',
            ]);

        $response
            ->assertStatus(201)
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

        $this->assertDatabaseHas('links', [
            'user_id' => $this->user->id,
            'destination_url' => 'https://example.com/long/path',
        ]);
    }

    /**
     * @test
     */
    public function can_create_link_with_domain_id(): void
    {
        $domain = AliasDomain::factory()->create([
            'domain' => 'go.example.com',
            'is_active' => true,
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com',
                'domain_id' => $domain->id,
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.domain_id', $domain->id)
            ->assertJsonPath('data.domain', 'go.example.com');

        $this->assertStringContainsString('go.example.com', $response->json('data.short_url'));
    }

    /**
     * @test
     */
    public function cannot_create_link_with_inactive_domain(): void
    {
        $domain = AliasDomain::factory()->create(['is_active' => false]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com',
                'domain_id' => $domain->id,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error', 'Selected domain is not available');
    }

    /**
     * @test
     */
    public function can_update_link_domain(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);
        $newDomain = AliasDomain::factory()->create([
            'domain' => 'custom.example.com',
            'is_active' => true,
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->patchJson('/api/v1/links/' . $link->id, [
                'url' => $link->destination_url,
                'domain_id' => $newDomain->id,
            ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.domain_id', $newDomain->id)
            ->assertJsonPath('data.domain', 'custom.example.com');

        $this->assertStringContainsString('custom.example.com', $response->json('data.short_url'));
    }

    /**
     * @test
     */
    public function can_create_link_with_custom_alias(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com',
                'alias' => 'my-custom-link',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.short_code', 'my-custom-link');

        $this->assertDatabaseHas('links', [
            'short_code' => 'my-custom-link',
            'custom_alias' => 'my-custom-link',
        ]);
    }

    /**
     * @test
     */
    public function url_is_required(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /**
     * @test
     */
    public function url_must_be_valid(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'not-a-valid-url',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /**
     * @test
     */
    public function alias_must_be_unique(): void
    {
        Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'taken',
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->postJson('/api/v1/links', [
                'url' => 'https://example.com',
                'alias' => 'taken',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['alias']);
    }

    /**
     * @test
     */
    public function can_get_single_link_by_id(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $link->short_code);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.short_code', $link->short_code);
    }

    /**
     * @test
     */
    public function can_get_single_link_by_short_code(): void
    {
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'my-code',
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/my-code');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.short_code', 'my-code');
    }

    /**
     * @test
     */
    public function getting_nonexistent_link_returns_404(): void
    {
        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/nonexistent');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function cannot_access_other_users_link(): void
    {
        $otherUser = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $link->id);

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function can_update_link(): void
    {
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'destination_url' => 'https://old.example.com',
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->patchJson('/api/v1/links/' . $link->id, [
                'url' => 'https://new.example.com',
            ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.original_url', 'https://new.example.com');

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'destination_url' => 'https://new.example.com',
        ]);
    }

    /**
     * @test
     */
    public function can_update_link_alias(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->patchJson('/api/v1/links/' . $link->id, [
                'url' => $link->destination_url,
                'alias' => 'new-alias',
            ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.short_code', 'new-alias');
    }

    /**
     * @test
     */
    public function updating_link_clears_old_cache(): void
    {
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'old-code',
        ]);
        Cache::put('redirect:default:old-code', 'https://old.com', 3600);

        $this
            ->withHeader('Authorization', $this->authHeader)
            ->patchJson('/api/v1/links/' . $link->id, [
                'url' => 'https://new.com',
                'alias' => 'new-code',
            ]);

        $this->assertNull(Cache::get('redirect:default:old-code'));
    }

    /**
     * @test
     */
    public function can_delete_link(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->deleteJson('/api/v1/links/' . $link->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['deleted_at'],
            ]);

        $this->assertSoftDeleted('links', ['id' => $link->id]);
    }

    /**
     * @test
     */
    public function deleting_link_clears_cache(): void
    {
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'to-delete',
        ]);
        Cache::put('redirect:default:to-delete', 'https://example.com', 3600);

        $this
            ->withHeader('Authorization', $this->authHeader)
            ->deleteJson('/api/v1/links/' . $link->id);

        $this->assertNull(Cache::get('redirect:default:to-delete'));
    }

    /**
     * @test
     */
    public function cannot_delete_other_users_link(): void
    {
        $otherUser = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $otherUser->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->deleteJson('/api/v1/links/' . $link->id);

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function link_response_includes_clicks_count(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        // Create actual Click records
        Click::factory()->count(5)->forLink($link)->create();

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $link->id);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.clicks', 5);
    }

    /**
     * @test
     */
    public function link_response_includes_short_url(): void
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links/' . $link->id);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.short_url', config('app.url') . '/' . $link->short_code);
    }

    /**
     * @test
     */
    public function link_response_includes_domain_fields(): void
    {
        $domain = AliasDomain::factory()->create([
            'domain' => 'branded.example.com',
            'is_active' => true,
        ]);
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
            ->assertJsonPath('data.domain', 'branded.example.com')
            ->assertJsonPath('data.short_url', 'https://branded.example.com/' . $link->short_code);
    }

    /**
     * @test
     */
    public function list_links_includes_domain_info(): void
    {
        $domain = AliasDomain::factory()->create(['is_active' => true]);
        Link::factory()->create([
            'user_id' => $this->user->id,
            'domain_id' => $domain->id,
        ]);

        $response = $this
            ->withHeader('Authorization', $this->authHeader)
            ->getJson('/api/v1/links');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.0.domain_id', $domain->id)
            ->assertJsonPath('data.0.domain', $domain->domain);
    }
}
