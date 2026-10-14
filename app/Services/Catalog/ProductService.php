<?php

namespace App\Services\Catalog;

use App\Enums\ProductStatus;
use App\Models\Admin;
use App\Models\Product;
use App\Services\Audit\AuditLogger;
use App\Support\RichTextSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Product management (FR-CAT-003). Option types are chosen once at creation
 * — changing them afterwards would orphan existing variants' option
 * combinations, so the update form only edits metadata.
 */
class ProductService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $categoryIds
     * @param  array<int, int>  $optionTypeIds
     */
    public function create(array $data, array $categoryIds, array $optionTypeIds, Admin $actor): Product
    {
        $product = DB::transaction(function () use ($data, $categoryIds, $optionTypeIds, $actor): Product {
            $product = Product::create([
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name'], null),
                'short_description' => $data['short_description'] ?? null,
                'description' => RichTextSanitizer::sanitize($data['description'] ?? null),
                'status' => $data['status'],
                'is_featured' => $data['is_featured'] ?? false,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'published_at' => $data['status'] === ProductStatus::Active->value ? now() : null,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $product->categories()->sync(array_unique([$data['category_id'], ...$categoryIds]));

            if ($optionTypeIds !== []) {
                $pivotData = [];
                foreach (array_values($optionTypeIds) as $position => $optionTypeId) {
                    $pivotData[$optionTypeId] = ['sort_order' => $position];
                }
                $product->optionTypes()->sync($pivotData);
            }

            return $product;
        });

        $this->audit->log('admin.product.created', $product, ['name' => $product->name, 'status' => $product->status->value]);

        return $product;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $categoryIds
     */
    public function update(Product $product, array $data, array $categoryIds, Admin $actor): Product
    {
        $before = $product->only(['name', 'slug', 'status', 'is_featured']);
        $wasActive = $product->status === ProductStatus::Active;
        $willBeActive = $data['status'] === ProductStatus::Active->value;

        $product->forceFill([
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name'], $product),
            'short_description' => $data['short_description'] ?? null,
            'description' => RichTextSanitizer::sanitize($data['description'] ?? null),
            'status' => $data['status'],
            'is_featured' => $data['is_featured'] ?? false,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'updated_by' => $actor->id,
        ]);

        if (! $wasActive && $willBeActive && $product->published_at === null) {
            $product->published_at = now();
        }

        $product->save();
        $product->categories()->sync(array_unique([$data['category_id'], ...$categoryIds]));

        $this->audit->log('admin.product.updated', $product, [
            'before' => $before,
            'after' => $product->only(['name', 'slug', 'status', 'is_featured']),
        ]);

        return $product;
    }

    /** @param  array<int, array{label: string, value: string, sort_order?: int}>  $attributes */
    public function syncAttributes(Product $product, array $attributes): void
    {
        $product->attributes()->delete();

        foreach (array_values($attributes) as $position => $attribute) {
            $product->attributes()->create([
                'label' => $attribute['label'],
                'value' => $attribute['value'],
                'sort_order' => $attribute['sort_order'] ?? $position,
            ]);
        }

        $this->audit->log('admin.product.attributes-updated', $product, ['count' => count($attributes)]);
    }

    /** @param  array<int, int>  $relatedProductIds */
    public function syncRelated(Product $product, array $relatedProductIds): void
    {
        $relatedProductIds = array_values(array_diff(array_unique($relatedProductIds), [$product->id]));

        $pivotData = [];
        foreach ($relatedProductIds as $position => $relatedId) {
            $pivotData[$relatedId] = ['sort_order' => $position];
        }

        $product->relatedProducts()->sync($pivotData);

        $this->audit->log('admin.product.related-updated', $product, ['related_ids' => $relatedProductIds]);
    }

    private function resolveSlug(?string $slug, string $name, ?Product $self): string
    {
        $base = Str::slug($slug ?: $name);
        $candidate = $base;
        $suffix = 2;

        while (
            Product::query()
                ->where('slug', $candidate)
                ->when($self, fn ($q) => $q->where('id', '!=', $self->id))
                ->exists()
        ) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
