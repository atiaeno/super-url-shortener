<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Jobs;

use App\Jobs\FetchOgTagsJob;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchOgTagsJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Link $link;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->link = Link::factory()->create([
            'user_id' => $this->user->id,
            'destination_url' => 'https://example.com',
        ]);
    }

    /**
     * @test
     */
    public function it_fetches_og_tags_successfully()
    {
        $html = '
            <html>
            <head>
                <meta property="og:title" content="Test Title">
                <meta property="og:description" content="Test Description">
                <meta property="og:image" content="https://example.com/image.jpg">
            </head>
            <body>Test content</body>
            </html>
        ';

        Http::fake([
            'https://example.com' => Http::response($html, 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals('Test Title', $this->link->og_title);
        $this->assertEquals('Test Description', $this->link->og_description);
        $this->assertEquals('https://example.com/image.jpg', $this->link->og_image);
    }

    /**
     * @test
     */
    public function it_handles_missing_link_gracefully()
    {
        $job = new FetchOgTagsJob(999);
        $job->handle();

        $this->assertTrue(true);  // Should not throw any errors
    }

    /**
     * @test
     */
    public function it_skips_if_og_tags_already_exist()
    {
        $this->link->update([
            'og_title' => 'Existing Title',
            'og_description' => 'Existing Description',
        ]);

        Http::fake([
            'https://example.com' => Http::response('<html><body>Test</body></html>', 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals('Existing Title', $this->link->og_title);
        $this->assertEquals('Existing Description', $this->link->og_description);

        Http::assertNothingSent();
    }

    /**
     * @test
     */
    public function it_handles_http_failure()
    {
        Http::fake([
            'https://example.com' => Http::response('Not Found', 404),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertNull($this->link->og_title);
        $this->assertNull($this->link->og_description);
        $this->assertNull($this->link->og_image);
    }

    /**
     * @test
     */
    public function it_handles_timeout()
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: Operation timed out');
        });

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertNull($this->link->og_title);
        $this->assertNull($this->link->og_description);
        $this->assertNull($this->link->og_image);
    }

    /**
     * @test
     */
    public function it_extracts_meta_tags_with_different_formats()
    {
        $html = '
            <html>
            <head>
                <meta content="Meta Title" property="og:title">
                <meta name="description" content="Meta Description">
                <meta content="https://example.com/meta-image.jpg" property="og:image">
            </head>
            <body>Test content</body>
            </html>
        ';

        Http::fake([
            'https://example.com' => Http::response($html, 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals('Meta Title', $this->link->og_title);
        $this->assertEquals('Meta Description', $this->link->og_description);
        $this->assertEquals('https://example.com/meta-image.jpg', $this->link->og_image);
    }

    /**
     * @test
     */
    public function it_falls_back_to_title_tag()
    {
        $html = '
            <html>
            <head>
                <title>Page Title</title>
            </head>
            <body>Test content</body>
            </html>
        ';

        Http::fake([
            'https://example.com' => Http::response($html, 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals('Page Title', $this->link->og_title);
        $this->assertNull($this->link->og_description);
        $this->assertNull($this->link->og_image);
    }

    /**
     * @test
     */
    public function it_truncates_long_content()
    {
        $longTitle = str_repeat('A', 300);
        $longDescription = str_repeat('B', 600);

        $html = '
            <html>
            <head>
                <meta property="og:title" content="' . $longTitle . '">
                <meta property="og:description" content="' . $longDescription . '">
            </head>
            <body>Test content</body>
            </html>
        ';

        Http::fake([
            'https://example.com' => Http::response($html, 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals(255, strlen($this->link->og_title));
        $this->assertEquals(500, strlen($this->link->og_description));
    }

    /**
     * @test
     */
    public function it_strips_html_tags_from_content()
    {
        $html = '
            <html>
            <head>
                <meta property="og:title" content="Title with tags">
                <meta property="og:description" content="Description with script here">
            </head>
            <body>Test content</body>
            </html>
        ';

        Http::fake([
            'https://example.com' => Http::response($html, 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals('Title with tags', $this->link->og_title);
        $this->assertEquals('Description with script here', $this->link->og_description);
    }

    /**
     * @test
     */
    public function it_handles_twitter_image_fallback()
    {
        $html = '
            <html>
            <head>
                <meta property="og:title" content="Test Title">
                <meta name="twitter:image" content="https://example.com/twitter-image.jpg">
            </head>
            <body>Test content</body>
            </html>
        ';

        Http::fake([
            'https://example.com' => Http::response($html, 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals('Test Title', $this->link->og_title);
        $this->assertEquals('https://example.com/twitter-image.jpg', $this->link->og_image);
    }

    /**
     * @test
     */
    public function it_only_updates_empty_fields()
    {
        $this->link->update([
            'og_title' => 'Existing Title',
        ]);

        $html = '
            <html>
            <head>
                <meta property="og:title" content="New Title">
                <meta property="og:description" content="New Description">
                <meta property="og:image" content="https://example.com/new-image.jpg">
            </head>
            <body>Test content</body>
            </html>
        ';

        Http::fake([
            'https://example.com' => Http::response($html, 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals('Existing Title', $this->link->og_title);  // Should not change
        $this->assertEquals('New Description', $this->link->og_description);
        $this->assertEquals('https://example.com/new-image.jpg', $this->link->og_image);
    }

    /**
     * @test
     */
    public function it_handles_malformed_html()
    {
        $html = '<html><head><meta property="og:title" content="Test Title"</head><body>Test</body></html>';

        Http::fake([
            'https://example.com' => Http::response($html, 200),
        ]);

        $job = new FetchOgTagsJob($this->link->id);
        $job->handle();

        $this->link->refresh();
        $this->assertEquals('Test Title', $this->link->og_title);
    }
}
