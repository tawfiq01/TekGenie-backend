<?php

use App\Enums\ProductStatus;
use App\Enums\StockMovementType;
use App\Models\Admin;
use App\Models\Category;
use App\Models\OptionType;
use App\Models\Product;
use App\Models\StockMovement;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('Catalog Manager');
});

it('creates a product with no option types and gives it one default variant (TC-CAT-005, BR-03)', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.store'), [
        'name' => 'USB-C Charger 65W',
        'category_id' => $category->id,
        'status' => 'draft',
    ])->assertRedirect();

    $product = Product::where('name', 'USB-C Charger 65W')->firstOrFail();
    expect($product->optionTypes)->toHaveCount(0);

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.variants.store', $product), [
        'sku' => 'CHG-65W',
        'price' => 1950,
        'stock' => 40,
    ])->assertRedirect(route('admin.products.variants.index', $product));

    $product->refresh();
    expect($product->variants)->toHaveCount(1)
        ->and($product->variants->first()->is_default)->toBeTrue()
        ->and((float) $product->min_price)->toBe(1950.0)
        ->and($product->in_stock)->toBeTrue();
});

it('creates a product with a Colour option and two variants, writing opening stock movements (TC-CAT-004)', function () {
    $category = Category::factory()->create();
    $colour = OptionType::create(['name' => 'Colour', 'display_style' => 'swatch']);
    $black = $colour->values()->create(['value' => 'Black']);
    $white = $colour->values()->create(['value' => 'White']);

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.store'), [
        'name' => 'Earbuds Pro',
        'category_id' => $category->id,
        'status' => 'active',
        'option_type_ids' => [$colour->id],
    ])->assertRedirect();

    $product = Product::where('name', 'Earbuds Pro')->firstOrFail();

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.variants.store', $product), [
        'option_value_ids' => [$black->id],
        'sku' => 'EBP-BLK',
        'price' => 2790,
        'stock' => 10,
    ])->assertRedirect();

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.variants.store', $product), [
        'option_value_ids' => [$white->id],
        'sku' => 'EBP-WHT',
        'price' => 2790,
        'stock' => 4,
    ])->assertRedirect();

    $product->refresh();
    expect($product->variants)->toHaveCount(2);

    $blackVariant = $product->variants->firstWhere('sku', 'EBP-BLK');
    $whiteVariant = $product->variants->firstWhere('sku', 'EBP-WHT');

    expect(StockMovement::where('product_variant_id', $blackVariant->id)->where('type', StockMovementType::Opening)->where('quantity', 10)->exists())->toBeTrue()
        ->and(StockMovement::where('product_variant_id', $whiteVariant->id)->where('type', StockMovementType::Opening)->where('quantity', 4)->exists())->toBeTrue()
        ->and((float) $product->min_price)->toBe(2790.0)
        ->and((float) $product->max_price)->toBe(2790.0);
});

it('rejects a duplicate SKU across variants (TC-CAT-006, VR-09)', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.store'), [
        'name' => 'Product A', 'category_id' => $category->id, 'status' => 'draft',
    ]);
    $productA = Product::where('name', 'Product A')->firstOrFail();
    $this->actingAs($this->admin, 'admin')->post(route('admin.products.variants.store', $productA), [
        'sku' => 'DUP-SKU', 'price' => 100, 'stock' => 1,
    ]);

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.store'), [
        'name' => 'Product B', 'category_id' => $category->id, 'status' => 'draft',
    ]);
    $productB = Product::where('name', 'Product B')->firstOrFail();

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.variants.store', $productB), [
        'sku' => 'DUP-SKU', 'price' => 200, 'stock' => 1,
    ])->assertSessionHasErrors('sku');
});

it('rejects a duplicate option combination on the same product (TC-CAT-007)', function () {
    $category = Category::factory()->create();
    $colour = OptionType::create(['name' => 'Colour', 'display_style' => 'swatch']);
    $black = $colour->values()->create(['value' => 'Black']);

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.store'), [
        'name' => 'T-Shirt', 'category_id' => $category->id, 'status' => 'draft', 'option_type_ids' => [$colour->id],
    ]);
    $product = Product::where('name', 'T-Shirt')->firstOrFail();

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.variants.store', $product), [
        'option_value_ids' => [$black->id], 'sku' => 'TS-BLK-1', 'price' => 500, 'stock' => 5,
    ])->assertRedirect();

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.variants.store', $product), [
        'option_value_ids' => [$black->id], 'sku' => 'TS-BLK-2', 'price' => 500, 'stock' => 5,
    ])->assertSessionHasErrors('option_value_ids');
});

it('rejects a compare-at price that is not higher than the price (TC-CAT-008, VR-08, BR-17)', function () {
    $category = Category::factory()->create();
    $this->actingAs($this->admin, 'admin')->post(route('admin.products.store'), [
        'name' => 'Headphones', 'category_id' => $category->id, 'status' => 'draft',
    ]);
    $product = Product::where('name', 'Headphones')->firstOrFail();

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.variants.store', $product), [
        'sku' => 'HP-1', 'price' => 2790, 'compare_at_price' => 2500, 'stock' => 1,
    ])->assertSessionHasErrors('compare_at_price');
});

it('hides products that are not Active or have no active variants (TC-CAT-013, FR-CAT-011)', function () {
    $category = Category::factory()->create();

    $draft = Product::factory()->create(['category_id' => $category->id, 'status' => ProductStatus::Draft]);
    $draft->variants()->create(['sku' => 'D-1', 'price' => 100, 'stock' => 5, 'is_active' => true, 'option_signature' => '']);

    $activeNoStock = Product::factory()->active()->create(['category_id' => $category->id]);
    $activeNoStock->variants()->create(['sku' => 'A-1', 'price' => 100, 'stock' => 0, 'is_active' => false, 'option_signature' => '']);

    $visible = Product::factory()->active()->create(['category_id' => $category->id]);
    $visible->variants()->create(['sku' => 'V-1', 'price' => 100, 'stock' => 5, 'is_active' => true, 'option_signature' => '']);

    $visibleIds = Product::visible()->pluck('id');

    expect($visibleIds)->not->toContain($draft->id)
        ->not->toContain($activeNoStock->id)
        ->toContain($visible->id);
});

it('saves and orders product specifications (TC-CAT-011, FR-CAT-009)', function () {
    $category = Category::factory()->create();
    $this->actingAs($this->admin, 'admin')->post(route('admin.products.store'), [
        'name' => 'Speaker', 'category_id' => $category->id, 'status' => 'draft',
    ]);
    $product = Product::where('name', 'Speaker')->firstOrFail();

    $this->actingAs($this->admin, 'admin')->put(route('admin.products.attributes.update', $product), [
        'attributes' => [
            ['label' => 'Battery', 'value' => '30 h'],
            ['label' => 'Bluetooth', 'value' => '5.3'],
        ],
    ])->assertRedirect();

    expect($product->attributes()->orderBy('sort_order')->pluck('label')->all())->toBe(['Battery', 'Bluetooth']);
});

it('links related products manually (TC-CAT-012, FR-CAT-010)', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $related = Product::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin, 'admin')->put(route('admin.products.related.update', $product), [
        'related_product_ids' => [$related->id],
    ])->assertRedirect();

    expect($product->relatedProducts()->pluck('products.id')->all())->toBe([$related->id]);
});
