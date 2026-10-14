<?php

use App\Models\Product;
use App\Services\Catalog\VariantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Shared Test Helpers
|--------------------------------------------------------------------------
*/

/**
 * A storefront-visible Product (Active status, one active variant) — the
 * minimum fixture every listing/search/detail test needs (Product::visible()).
 *
 * @param  array<string, mixed>  $attributes
 * @param  array<string, mixed>  $variantAttributes
 */
function makeVisibleProduct(array $attributes = [], array $variantAttributes = []): Product
{
    $product = Product::factory()->active()->create($attributes);
    $product->variants()->create(array_merge([
        'sku' => 'SKU-'.$product->id, 'price' => 500, 'stock' => 10, 'is_active' => true, 'option_signature' => '',
    ], $variantAttributes));

    // Production code always creates variants through VariantService, which
    // recalculates these — do the same here so min_price/max_price/in_stock
    // aren't left null and listing filters behave as they would for real.
    app(VariantService::class)->recalcProductAggregates($product);

    return $product->refresh();
}
