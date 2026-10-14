<?php

use App\Enums\StockMovementType;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Inventory\InventoryService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('adjusts stock, writes a movement, and recalculates the product aggregate (TC-INV-002, TC-INV-006)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);
    $variant = $product->variants()->create(['sku' => 'INV-1', 'price' => 500, 'stock' => 2, 'is_active' => true, 'option_signature' => '']);

    $this->actingAs($admin, 'admin')->post(route('admin.inventory.adjust', $variant), [
        'quantity' => -3,
        'reason' => 'Damaged in warehouse',
    ])->assertSessionHasErrors('quantity');

    expect($variant->fresh()->stock)->toBe(2);

    $this->actingAs($admin, 'admin')->post(route('admin.inventory.adjust', $variant), [
        'quantity' => 5,
        'reason' => 'Restock from supplier',
    ])->assertRedirect(route('admin.inventory.history', $variant));

    $variant->refresh();
    $product->refresh();

    expect($variant->stock)->toBe(7)
        ->and($product->in_stock)->toBeTrue();

    $movement = StockMovement::where('product_variant_id', $variant->id)->where('type', StockMovementType::Adjustment)->first();
    expect($movement)->not->toBeNull()
        ->and($movement->quantity)->toBe(5)
        ->and($movement->balance_after)->toBe(7)
        ->and($movement->reason)->toBe('Restock from supplier')
        ->and($movement->admin_id)->toBe($admin->id);
});

it('refuses an adjustment that would take stock below zero (TC-INV-001, TC-INV-007, BR-04)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);
    $variant = $product->variants()->create(['sku' => 'INV-2', 'price' => 500, 'stock' => 2, 'is_active' => true, 'option_signature' => '']);

    expect(fn () => app(InventoryService::class)->adjust($variant, -5, 'damaged', $admin))
        ->toThrow(ValidationException::class);

    expect($variant->fresh()->stock)->toBe(2);
    expect(StockMovement::where('product_variant_id', $variant->id)->exists())->toBeFalse();
});

it('requires permission to adjust stock (TC-INV-006, BR-18)', function () {
    $contentEditor = Admin::factory()->create();
    $contentEditor->assignRole('Content Editor');

    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);
    $variant = $product->variants()->create(['sku' => 'INV-3', 'price' => 500, 'stock' => 2, 'is_active' => true, 'option_signature' => '']);

    $this->actingAs($contentEditor, 'admin')->get(route('admin.inventory.index'))->assertForbidden();
    $this->actingAs($contentEditor, 'admin')->post(route('admin.inventory.adjust', $variant), [
        'quantity' => 1, 'reason' => 'x',
    ])->assertForbidden();
});

it('filters the inventory list by low stock and out of stock (TC-INV-008)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');
    $category = Category::factory()->create();

    $out = Product::factory()->create(['category_id' => $category->id])->variants()->create([
        'sku' => 'OUT-1', 'price' => 100, 'stock' => 0, 'is_active' => true, 'option_signature' => '',
    ]);
    $low = Product::factory()->create(['category_id' => $category->id])->variants()->create([
        'sku' => 'LOW-1', 'price' => 100, 'stock' => 3, 'low_stock_threshold' => 5, 'is_active' => true, 'option_signature' => '',
    ]);
    $healthy = Product::factory()->create(['category_id' => $category->id])->variants()->create([
        'sku' => 'OK-1', 'price' => 100, 'stock' => 50, 'is_active' => true, 'option_signature' => '',
    ]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.inventory.index', ['filter' => 'out_of_stock']));
    $response->assertOk()->assertSee('OUT-1')->assertDontSee('LOW-1')->assertDontSee('OK-1');

    $response = $this->actingAs($admin, 'admin')->get(route('admin.inventory.index', ['filter' => 'low_stock']));
    $response->assertOk()->assertSee('LOW-1')->assertDontSee('OUT-1')->assertDontSee('OK-1');
});
