<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature;

use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_shorten_url(): void
    {
        $response = $this->postJson('/guest/shorten', [
            'url' => 'https://example.com/test',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'short_url',
                'short_code',
            ]);
    }

    public function test_guest_shorten_requires_url(): void
    {
        $response = $this->postJson('/guest/shorten', []);

        $response->assertStatus(422);
    }

    public function test_guest_shorten_validates_url_format(): void
    {
        $response = $this->postJson('/guest/shorten', [
            'url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_shorten_returns_existing_link(): void
    {
        $existingLink = Link::create([
            'short_code' => 'exist123',
            'destination_url' => 'https://example.com/duplicate',
            'is_active' => true,
        ]);

        $response = $this->postJson('/guest/shorten', [
            'url' => 'https://example.com/duplicate',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('short_code', 'exist123');
    }

    public function test_guest_shorten_rate_limited(): void
    {
        for ($i = 0; $i < 31; $i++) {
            $this->postJson('/guest/shorten', [
                'url' => "https://example.com/test{$i}",
            ]);
        }

        $response = $this->postJson('/guest/shorten', [
            'url' => 'https://example.com/rate-limit-test',
        ]);

        $response->assertStatus(429);
    }
}
