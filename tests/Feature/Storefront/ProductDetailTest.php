<?php

use App\Models\Category;
use App\Models\OptionType;
use App\Models\Product;

it('shows an active product with a visible variant (FR-STF-005)', function () {
    $category = Category::factory()->create();
    $product = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Bluetooth Speaker'], ['price' => 1500]);

    $response = $this->get(route('product.show', $product));

    $response->assertOk()->assertSee('Bluetooth Speaker')->assertSee('1500');
});

it('returns 404 for a draft product (FR-CAT-011)', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $product->variants()->create(['sku' => 'DRAFT-1', 'price' => 100, 'stock' => 5, 'is_active' => true, 'option_signature' => '']);

    $this->get(route('product.show', $product))->assertNotFound();
});

it('returns 404 for an active product with no active variants (FR-CAT-011)', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->active()->create(['category_id' => $category->id]);
    $product->variants()->create(['sku' => 'INACTIVE-1', 'price' => 100, 'stock' => 5, 'is_active' => false, 'option_signature' => '']);

    $this->get(route('product.show', $product))->assertNotFound();
});

it('embeds every variant combination for the client-side picker (FR-STF-006)', function () {
    $category = Category::factory()->create();
    $colour = OptionType::create(['name' => 'Colour', 'display_style' => 'swatch']);
    $black = $colour->values()->create(['value' => 'Black']);
    $white = $colour->values()->create(['value' => 'White']);

    $product = Product::factory()->active()->create(['category_id' => $category->id]);
    $product->optionTypes()->sync([$colour->id => ['sort_order' => 0]]);

    $blackVariant = $product->variants()->create(['sku' => 'SPK-BLK', 'price' => 1200, 'stock' => 3, 'is_active' => true, 'option_signature' => (string) $black->id]);
    $blackVariant->optionValues()->sync([$black->id]);
    $whiteVariant = $product->variants()->create(['sku' => 'SPK-WHT', 'price' => 1200, 'stock' => 0, 'is_active' => true, 'option_signature' => (string) $white->id]);
    $whiteVariant->optionValues()->sync([$white->id]);

    $response = $this->get(route('product.show', $product));

    $response->assertOk()
        ->assertSee('SPK-BLK')
        ->assertSee('SPK-WHT')
        ->assertSee('Black')
        ->assertSee('White');
});

it('falls back to same-category products when none are manually related (FR-CAT-010)', function () {
    $category = Category::factory()->create();
    $product = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Main Product']);
    $sibling = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Sibling Product']);

    $response = $this->get(route('product.show', $product));

    $response->assertOk()->assertSee('Sibling Product');
});
