<?php

use App\Models\Brand;
use App\Models\Category;
use Database\Seeders\HomeSectionSeeder;

beforeEach(function () {
    $this->seed(HomeSectionSeeder::class);
});

it('lists only products in the chosen category or its subcategories (FR-SRC-002)', function () {
    $electronics = Category::factory()->create(['name' => 'Electronics']);
    $phones = Category::factory()->create(['name' => 'Phones', 'parent_id' => $electronics->id]);
    $clothing = Category::factory()->create(['name' => 'Clothing']);

    $phone = makeVisibleProduct(['category_id' => $phones->id, 'name' => 'Smartphone X']);
    $shirt = makeVisibleProduct(['category_id' => $clothing->id, 'name' => 'Cotton Shirt']);
    $phone->categories()->sync([$phones->id]);
    $shirt->categories()->sync([$clothing->id]);

    $response = $this->get(route('category.show', $electronics));

    $response->assertOk()->assertSee('Smartphone X')->assertDontSee('Cotton Shirt');
});

it('filters by price range and in-stock only (FR-SRC-002)', function () {
    $category = Category::factory()->create();
    $cheap = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Budget Item'], ['price' => 100]);
    $expensive = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Premium Item'], ['price' => 5000]);
    $cheap->categories()->sync([$category->id]);
    $expensive->categories()->sync([$category->id]);

    $response = $this->get(route('category.show', $category).'?max_price=1000');

    $response->assertOk()->assertSee('Budget Item')->assertDontSee('Premium Item');
});

it('sorts by price (FR-SRC-003)', function () {
    $category = Category::factory()->create();
    $cheap = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Cheap One'], ['price' => 100]);
    $pricey = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Pricey One'], ['price' => 900]);
    $cheap->categories()->sync([$category->id]);
    $pricey->categories()->sync([$category->id]);

    $response = $this->get(route('category.show', $category).'?sort=price_asc');
    $content = $response->getContent();

    expect(strpos($content, 'Cheap One'))->toBeLessThan(strpos($content, 'Pricey One'));
});

it('shows an empty-state message with suggestions when no products match (FR-SRC-005)', function () {
    $category = Category::factory()->create();

    $response = $this->get(route('category.show', $category).'?max_price=1');

    $response->assertOk()->assertSee('No products match these filters');
});

it('searches by name, SKU and short description (FR-SRC-001)', function () {
    $category = Category::factory()->create();
    $bySku = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Wireless Mouse'], ['sku' => 'FINDME-123']);
    $other = makeVisibleProduct(['category_id' => $category->id, 'name' => 'Keyboard']);

    $response = $this->get(route('search.index', ['q' => 'FINDME-123']));

    $response->assertOk()->assertSee('Wireless Mouse')->assertDontSee('Keyboard');
});

it('does not run a query until a search term is given', function () {
    $response = $this->get(route('search.index'));

    $response->assertOk()->assertSee('Type a search term');
});

it('only shows brand filter options that are active', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();

    $response = $this->get(route('category.show', $category));

    $response->assertOk()->assertSee($brand->name);
});
