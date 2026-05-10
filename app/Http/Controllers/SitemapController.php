<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Response;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Sitemap;

class SitemapController extends Controller
{
    /**
     * Generate and return the XML sitemap.
     */
    public function index(): Response
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')
                ->setChangeFrequency('weekly')
                ->setPriority(1.0))
            ->add(Url::create('/login')
                ->setChangeFrequency('monthly')
                ->setPriority(0.5))
            ->add(Url::create('/register')
                ->setChangeFrequency('monthly')
                ->setPriority(0.6));

        // Add public links to sitemap
        $publicLinks = Link::where('visibility', 'public')
            ->select('short_code', 'updated_at')
            ->cursor();

        foreach ($publicLinks as $link) {
            $sitemap->add(Url::create('/' . $link->short_code)
                ->setChangeFrequency('monthly')
                ->setPriority(0.8)
                ->setLastModificationDate($link->updated_at));
        }

        return response($sitemap->render(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Return robots.txt.
     */
    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /links\nDisallow: /admin\nSitemap: " . url('/sitemap.xml');

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
