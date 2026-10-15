<?php

namespace App\Services\Seo;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * FR-SET-002: title/description/canonical/Open Graph resolution. A
 * per-entity meta_title/meta_description override, where present, is used
 * verbatim; otherwise the global default pattern/description apply.
 */
class SeoMetaService
{
    public function forProduct(Product $product): SeoMeta
    {
        $mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();

        return new SeoMeta(
            title: $this->resolveTitle($product->meta_title, $product->name),
            description: $this->resolveDescription($product->meta_description, $product->short_description),
            canonical: route('product.show', $product),
            ogImage: $mainImage?->url('detail'),
            ogType: 'product',
        );
    }

    public function forCategory(Category $category): SeoMeta
    {
        return new SeoMeta(
            title: $this->resolveTitle($category->meta_title, $category->name),
            description: $this->resolveDescription($category->meta_description, $category->description),
            canonical: route('category.show', $category),
            ogImage: $category->image_path ? Storage::disk('public')->url($category->image_path) : null,
        );
    }

    public function forPage(Page $page): SeoMeta
    {
        return new SeoMeta(
            title: $this->resolveTitle($page->meta_title, $page->title),
            description: $this->resolveDescription($page->meta_description, null),
            canonical: route('page.show', $page),
        );
    }

    public function forGeneric(string $title, ?string $description = null): SeoMeta
    {
        return new SeoMeta(
            title: $this->resolveTitle(null, $title),
            description: $this->resolveDescription(null, $description),
            canonical: URL::current(),
        );
    }

    /**
     * FR-SET-006: Product schema — BDT price, availability, and a real aggregate rating only when reviews exist.
     *
     * @return array<string, mixed>
     */
    public function productJsonLd(Product $product): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->short_description,
            'image' => $product->images->map(fn ($image) => $image->url('detail'))->values()->all(),
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => (string) ($product->min_price ?? '0.00'),
                'availability' => $product->in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => route('product.show', $product),
            ],
        ];

        if ($product->rating_count > 0 && $product->rating_avg !== null) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $product->rating_avg,
                'reviewCount' => $product->rating_count,
            ];
        }

        return $data;
    }

    /**
     * FR-SET-006: breadcrumb trail JSON-LD. Each item is ['name' => string, 'url' => string|null] —
     * the final (current-page) item conventionally has no url.
     *
     * @param  array<int, array{name: string, url?: string|null}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbJsonLd(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index) => array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ]))->all(),
        ];
    }

    private function resolveTitle(?string $override, string $fallbackTitle): string
    {
        if ($override) {
            return $override;
        }

        $pattern = (string) Setting::get('seo', 'default_meta_title_pattern', '{title} | '.config('app.name'));

        return str_replace('{title}', $fallbackTitle, $pattern);
    }

    private function resolveDescription(?string $override, ?string $fallback): string
    {
        if ($override) {
            return $override;
        }

        if ($fallback) {
            return Str::limit($fallback, 255, '');
        }

        return (string) Setting::get('seo', 'default_meta_description', '');
    }
}
