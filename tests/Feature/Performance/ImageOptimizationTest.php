<?php

use Illuminate\Support\Facades\DB;

it('lazy-loads and gives a responsive srcset to product card images on the listing page (NFR-PERF-03)', function () {
    $product = makeVisibleProduct(['name' => 'Lazy Card Widget']);
    $product->images()->create(['path' => 'products/lazy-card-widget', 'is_main' => true, 'sort_order' => 0]);

    $html = $this->get(route('shop.index'))->assertOk()->getContent();

    expect($html)->toContain('loading="lazy"')
        ->toContain('srcset="')
        ->toContain('width="400" height="400"');
});

it('eager-loads the main product image with high fetch priority on the product detail page (NFR-PERF-01/03)', function () {
    $product = makeVisibleProduct(['name' => 'Priority Widget']);
    $product->images()->create(['path' => 'products/priority-widget', 'is_main' => true, 'sort_order' => 0]);

    $html = $this->get(route('product.show', $product))->assertOk()->getContent();

    expect($html)->toContain('fetchpriority="high"')
        ->toContain(':srcset="activeSrcset"');
});

it('does not run an extra query per product for category/brand on the product detail page (NFR-PERF-04)', function () {
    $product = makeVisibleProduct(['name' => 'N Plus One Widget']);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route('product.show', $product))->assertOk();

    $categoryQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], '`categories`'))->count();
    $brandQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], '`brands`'))->count();

    expect($categoryQueries)->toBeLessThanOrEqual(1)
        ->and($brandQueries)->toBeLessThanOrEqual(1);
});
