<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature;

use App\Models\Link;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_page_loads_for_valid_link(): void
    {
        $link = Link::create([
            'short_code' => 'test123',
            'destination_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $response = $this->get('/test123');

        $response->assertStatus(200);
        $response->assertViewHas('state', 'redirect');
    }

    public function test_redirect_page_shows_404_for_invalid_link(): void
    {
        $response = $this->get('/nonexistent');

        $response->assertStatus(404);
        $response->assertViewHas('state', 'not-found');
    }

    public function test_redirect_page_shows_410_for_expired_link(): void
    {
        $link = Link::create([
            'short_code' => 'expired',
            'destination_url' => 'https://example.com',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->get('/expired');

        $response->assertStatus(410);
        $response->assertViewHas('state', 'expired');
    }

    public function test_redirect_page_shows_404_for_inactive_link(): void
    {
        $link = Link::create([
            'short_code' => 'inactive',
            'destination_url' => 'https://example.com',
            'is_active' => false,
        ]);

        $response = $this->get('/inactive');

        $response->assertStatus(404);
    }

    public function test_redirect_uses_settings_countdown(): void
    {
        Setting::set('redirect_countdown', 10);

        $link = Link::create([
            'short_code' => 'countdown',
            'destination_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $response = $this->get('/countdown');

        $response->assertStatus(200);
        $response->assertViewHas('countdown', 10);
    }

    public function test_redirect_uses_settings_mode(): void
    {
        Setting::set('redirect_mode', 'manual');

        $link = Link::create([
            'short_code' => 'manualmode',
            'destination_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $response = $this->get('/manualmode');

        $response->assertStatus(200);
        $response->assertViewHas('redirectMode', 'manual');
    }

    public function test_social_crawler_receives_og_tags(): void
    {
        $link = Link::create([
            'short_code' => 'ogtest',
            'destination_url' => 'https://example.com',
            'is_active' => true,
            'og_title' => 'Test OG Title',
            'og_description' => 'Test OG Description',
        ]);

        $response = $this->get('/ogtest', [
            'User-Agent' => 'facebookexternalhit/1.1',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
