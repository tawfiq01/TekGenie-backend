<?php

use App\Enums\ProductStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Services\Seo\SitemapGenerator;
use Database\Seeders\RoleAndPermissionSeeder;

it('lists only active products, active categories and published pages, as valid XML (TC-SET-003)', function () {
    $active = makeVisibleProduct(['name' => 'Active Product']);
    $draft = Product::factory()->create(['name' => 'Draft Product', 'status' => ProductStatus::Draft]);
    $archived = Product::factory()->create(['name' => 'Archived Product', 'status' => ProductStatus::Archived]);

    $activeCategory = Category::factory()->create(['is_active' => true]);
    $inactiveCategory = Category::factory()->create(['is_active' => false]);

    $publishedPage = Page::factory()->create(['is_published' => true]);
    $unpublishedPage = Page::factory()->create(['is_published' => false]);

    app(SitemapGenerator::class)->regenerate();
    $response = $this->get(route('sitemap'));

    $response->assertOk();
    $xml = simplexml_load_string($response->getContent());
    expect($xml)->not->toBeFalse();

    $locs = sitemapLocs($xml);

    expect($locs)->toContain(route('product.show', $active))
        ->toContain(route('category.show', $activeCategory))
        ->toContain(route('page.show', $publishedPage))
        ->not->toContain(route('product.show', $draft))
        ->not->toContain(route('product.show', $archived))
        ->not->toContain(route('category.show', $inactiveCategory))
        ->not->toContain(route('page.show', $unpublishedPage));
});

it('regenerates on demand via the admin action', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    makeVisibleProduct(['name' => 'Before Regenerate']);
    $this->get(route('sitemap'))->assertOk();

    $new = makeVisibleProduct(['name' => 'After Regenerate']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.settings.seo.regenerate-sitemap'))
        ->assertRedirect(route('admin.settings.seo'));

    $xml = simplexml_load_string($this->get(route('sitemap'))->getContent());
    $locs = sitemapLocs($xml);

    expect($locs)->toContain(route('product.show', $new));
});
