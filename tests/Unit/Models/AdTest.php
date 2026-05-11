<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Models;

use App\Models\Ad;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $ad = Ad::factory()->create([
            'target_countries' => ['US', 'UK', 'CA'],
            'is_active' => 1,
            'countdown_seconds' => '10',
        ]);

        $this->assertIsArray($ad->target_countries);
        $this->assertIsBool($ad->is_active);
        $this->assertIsInt($ad->countdown_seconds);
        $this->assertEquals(['US', 'UK', 'CA'], $ad->target_countries);
        $this->assertTrue($ad->is_active);
        $this->assertEquals(10, $ad->countdown_seconds);
    }

    /** @test */
    public function it_clears_cache_on_creation()
    {
        Cache::put('ads_all', 'cached_value');

        Ad::factory()->create();

        $this->assertNull(Cache::get('ads_all'));
    }

    /** @test */
    public function it_clears_cache_on_update()
    {
        $ad = Ad::factory()->create();
        Cache::put('ads_all', 'cached_value');

        $ad->update(['name' => 'Updated Name']);

        $this->assertNull(Cache::get('ads_all'));
    }

    /** @test */
    public function it_clears_cache_on_deletion()
    {
        $ad = Ad::factory()->create();
        Cache::put('ads_all', 'cached_value');

        $ad->delete();

        $this->assertNull(Cache::get('ads_all'));
    }

    /** @test */
    public function it_gets_cached_active_ads()
    {
        $activeAds = Ad::factory()->count(3)->create(['is_active' => true]);
        Ad::factory()->create(['is_active' => false]);

        $cachedAds = Ad::getCachedActive();

        $this->assertCount(3, $cachedAds);
        $this->assertEquals($activeAds->pluck('id'), $cachedAds->pluck('id'));
    }

    /** @test */
    public function it_gets_cached_ad_for_placement()
    {
        $headerAd = Ad::factory()->create([
            'is_active' => true,
            'placement' => 'header',
        ]);

        $footerAd = Ad::factory()->create([
            'is_active' => true,
            'placement' => 'footer',
        ]);

        $headerResult = Ad::getCachedForPlacement('header');
        $footerResult = Ad::getCachedForPlacement('footer');
        $nonExistentResult = Ad::getCachedForPlacement('nonexistent');

        $this->assertEquals($headerAd->id, $headerResult->id);
        $this->assertEquals($footerAd->id, $footerResult->id);
        $this->assertNull($nonExistentResult);
    }

    /** @test */
    public function it_returns_random_ad_for_placement_with_multiple_options()
    {
        $ads = Ad::factory()->count(3)->create([
            'is_active' => true,
            'placement' => 'header',
        ]);

        $result = Ad::getCachedForPlacement('header');

        $this->assertInstanceOf(Ad::class, $result);
        $this->assertEquals('header', $result->placement);
        $this->assertContains($result->id, $ads->pluck('id'));
    }

    /** @test */
    public function it_filters_active_ads()
    {
        $activeAd = Ad::factory()->create(['is_active' => true]);
        $inactiveAd = Ad::factory()->create(['is_active' => false]);

        $activeAds = Ad::active()->get();

        $this->assertCount(1, $activeAds);
        $this->assertEquals($activeAd->id, $activeAds->first()->id);
    }

    /** @test */
    public function it_filters_banner_ads()
    {
        $bannerAd = Ad::factory()->create(['format' => 'banner']);
        $interstitialAd = Ad::factory()->create(['format' => 'interstitial']);

        $bannerAds = Ad::banner()->get();

        $this->assertCount(1, $bannerAds);
        $this->assertEquals($bannerAd->id, $bannerAds->first()->id);
    }

    /** @test */
    public function it_filters_interstitial_ads()
    {
        $bannerAd = Ad::factory()->create(['format' => 'banner']);
        $interstitialAd = Ad::factory()->create(['format' => 'interstitial']);

        $interstitialAds = Ad::interstitial()->get();

        $this->assertCount(1, $interstitialAds);
        $this->assertEquals($interstitialAd->id, $interstitialAds->first()->id);
    }

    /** @test */
    public function it_filters_ads_by_country()
    {
        $globalAd = Ad::factory()->create([
            'target_countries' => null,
        ]);

        $usAd = Ad::factory()->create([
            'target_countries' => ['US', 'CA'],
        ]);

        $ukAd = Ad::factory()->create([
            'target_countries' => ['UK', 'FR'],
        ]);

        $usAds = Ad::forCountry('US')->get();
        $ukAds = Ad::forCountry('UK')->get();
        $frAds = Ad::forCountry('FR')->get();

        $this->assertCount(2, $usAds); // globalAd + usAd
        $this->assertCount(2, $ukAds); // globalAd + ukAd
        $this->assertCount(2, $frAds); // globalAd + ukAd

        $this->assertContains($globalAd->id, $usAds->pluck('id'));
        $this->assertContains($usAd->id, $usAds->pluck('id'));
        $this->assertContains($globalAd->id, $ukAds->pluck('id'));
        $this->assertContains($ukAd->id, $ukAds->pluck('id'));
    }

    /** @test */
    public function it_filters_ads_by_placement()
    {
        $headerAd = Ad::factory()->create(['placement' => 'header']);
        $footerAd = Ad::factory()->create(['placement' => 'footer']);

        $headerAds = Ad::forPlacement('header')->get();
        $footerAds = Ad::forPlacement('footer')->get();

        $this->assertCount(1, $headerAds);
        $this->assertCount(1, $footerAds);
        $this->assertEquals($headerAd->id, $headerAds->first()->id);
        $this->assertEquals($footerAd->id, $footerAds->first()->id);
    }

    /** @test */
    public function it_filters_redirect_ads()
    {
        $redirectAd = Ad::factory()->create(['placement' => 'redirect']);
        $headerAd = Ad::factory()->create(['placement' => 'header']);

        $redirectAds = Ad::redirect()->get();

        $this->assertCount(1, $redirectAds);
        $this->assertEquals($redirectAd->id, $redirectAds->first()->id);
    }

    /** @test */
    public function it_filters_header_ads()
    {
        $headerAd = Ad::factory()->create(['placement' => 'header']);
        $footerAd = Ad::factory()->create(['placement' => 'footer']);

        $headerAds = Ad::header()->get();

        $this->assertCount(1, $headerAds);
        $this->assertEquals($headerAd->id, $headerAds->first()->id);
    }

    /** @test */
    public function it_filters_footer_ads()
    {
        $headerAd = Ad::factory()->create(['placement' => 'header']);
        $footerAd = Ad::factory()->create(['placement' => 'footer']);

        $footerAds = Ad::footer()->get();

        $this->assertCount(1, $footerAds);
        $this->assertEquals($footerAd->id, $footerAds->first()->id);
    }

    /** @test */
    public function it_filters_sidebar_ads()
    {
        $sidebarAd = Ad::factory()->create(['placement' => 'sidebar']);
        $headerAd = Ad::factory()->create(['placement' => 'header']);

        $sidebarAds = Ad::sidebar()->get();

        $this->assertCount(1, $sidebarAds);
        $this->assertEquals($sidebarAd->id, $sidebarAds->first()->id);
    }

    /** @test */
    public function it_filters_left_side_ads()
    {
        $leftSideAd = Ad::factory()->create(['placement' => 'left_side']);
        $rightSideAd = Ad::factory()->create(['placement' => 'right_side']);

        $leftSideAds = Ad::leftSide()->get();

        $this->assertCount(1, $leftSideAds);
        $this->assertEquals($leftSideAd->id, $leftSideAds->first()->id);
    }

    /** @test */
    public function it_filters_right_side_ads()
    {
        $leftSideAd = Ad::factory()->create(['placement' => 'left_side']);
        $rightSideAd = Ad::factory()->create(['placement' => 'right_side']);

        $rightSideAds = Ad::rightSide()->get();

        $this->assertCount(1, $rightSideAds);
        $this->assertEquals($rightSideAd->id, $rightSideAds->first()->id);
    }

    /** @test */
    public function it_filters_before_counter_ads()
    {
        $beforeCounterAd = Ad::factory()->create(['placement' => 'before_counter']);
        $afterCounterAd = Ad::factory()->create(['placement' => 'after_counter']);

        $beforeCounterAds = Ad::beforeCounter()->get();

        $this->assertCount(1, $beforeCounterAds);
        $this->assertEquals($beforeCounterAd->id, $beforeCounterAds->first()->id);
    }

    /** @test */
    public function it_filters_under_counter_ads()
    {
        $underCounterAd = Ad::factory()->create(['placement' => 'under_counter']);
        $aboveCounterAd = Ad::factory()->create(['placement' => 'above_counter']);

        $underCounterAds = Ad::underCounter()->get();

        $this->assertCount(1, $underCounterAds);
        $this->assertEquals($underCounterAd->id, $underCounterAds->first()->id);
    }

    /** @test */
    public function it_filters_above_button_ads()
    {
        $aboveButtonAd = Ad::factory()->create(['placement' => 'above_button']);
        $belowButtonAd = Ad::factory()->create(['placement' => 'below_button']);

        $aboveButtonAds = Ad::aboveButton()->get();

        $this->assertCount(1, $aboveButtonAds);
        $this->assertEquals($aboveButtonAd->id, $aboveButtonAds->first()->id);
    }

    /** @test */
    public function it_filters_under_button_ads()
    {
        $underButtonAd = Ad::factory()->create(['placement' => 'under_button']);
        $overButtonAd = Ad::factory()->create(['placement' => 'over_button']);

        $underButtonAds = Ad::underButton()->get();

        $this->assertCount(1, $underButtonAds);
        $this->assertEquals($underButtonAd->id, $underButtonAds->first()->id);
    }

    /** @test */
    public function it_filters_popup_ads()
    {
        $popupAd = Ad::factory()->create(['placement' => 'popup']);
        $bannerAd = Ad::factory()->create(['placement' => 'banner']);

        $popupAds = Ad::popup()->get();

        $this->assertCount(1, $popupAds);
        $this->assertEquals($popupAd->id, $popupAds->first()->id);
    }

    /** @test */
    public function it_handles_fillable_fields()
    {
        $data = [
            'name' => 'Test Ad',
            'format' => 'banner',
            'placement' => 'header',
            'content' => '<div>Ad content</div>',
            'image_path' => 'ads/image.jpg',
            'target_url' => 'https://example.com',
            'target_countries' => ['US', 'UK'],
            'countdown_seconds' => 10,
            'is_active' => true,
        ];

        $ad = Ad::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $ad->$key);
        }
    }

    /** @test */
    public function it_handles_empty_target_countries()
    {
        $ad = Ad::factory()->create([
            'target_countries' => [],
        ]);

        $this->assertEquals([], $ad->target_countries);
        $this->assertIsArray($ad->target_countries);
    }

    /** @test */
    public function it_combines_multiple_scopes()
    {
        $targetAd = Ad::factory()->create([
            'is_active' => true,
            'format' => 'banner',
            'placement' => 'header',
            'target_countries' => ['US'],
        ]);

        // Create ads that don't match all criteria
        Ad::factory()->create(['is_active' => false, 'format' => 'banner', 'placement' => 'header']);
        Ad::factory()->create(['is_active' => true, 'format' => 'interstitial', 'placement' => 'header']);
        Ad::factory()->create(['is_active' => true, 'format' => 'banner', 'placement' => 'footer']);

        $filteredAds = Ad::active()
            ->banner()
            ->header()
            ->forCountry('US')
            ->get();

        $this->assertCount(1, $filteredAds);
        $this->assertEquals($targetAd->id, $filteredAds->first()->id);
    }

    /** @test */
    public function it_handles_null_target_countries_in_country_filter()
    {
        $globalAd = Ad::factory()->create([
            'target_countries' => null,
        ]);

        $countrySpecificAd = Ad::factory()->create([
            'target_countries' => ['US'],
        ]);

        $allCountryAds = Ad::forCountry('US')->get();

        $this->assertCount(2, $allCountryAds);
        $this->assertContains($globalAd->id, $allCountryAds->pluck('id'));
        $this->assertContains($countrySpecificAd->id, $allCountryAds->pluck('id'));
    }
}
