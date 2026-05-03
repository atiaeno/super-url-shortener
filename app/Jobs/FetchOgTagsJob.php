<?php
// © Atia Hegazy — atiaeno.com

namespace App\Jobs;

use App\Models\Link;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchOgTagsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 15;

    public function __construct(private int $linkId) {}

    public function handle(): void
    {
        $link = Link::find($this->linkId);

        if (! $link || ($link->og_title && $link->og_description)) {
            return;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ShortlinkBot/1.0)'])
                ->get($link->destination_url);

            if (! $response->successful()) {
                return;
            }

            $html = $response->body();

            $ogTitle       = $this->extractMeta($html, 'og:title')
                          ?? $this->extractTitle($html);
            $ogDescription = $this->extractMeta($html, 'og:description')
                          ?? $this->extractMeta($html, 'description');

            $updates = [];

            if ($ogTitle && empty($link->og_title)) {
                $updates['og_title'] = mb_substr(strip_tags($ogTitle), 0, 255);
            }

            if ($ogDescription && empty($link->og_description)) {
                $updates['og_description'] = mb_substr(strip_tags($ogDescription), 0, 500);
            }

            if (! empty($updates)) {
                $link->update($updates);
            }
        } catch (\Throwable) {
            // Silently fail — OG scraping is best-effort
        }
    }

    private function extractMeta(string $html, string $property): ?string
    {
        // og:* properties
        if (preg_match('/<meta[^>]+property=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return $m[1];
        }

        // name= attributes (for description)
        if (preg_match('/<meta[^>]+name=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return $m[1];
        }

        // Reverse attribute order
        if (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property|name)=["\']' . preg_quote($property, '/') . '["\'][^>]*>/i', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    private function extractTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
            return trim($m[1]);
        }

        return null;
    }
}
