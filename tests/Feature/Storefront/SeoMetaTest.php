<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Setting;

beforeEach(function () {
    Setting::updateOrCreate(['group' => 'seo', 'key' => 'default_meta_title_pattern'], ['value' => '{title} | Test Store']);
    Setting::updateOrCreate(['group' => 'seo', 'key' => 'default_meta_description'], ['value' => 'Default store description.']);
});

it('uses the default title pattern and description when a product has no override (TC-SET-002)', function () {
    $product = makeVisibleProduct(['name' => 'Wireless Earbuds Pro', 'short_description' => 'Great sound on the go.']);

    $response = $this->get(route('product.show', $product));

    $response->assertOk()
        ->assertSee('<title>Wireless Earbuds Pro | Test Store</title>', false)
        ->assertSee('<meta name="description" content="Great sound on the go.">', false)
        ->assertSee('<link rel="canonical" href="'.route('product.show', $product).'">', false)
        ->assertSee('<meta property="og:title" content="Wireless Earbuds Pro | Test Store">', false)
        ->assertSee('<meta property="og:type" content="product">', false);
});

it('uses the per-product meta title/description verbatim when overridden (TC-SET-002)', function () {
    $product = makeVisibleProduct([
        'name' => 'Wireless Earbuds Pro',
        'meta_title' => 'Buy Earbuds Pro — Free Delivery in Dhaka',
        'meta_description' => 'A custom override description.',
    ]);

    $this->get(route('product.show', $product))
        ->assertOk()
        ->assertSee('<title>Buy Earbuds Pro — Free Delivery in Dhaka</title>', false)
        ->assertSee('<meta name="description" content="A custom override description.">', false);
});

it('resolves title/description/canonical for a category page (TC-SET-002)', function () {
    $category = Category::factory()->create(['name' => 'Audio', 'description' => 'Headphones and speakers.']);

    $this->get(route('category.show', $category))
        ->assertOk()
        ->assertSee('<title>Audio | Test Store</title>', false)
        ->assertSee('<meta name="description" content="Headphones and speakers.">', false)
        ->assertSee('<link rel="canonical" href="'.route('category.show', $category).'">', false);
});

it('resolves title/description/canonical for a static page (TC-SET-002)', function () {
    $page = Page::factory()->create(['title' => 'Shipping Policy', 'meta_description' => 'How we ship orders.']);

    $this->get(route('page.show', $page))
        ->assertOk()
        ->assertSee('<title>Shipping Policy | Test Store</title>', false)
        ->assertSee('<meta name="description" content="How we ship orders.">', false);
});

it('falls back to the default pattern and description on generic pages with no entity override', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<meta name="description" content="Default store description.">', false);
});
