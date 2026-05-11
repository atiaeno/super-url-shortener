<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Models;

use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClickTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Link $link;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->link = Link::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_belongs_to_link()
    {
        $click = Click::factory()->create([
            'link_id' => $this->link->id,
        ]);

        $this->assertInstanceOf(Link::class, $click->link);
        $this->assertEquals($this->link->id, $click->link->id);
    }

    /** @test */
    public function it_filters_clicks_by_link()
    {
        $link1 = Link::factory()->create(['user_id' => $this->user->id]);
        $link2 = Link::factory()->create(['user_id' => $this->user->id]);

        $click1 = Click::factory()->create(['link_id' => $link1->id]);
        $click2 = Click::factory()->create(['link_id' => $link2->id]);

        $link1Clicks = Click::forLink($link1->id)->get();
        $link2Clicks = Click::forLink($link2->id)->get();

        $this->assertCount(1, $link1Clicks);
        $this->assertCount(1, $link2Clicks);
        $this->assertEquals($click1->id, $link1Clicks->first()->id);
        $this->assertEquals($click2->id, $link2Clicks->first()->id);
    }

    /** @test */
    public function it_filters_clicks_by_period()
    {
        $startDate = now()->subDays(7);
        $endDate = now()->subDays(3);

        $clickInPeriod = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(5),
        ]);

        $clickBeforePeriod = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(10),
        ]);

        $clickAfterPeriod = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => now()->subDays(1),
        ]);

        $periodClicks = Click::forPeriod($startDate, $endDate)->get();

        $this->assertCount(1, $periodClicks);
        $this->assertEquals($clickInPeriod->id, $periodClicks->first()->id);
    }

    /** @test */
    public function it_filters_clicks_by_country()
    {
        $usClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'country_code' => 'US',
        ]);

        $ukClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'country_code' => 'UK',
        ]);

        $usClicks = Click::fromCountry('US')->get();
        $ukClicks = Click::fromCountry('UK')->get();

        $this->assertCount(1, $usClicks);
        $this->assertCount(1, $ukClicks);
        $this->assertEquals($usClick->id, $usClicks->first()->id);
        $this->assertEquals($ukClick->id, $ukClicks->first()->id);
    }

    /** @test */
    public function it_filters_clicks_by_device_type()
    {
        $mobileClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'device_type' => 'mobile',
        ]);

        $desktopClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'device_type' => 'desktop',
        ]);

        $tabletClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'device_type' => 'tablet',
        ]);

        $mobileClicks = Click::onDevice('mobile')->get();
        $desktopClicks = Click::onDevice('desktop')->get();
        $tabletClicks = Click::onDevice('tablet')->get();

        $this->assertCount(1, $mobileClicks);
        $this->assertCount(1, $desktopClicks);
        $this->assertCount(1, $tabletClicks);
    }

    /** @test */
    public function it_filters_clicks_by_referrer_domain()
    {
        $googleClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'referrer' => 'https://google.com/search?q=test',
            'referrer_domain' => 'google.com',
        ]);

        $facebookClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'referrer' => 'https://facebook.com/post/123',
            'referrer_domain' => 'facebook.com',
        ]);

        $directClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'referrer' => null,
            'referrer_domain' => null,
        ]);

        $googleClicks = Click::withReferrer('google.com')->get();
        $facebookClicks = Click::withReferrer('facebook.com')->get();

        $this->assertCount(1, $googleClicks);
        $this->assertCount(1, $facebookClicks);
        $this->assertEquals($googleClick->id, $googleClicks->first()->id);
        $this->assertEquals($facebookClick->id, $facebookClicks->first()->id);
    }

    /** @test */
    public function it_handles_null_referrer_in_with_referrer_scope()
    {
        $clickWithReferrer = Click::factory()->create([
            'link_id' => $this->link->id,
            'referrer' => 'https://example.com',
            'referrer_domain' => 'example.com',
        ]);

        $clickWithoutReferrer = Click::factory()->create([
            'link_id' => $this->link->id,
            'referrer' => null,
            'referrer_domain' => null,
        ]);

        $referrerClicks = Click::withReferrer('example.com')->get();

        $this->assertCount(1, $referrerClicks);
        $this->assertEquals($clickWithReferrer->id, $referrerClicks->first()->id);
    }

    /** @test */
    public function it_casts_created_at_to_datetime()
    {
        $click = Click::factory()->create([
            'link_id' => $this->link->id,
            'created_at' => '2023-12-31 23:59:59',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $click->created_at);
    }

    /** @test */
    public function it_handles_fillable_fields()
    {
        $data = [
            'link_id' => $this->link->id,
            'ip_hash' => hash('sha256', '192.168.1.1'),
            'country_code' => 'US',
            'device_type' => 'mobile',
            'browser' => 'Chrome',
            'os' => 'Android',
            'referrer' => 'https://google.com',
            'referrer_domain' => 'google.com',
        ];

        $click = Click::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $click->$key);
        }
    }

    /** @test */
    public function it_combines_multiple_scopes()
    {
        $startDate = now()->subDays(7);
        $endDate = now()->subDays(1);

        $targetClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'country_code' => 'US',
            'device_type' => 'mobile',
            'created_at' => now()->subDays(3),
        ]);

        // Create other clicks that don't match all criteria
        Click::factory()->create([
            'link_id' => $this->link->id,
            'country_code' => 'UK',
            'device_type' => 'mobile',
            'created_at' => now()->subDays(3),
        ]);

        Click::factory()->create([
            'link_id' => $this->link->id,
            'country_code' => 'US',
            'device_type' => 'desktop',
            'created_at' => now()->subDays(3),
        ]);

        Click::factory()->create([
            'link_id' => $this->link->id,
            'country_code' => 'US',
            'device_type' => 'mobile',
            'created_at' => now()->subDays(10),
        ]);

        $filteredClicks = Click::forLink($this->link->id)
            ->forPeriod($startDate, $endDate)
            ->fromCountry('US')
            ->onDevice('mobile')
            ->get();

        $this->assertCount(1, $filteredClicks);
        $this->assertEquals($targetClick->id, $filteredClicks->first()->id);
    }

    /** @test */
    public function it_handles_empty_results_in_scopes()
    {
        $clicks = Click::fromCountry('NONEXISTENT')->get();
        $this->assertCount(0, $clicks);

        $clicks = Click::onDevice('nonexistent')->get();
        $this->assertCount(0, $clicks);

        $clicks = Click::withReferrer('nonexistent.com')->get();
        $this->assertCount(0, $clicks);
    }

    /** @test */
    public function it_preserves_timestamps()
    {
        $click = Click::factory()->create([
            'link_id' => $this->link->id,
        ]);

        $this->assertNotNull($click->created_at);
        $this->assertNotNull($click->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $click->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $click->updated_at);
    }

    /** @test */
    public function it_handles_case_insensitive_country_codes()
    {
        $usClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'country_code' => 'us',
        ]);

        $filteredClicks = Click::fromCountry('US')->get();
        $this->assertCount(1, $filteredClicks);
        $this->assertEquals($usClick->id, $filteredClicks->first()->id);
    }

    /** @test */
    public function it_handles_case_insensitive_device_types()
    {
        $mobileClick = Click::factory()->create([
            'link_id' => $this->link->id,
            'device_type' => 'Mobile',
        ]);

        $filteredClicks = Click::onDevice('mobile')->get();
        $this->assertCount(1, $filteredClicks);
        $this->assertEquals($mobileClick->id, $filteredClicks->first()->id);
    }
}
