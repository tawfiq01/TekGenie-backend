<?php

namespace App\Services\Seo;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use SimpleXMLElement;

/**
 * FR-SET-003: sitemap of active products, active categories and published
 * pages. Cached (regenerated on a schedule via console.php and on demand via
 * the admin "Regenerate" action) rather than rebuilt on every request.
 */
class SitemapGenerator
{
    private const CACHE_KEY = 'seo.sitemap.xml';

    public function xml(): string
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(6), fn () => $this->build());
    }

    public function regenerate(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function build(): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $this->addUrl($xml, route('home'));

        foreach (Product::visible()->get() as $product) {
            $this->addUrl($xml, route('product.show', $product), $product->updated_at);
        }

        foreach (Category::where('is_active', true)->get() as $category) {
            $this->addUrl($xml, route('category.show', $category), $category->updated_at);
        }

        foreach (Page::where('is_published', true)->get() as $page) {
            $this->addUrl($xml, route('page.show', $page), $page->updated_at);
        }

        return (string) $xml->asXML();
    }

    private function addUrl(SimpleXMLElement $xml, string $loc, ?Carbon $lastmod = null): void
    {
        $node = $xml->addChild('url');
        $node->addChild('loc', htmlspecialchars($loc));

        if ($lastmod !== null) {
            $node->addChild('lastmod', $lastmod->toAtomString());
        }
    }
}
