<?php

namespace App\Services\Catalog;

use App\Enums\StockMovementType;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Variant matrix management (FR-CAT-005; BR-03). A product with no option
 * types gets exactly one variant (option_signature ''); a product with
 * option types gets one variant per distinct combination.
 */
class VariantService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $optionValueIds
     */
    public function create(Product $product, array $data, array $optionValueIds, Admin $actor): ProductVariant
    {
        $signature = $this->buildSignature($optionValueIds);

        if ($product->variants()->where('option_signature', $signature)->exists()) {
            throw ValidationException::withMessages([
                'option_value_ids' => 'A variant with this exact option combination already exists.',
            ]);
        }

        $stock = (int) ($data['stock'] ?? 0);

        $variant = DB::transaction(function () use ($product, $data, $optionValueIds, $signature, $stock, $actor) {
            $variant = $product->variants()->create([
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'price' => $data['price'],
                'compare_at_price' => $data['compare_at_price'] ?? null,
                'stock' => $stock,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
                'weight_grams' => $data['weight_grams'] ?? null,
                'is_default' => $optionValueIds === [],
                'is_active' => $data['is_active'] ?? true,
                'option_signature' => $signature,
            ]);

            if ($optionValueIds !== []) {
                $variant->optionValues()->sync($optionValueIds);
            }

            if ($stock > 0) {
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => StockMovementType::Opening,
                    'quantity' => $stock,
                    'affects_stock' => true,
                    'balance_after' => $stock,
                    'reason' => 'Initial stock on variant creation',
                    'admin_id' => $actor->id,
                ]);
            }

            return $variant;
        });

        $this->recalcProductAggregates($product);

        $this->audit->log('admin.product-variant.created', $variant, ['sku' => $variant->sku, 'stock' => $stock]);

        return $variant;
    }

    /** @param  array<string, mixed>  $data */
    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        $trackedFields = ['sku', 'barcode', 'price', 'compare_at_price', 'low_stock_threshold', 'weight_grams', 'is_active'];
        $before = $variant->only($trackedFields);

        $variant->forceFill([
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
            'weight_grams' => $data['weight_grams'] ?? null,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $variant->is_active,
        ])->save();

        $this->recalcProductAggregates($variant->product);

        $this->audit->log('admin.product-variant.updated', $variant, [
            'before' => $before,
            'after' => $variant->only($trackedFields),
        ]);

        return $variant;
    }

    public function recalcProductAggregates(Product $product): void
    {
        $product->forceFill([
            'min_price' => $product->variants()->where('is_active', true)->min('price'),
            'max_price' => $product->variants()->where('is_active', true)->max('price'),
            'in_stock' => $product->variants()->where('is_active', true)->where('stock', '>', 0)->exists(),
        ])->saveQuietly();
    }

    /** @param  array<int, int>  $optionValueIds */
    private function buildSignature(array $optionValueIds): string
    {
        sort($optionValueIds);

        return implode('-', $optionValueIds);
    }
}
