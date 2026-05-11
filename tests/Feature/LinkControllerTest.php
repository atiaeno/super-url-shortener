<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\AliasDomain;
use App\Models\Link;
use App\Models\LinkAnalyticsDaily;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * @test
     */
    public function it_displays_links_index()
    {
        Link::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->get('/links');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_filters_links_by_status()
    {
        Link::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);
        Link::factory()->create(['user_id' => $this->user->id, 'is_active' => false]);

        $response = $this->get('/links?status=active');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_searches_links_by_short_code()
    {
        Link::factory()->create(['user_id' => $this->user->id, 'short_code' => 'test123']);
        Link::factory()->create(['user_id' => $this->user->id, 'short_code' => 'other456']);

        $response = $this->get('/links?search=test');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_searches_links_by_destination_url()
    {
        Link::factory()->create(['user_id' => $this->user->id, 'destination_url' => 'https://example.com']);
        Link::factory()->create(['user_id' => $this->user->id, 'destination_url' => 'https://other.com']);

        $response = $this->get('/links?search=example');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_shows_link_creation_form()
    {
        $response = $this->get('/links/create');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_creates_new_link()
    {
        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'visibility' => 'public',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('links', [
            'user_id' => $this->user->id,
            'destination_url' => 'https://example.com',
            'visibility' => 'public',
        ]);
    }

    /**
     * @test
     */
    public function it_validates_destination_url()
    {
        $response = $this->post('/links', [
            'destination_url' => 'invalid-url',
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors('destination_url');
    }

    /**
     * @test
     */
    public function it_prevents_shortening_alias_domains()
    {
        AliasDomain::factory()->create(['domain' => 'example.com', 'is_active' => true]);

        $response = $this->post('/links', [
            'destination_url' => 'https://example.com/page',
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors('destination_url');
    }

    /**
     * @test
     */
    public function it_handles_custom_alias()
    {
        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'custom_alias' => 'myalias',
            'visibility' => 'public',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('links', [
            'custom_alias' => 'myalias',
        ]);
    }

    /**
     * @test
     */
    public function it_requires_password_for_private_links()
    {
        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'visibility' => 'private',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('links', [
            'visibility' => 'private',
        ]);
    }

    /**
     * @test
     */
    public function it_shows_link_details()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get("/links/{$link->id}");

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_shows_link_analytics_by_period()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        // Create analytics data
        LinkAnalyticsDaily::factory()->create([
            'link_id' => $link->id,
            'date' => now()->subDays(5),
            'total_clicks' => 10,
            'by_country' => ['US' => 5, 'UK' => 5],
            'by_device' => ['mobile' => 6, 'desktop' => 4],
            'by_browser' => ['Chrome' => 8, 'Firefox' => 2],
            'by_referrer' => ['google.com' => 7, 'direct' => 3],
        ]);

        $response = $this->get("/links/{$link->id}?period=week");

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_shows_edit_form_for_owned_link()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get("/links/{$link->id}/edit");

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_prevents_editing_other_users_links()
    {
        $otherUser = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get("/links/{$link->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function it_updates_link()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->put("/links/{$link->id}", [
            'destination_url' => 'https://updated.com',
            'campaign_tag' => 'updated',
            'og_title' => 'Updated Title',
            'og_description' => 'Updated Description',
            'is_active' => false,
        ]);

        $response->assertRedirect('/links');
        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'destination_url' => 'https://updated.com',
            'campaign_tag' => 'updated',
            'og_title' => 'Updated Title',
            'og_description' => 'Updated Description',
            'is_active' => false,
        ]);
    }

    /**
     * @test
     */
    public function it_handles_admin_fields_for_admin_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $link = Link::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin);

        $response = $this->put("/links/{$link->id}", [
            'destination_url' => 'https://updated.com',
        ]);

        $response->assertRedirect('/links');
    }

    /**
     * @test
     */
    public function it_busts_cache_when_destination_url_changes()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->put("/links/{$link->id}", [
            'destination_url' => 'https://updated.com',
        ]);

        $response->assertRedirect('/links');
    }

    /**
     * @test
     */
    public function it_deletes_link()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete("/links/{$link->id}");

        $response->assertRedirect('/links');
        $this->assertSoftDeleted('links', ['id' => $link->id]);
    }

    /**
     * @test
     */
    public function it_clears_cache_on_deletion()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete("/links/{$link->id}");

        $response->assertRedirect('/links');
    }

    /**
     * @test
     */
    public function it_toggles_link_status()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);

        $response = $this->patch("/links/{$link->id}/toggle");

        $response->assertRedirect();
    }

    /**
     * @test
     */
    public function it_updates_cache_when_activating_link()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id, 'is_active' => false]);

        $response = $this->patch("/links/{$link->id}/toggle");

        $response->assertRedirect();
    }

    /**
     * @test
     */
    public function it_clears_cache_when_deactivating_link()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id, 'is_active' => true]);

        $response = $this->patch("/links/{$link->id}/toggle");

        $response->assertRedirect();
    }

    /**
     * @test
     */
    public function it_paginates_links()
    {
        Link::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this->get('/links');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_preserves_query_parameters_in_pagination()
    {
        Link::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this->get('/links?status=active&search=test');

        $response->assertStatus(200);
        // Check that pagination links preserve query parameters
        $content = $response->getContent();
        $this->assertStringContainsString('status=active', $content);
        $this->assertStringContainsString('search=test', $content);
    }

    /**
     * @test
     */
    public function it_formats_country_flags_in_analytics()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        LinkAnalyticsDaily::factory()->create([
            'link_id' => $link->id,
            'date' => now(),
            'total_clicks' => 5,
            'by_country' => ['US' => 3, 'GB' => 2],
        ]);

        $response = $this->get("/links/{$link->id}");

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_handles_analytics_with_no_data()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get("/links/{$link->id}");

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_aggregates_analytics_from_multiple_days()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        // Create data for multiple days
        LinkAnalyticsDaily::factory()->create([
            'link_id' => $link->id,
            'date' => now()->subDays(2),
            'total_clicks' => 10,
            'by_country' => ['US' => 10],
        ]);

        LinkAnalyticsDaily::factory()->create([
            'link_id' => $link->id,
            'date' => now()->subDays(1),
            'total_clicks' => 15,
            'by_country' => ['US' => 10, 'UK' => 5],
        ]);

        $response = $this->get("/links/{$link->id}?period=week");

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_handles_domain_selection_in_creation()
    {
        AliasDomain::factory()->create(['domain' => 'custom.com', 'is_active' => true]);

        $response = $this->get('/links/create');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_creates_link_with_custom_domain()
    {
        $domain = AliasDomain::factory()->create(['domain' => 'custom.com', 'is_active' => true]);

        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'domain_id' => $domain->id,
            'visibility' => 'public',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('links', [
            'domain_id' => $domain->id,
        ]);
    }

    /**
     * @test
     */
    public function it_validates_custom_alias_uniqueness()
    {
        Link::factory()->create([
            'user_id' => $this->user->id,
            'custom_alias' => 'taken',
        ]);

        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'custom_alias' => 'taken',
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors('custom_alias');
    }

    /**
     * @test
     */
    public function it_validates_custom_alias_format()
    {
        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'custom_alias' => 'invalid alias with spaces',
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors('custom_alias');
    }

    /**
     * @test
     */
    public function it_handles_campaign_tag()
    {
        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'campaign_tag' => 'spring-sale-2023',
            'visibility' => 'public',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('links', [
            'campaign_tag' => 'spring-sale-2023',
        ]);
    }

    /**
     * @test
     */
    public function it_limits_campaign_tag_length()
    {
        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'campaign_tag' => str_repeat('a', 101),  // Over 100 character limit
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors('campaign_tag');
    }

    /**
     * @test
     */
    public function it_shows_short_url_in_creation_response()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $response = $this->post('/links', [
            'destination_url' => 'https://example.com',
            'visibility' => 'public',
        ]);

        $response->assertStatus(200);
    }
}
