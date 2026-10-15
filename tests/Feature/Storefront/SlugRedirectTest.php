<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Page;
use App\Models\SlugRedirect;
use Database\Seeders\RoleAndPermissionSeeder;

it('301s a changed product slug to the product\'s current URL (TC-SET-005)', function () {
    $product = makeVisibleProduct(['slug' => 'earbuds-pro']);

    $product->update(['slug' => 'wireless-earbuds-pro']);

    expect(SlugRedirect::where('entity_type', 'product')->where('old_slug', 'earbuds-pro')->exists())->toBeTrue();

    $this->get('/product/earbuds-pro')
        ->assertRedirect(route('product.show', $product->fresh()))
        ->assertStatus(301);

    $this->get(route('product.show', $product->fresh()))->assertOk();
});

it('301s a changed category slug to the category\'s current URL (TC-SET-005)', function () {
    $category = Category::factory()->create(['slug' => 'audio']);

    $category->update(['slug' => 'audio-gear']);

    $this->get('/category/audio')
        ->assertRedirect(route('category.show', $category->fresh()))
        ->assertStatus(301);
});

it('301s a changed page slug to the page\'s current URL (TC-SET-005)', function () {
    $page = Page::factory()->create(['slug' => 'shipping']);

    $page->update(['slug' => 'shipping-policy']);

    $this->get('/page/shipping')
        ->assertRedirect(route('page.show', $page->fresh()))
        ->assertStatus(301);
});

it('404s a slug that never existed and was never redirected', function () {
    $this->get('/product/this-was-never-a-product')->assertNotFound();
});

it('does not break method-not-allowed semantics for other routes', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->assignRole('Super Admin');

    // An admin route that only accepts GET/POST, hit with PATCH, should still 404 —
    // not get swallowed by any site-wide catch-all.
    $this->actingAs($admin, 'admin')->patch('/admin/audit-log/999999')->assertNotFound();
});
