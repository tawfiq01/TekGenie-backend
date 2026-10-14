<?php

namespace App\Services\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared query builder for category, "shop all" and search listings
 * (FR-SRC-002/003/004). Draft/Archived/out-of-stock-variant products never
 * appear — Product::visible() enforces FR-CAT-011 at the query root.
 */
class ProductListingService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(array $filters, ?string $searchTerm = null): LengthAwarePaginator
    {
        $query = Product::visible()->with(['category', 'brand', 'images']);

        if ($searchTerm !== null && $searchTerm !== '') {
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->whereFullText(['name', 'short_description'], $searchTerm)
                    ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', "%{$searchTerm}%"));
            });
        }

        if (! empty($filters['category_id'])) {
            $categoryIds = $this->categoryAndChildIds((int) $filters['category_id']);
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('max_price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('min_price', '<=', $filters['max_price']);
        }

        if (! empty($filters['in_stock'])) {
            $query->where('in_stock', true);
        }

        if (! empty($filters['option_value_ids'])) {
            $query->whereHas(
                'variants',
                fn ($v) => $v->where('is_active', true)
                    ->whereHas('optionValues', fn ($ov) => $ov->whereIn('option_values.id', (array) $filters['option_value_ids']))
            );
        }

        $this->applySort($query, $filters['sort'] ?? null, $searchTerm);

        return $query->paginate(24)->withQueryString();
    }

    /** @return array<int, int> */
    private function categoryAndChildIds(int $categoryId): array
    {
        $category = Category::query()->with('children')->find($categoryId);

        if (! $category) {
            return [$categoryId];
        }

        return [$category->id, ...$category->children->pluck('id')];
    }

    /** @param  Builder<Product>  $query */
    private function applySort(Builder $query, ?string $sort, ?string $searchTerm): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('min_price'),
            'price_desc' => $query->orderByDesc('max_price'),
            'best_selling' => $query->orderByDesc('sold_count_30d'),
            'relevance' => $searchTerm
                ? $query->orderByRaw('MATCH(name, short_description) AGAINST (?) DESC', [$searchTerm])
                : $query->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at'),
        };
    }
}
