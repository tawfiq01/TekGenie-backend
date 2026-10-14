<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\OptionType;
use App\Models\OptionValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Local/staging only demo catalogue, clearly labelled [Demo]. Covers the
 * edge cases listed in DOC-08 §5: a product without options, a product with
 * two option types, an out-of-stock variant, a low-stock variant, a compare-at
 * price equal to price, and a Bangla product name. Never run in production
 * and never seeds reviews or sales counts (FR-REV-005, BR-17).
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::query()->firstOrCreate(
            ['slug' => 'electronics'],
            ['name' => 'Electronics', 'sort_order' => 1, 'is_active' => true]
        );

        $audio = Category::query()->firstOrCreate(
            ['slug' => 'audio'],
            ['name' => 'Audio', 'parent_id' => $electronics->id, 'sort_order' => 1, 'is_active' => true]
        );

        $accessories = Category::query()->firstOrCreate(
            ['slug' => 'accessories'],
            ['name' => 'Accessories', 'sort_order' => 2, 'is_active' => true]
        );

        $colour = OptionType::query()->firstOrCreate(
            ['name' => 'Colour'],
            ['display_style' => 'swatch', 'sort_order' => 1]
        );

        $size = OptionType::query()->firstOrCreate(
            ['name' => 'Size'],
            ['display_style' => 'chip', 'sort_order' => 2]
        );

        $black = OptionValue::query()->firstOrCreate(
            ['option_type_id' => $colour->id, 'value' => 'Black'],
            ['swatch_hex' => '#1F2328', 'sort_order' => 1]
        );
        $white = OptionValue::query()->firstOrCreate(
            ['option_type_id' => $colour->id, 'value' => 'White'],
            ['swatch_hex' => '#F4F6F8', 'sort_order' => 2]
        );
        $navy = OptionValue::query()->firstOrCreate(
            ['option_type_id' => $colour->id, 'value' => 'Navy'],
            ['swatch_hex' => '#1B3C52', 'sort_order' => 3]
        );

        $sizeM = OptionValue::query()->firstOrCreate(
            ['option_type_id' => $size->id, 'value' => 'M'],
            ['sort_order' => 1]
        );
        $sizeL = OptionValue::query()->firstOrCreate(
            ['option_type_id' => $size->id, 'value' => 'L'],
            ['sort_order' => 2]
        );

        // Product with two option values (Colour), one of them low stock.
        $earbuds = $this->createProduct($audio, '[Demo] Wireless Earbuds Pro', [$colour->id]);
        $this->createVariant($earbuds, 'EBP-BLK', 2790.00, null, 24, [$black->id]);
        $this->createVariant($earbuds, 'EBP-WHT', 2790.00, null, 4, [$white->id], lowStockThreshold: 5);

        // Product without options: exactly one default variant.
        $charger = $this->createProduct($electronics, '[Demo] USB-C Charger 65W', []);
        $this->createVariant($charger, 'CHG-65W', 1950.00, null, 40, [], isDefault: true);

        // Product with two option types combined (Colour x Size).
        $speaker = $this->createProduct($audio, '[Demo] Bluetooth Speaker', [$colour->id, $size->id]);
        $this->createVariant($speaker, 'SPK-BLK-M', 3490.00, 3990.00, 15, [$black->id, $sizeM->id]);
        $this->createVariant($speaker, 'SPK-NVY-L', 3690.00, null, 9, [$navy->id, $sizeL->id]);

        // Out-of-stock variant.
        $powerBank = $this->createProduct($accessories, '[Demo] Power Bank 10000mAh', []);
        $this->createVariant($powerBank, 'PB-10000', 1690.00, null, 0, [], isDefault: true);

        // Bangla product name.
        $phoneCover = $this->createProduct($accessories, '[Demo] ফোন কভার', []);
        $this->createVariant($phoneCover, 'CVR-001', 350.00, null, 60, [], isDefault: true);

        // Compare-at price equal to price: the form (VR-08) refuses this, but
        // the storefront must still hide the strike-through if it ever occurs
        // in the data (TC-CAT-008). Written directly, bypassing validation.
        $compareAtTest = $this->createProduct($accessories, '[Demo] Compare-At Equal Price Test', []);
        $this->createVariant($compareAtTest, 'CMP-EQ-001', 999.00, 999.00, 10, [], isDefault: true);
    }

    /**
     * @param  array<int, int>  $optionTypeIds
     */
    private function createProduct(Category $category, string $name, array $optionTypeIds): Product
    {
        $product = Product::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            [
                'category_id' => $category->id,
                'name' => $name,
                'short_description' => $name,
                'status' => ProductStatus::Active,
                'is_featured' => false,
                'published_at' => now(),
            ]
        );

        if ($optionTypeIds !== []) {
            $product->optionTypes()->syncWithoutDetaching($optionTypeIds);
        }

        return $product;
    }

    /**
     * @param  array<int, int>  $optionValueIds
     */
    private function createVariant(
        Product $product,
        string $sku,
        float $price,
        ?float $compareAtPrice,
        int $stock,
        array $optionValueIds,
        bool $isDefault = false,
        ?int $lowStockThreshold = null,
    ): void {
        $variant = ProductVariant::query()->firstOrCreate(
            ['sku' => $sku],
            [
                'product_id' => $product->id,
                'price' => $price,
                'compare_at_price' => $compareAtPrice,
                'stock' => $stock,
                'low_stock_threshold' => $lowStockThreshold,
                'is_default' => $isDefault,
                'is_active' => true,
                'option_signature' => $optionValueIds === [] ? '' : implode('-', $optionValueIds),
            ]
        );

        if ($optionValueIds !== []) {
            $variant->optionValues()->syncWithoutDetaching($optionValueIds);
        }

        if ($stock > 0 && ! $variant->stockMovements()->where('type', StockMovementType::Opening)->exists()) {
            DB::table('stock_movements')->insert([
                'product_variant_id' => $variant->id,
                'type' => StockMovementType::Opening->value,
                'quantity' => $stock,
                'affects_stock' => true,
                'balance_after' => $stock,
                'reason' => 'Demo seed opening stock',
                'created_at' => now(),
            ]);
        }

        $this->refreshProductAggregates($product);
    }

    private function refreshProductAggregates(Product $product): void
    {
        $variants = $product->variants()->where('is_active', true)->get();

        $product->forceFill([
            'min_price' => $variants->min('price'),
            'max_price' => $variants->max('price'),
            'in_stock' => $variants->sum('stock') > 0,
        ])->save();
    }
}
