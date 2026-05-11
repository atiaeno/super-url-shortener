<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Models;

use App\Models\AliasDomain;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function it_uses_soft_deletes()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $link->delete();

        $this->assertSoftDeleted($link);
        $this->assertNotNull($link->deleted_at);
    }

    /**
     * @test
     */
    public function it_hashes_destination_url_on_save()
    {
        $url = 'https://example.com/test';
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'destination_url' => $url,
        ]);

        $expectedHash = hash('sha256', $url);
        $this->assertEquals($expectedHash, $link->destination_url_hash);
    }

    /**
     * @test
     */
    public function it_updates_hash_when_url_changes()
    {
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'destination_url' => 'https://example.com/old',
        ]);

        $originalHash = $link->destination_url_hash;

        $link->update(['destination_url' => 'https://example.com/new']);

        $this->assertNotEquals($originalHash, $link->fresh()->destination_url_hash);
        $this->assertEquals(
            hash('sha256', 'https://example.com/new'),
            $link->fresh()->destination_url_hash
        );
    }

    /**
     * @test
     */
    public function it_hashes_password_for_private_links()
    {
        $password = 'secret123';
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'visibility' => 'private',
            'password' => $password,
        ]);

        $this->assertNotEquals($password, $link->password);
        $this->assertTrue(password_verify($password, $link->password));
    }

    /**
     * @test
     */
    public function it_removes_password_for_public_links()
    {
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'visibility' => 'private',
            'password' => 'secret123',
        ]);

        $link->update(['visibility' => 'public']);

        $this->assertNull($link->fresh()->password);
    }

    /**
     * @test
     */
    public function it_does_not_rehash_password_if_unchanged()
    {
        $password = 'secret123';
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'visibility' => 'private',
            'password' => $password,
        ]);

        $originalHash = $link->password;

        // Update another field, not password
        $link->update(['destination_url' => 'https://new-url.com']);

        $this->assertEquals($originalHash, $link->fresh()->password);
    }

    /**
     * @test
     */
    public function it_belongs_to_user()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(User::class, $link->user);
        $this->assertEquals($this->user->id, $link->user->id);
    }

    /**
     * @test
     */
    public function it_belongs_to_domain()
    {
        $domain = AliasDomain::factory()->create();
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'domain_id' => $domain->id,
        ]);

        $this->assertInstanceOf(AliasDomain::class, $link->domain);
        $this->assertEquals($domain->id, $link->domain->id);
    }

    /**
     * @test
     */
    public function it_has_many_clicks()
    {
        $link = Link::factory()->create(['user_id' => $this->user->id]);

        $clicks = $link->clicks()->createMany([
            ['ip_hash' => 'hash1', 'country_code' => 'US'],
            ['ip_hash' => 'hash2', 'country_code' => 'UK'],
        ]);

        $this->assertCount(2, $link->clicks);
        $this->assertEquals($clicks->pluck('id'), $link->clicks->pluck('id'));
    }

    /**
     * @test
     */
    public function it_generates_short_url_with_default_domain()
    {
        config(['app.url' => 'https://short.ly']);

        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'abc123',
        ]);

        $this->assertEquals('https://short.ly/abc123', $link->short_url);
    }

    /**
     * @test
     */
    public function it_generates_short_url_with_custom_domain()
    {
        $domain = AliasDomain::factory()->create(['domain' => 'custom.com']);

        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'abc123',
            'domain_id' => $domain->id,
        ]);

        $this->assertEquals('https://custom.com/abc123', $link->short_url);
    }

    /**
     * @test
     */
    public function it_generates_short_url_with_custom_alias()
    {
        config(['app.url' => 'https://short.ly']);

        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'abc123',
            'custom_alias' => 'myalias',
        ]);

        $this->assertEquals('https://short.ly/myalias', $link->short_url);
    }

    /**
     * @test
     */
    public function it_adds_https_to_domain_without_protocol()
    {
        $domain = AliasDomain::factory()->create(['domain' => 'example.com']);

        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'abc123',
            'domain_id' => $domain->id,
        ]);

        $this->assertEquals('https://example.com/abc123', $link->short_url);
    }

    /**
     * @test
     */
    public function it_trims_trailing_slash_from_domain()
    {
        config(['app.url' => 'https://short.ly/']);

        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'short_code' => 'abc123',
        ]);

        $this->assertEquals('https://short.ly/abc123', $link->short_url);
    }

    /**
     * @test
     */
    public function it_filters_active_links()
    {
        $activeLink = Link::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $inactiveLink = Link::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false,
        ]);

        $expiredLink = Link::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $activeLinks = Link::active()->get();

        $this->assertCount(1, $activeLinks);
        $this->assertEquals($activeLink->id, $activeLinks->first()->id);
    }

    /**
     * @test
     */
    public function it_filters_links_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $link1 = Link::factory()->create(['user_id' => $user1->id]);
        $link2 = Link::factory()->create(['user_id' => $user2->id]);

        $user1Links = Link::forUser($user1->id)->get();
        $user2Links = Link::forUser($user2->id)->get();

        $this->assertCount(1, $user1Links);
        $this->assertCount(1, $user2Links);
        $this->assertEquals($link1->id, $user1Links->first()->id);
        $this->assertEquals($link2->id, $user2Links->first()->id);
    }

    /**
     * @test
     */
    public function it_increments_clicks_count()
    {
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'clicks_count' => 5,
        ]);

        $link->incrementClicks();

        $this->assertEquals(6, $link->fresh()->clicks_count);
    }

    /**
     * @test
     */
    public function it_casts_attributes_correctly()
    {
        $link = Link::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => 1,
            'visibility' => 'public',
            'expires_at' => '2023-12-31 23:59:59',
            'clicks_count' => '10',
            'report_count' => '5',
        ]);

        $this->assertIsBool($link->is_active);
        $this->assertIsString($link->visibility);
        $this->assertInstanceOf(\Carbon\Carbon::class, $link->expires_at);
        $this->assertIsInt($link->clicks_count);
        $this->assertIsInt($link->report_count);
    }

    /**
     * @test
     */
    public function it_handles_expiration_in_active_scope()
    {
        $futureLink = Link::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $pastLink = Link::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $noExpiryLink = Link::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'expires_at' => null,
        ]);

        $activeLinks = Link::active()->get();

        $this->assertCount(2, $activeLinks);
        $this->assertContains($futureLink->id, $activeLinks->pluck('id'));
        $this->assertContains($noExpiryLink->id, $activeLinks->pluck('id'));
        $this->assertNotContains($pastLink->id, $activeLinks->pluck('id'));
    }

    /**
     * @test
     */
    public function it_handles_fillable_fields()
    {
        $data = [
            'user_id' => $this->user->id,
            'short_code' => 'abc123',
            'destination_url' => 'https://example.com',
            'custom_alias' => 'myalias',
            'campaign_tag' => 'campaign1',
            'og_title' => 'Test Title',
            'og_description' => 'Test Description',
            'og_image' => 'https://example.com/image.jpg',
            'is_active' => true,
            'visibility' => 'public',
            'expires_at' => now()->addDay(),
            'clicks_count' => 0,
            'report_count' => 0,
        ];

        $link = Link::create($data);

        foreach ($data as $key => $value) {
            if ($value instanceof \Carbon\Carbon) {
                $this->assertEquals($value->toDateTimeString(), $link->$key->toDateTimeString());
            } else {
                $this->assertEquals($value, $link->$key);
            }
        }
    }
}
