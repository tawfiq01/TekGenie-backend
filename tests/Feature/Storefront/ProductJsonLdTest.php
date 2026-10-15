<?php

it('embeds a valid Product schema with BDT price and availability, and no aggregateRating without real reviews (TC-SET-006)', function () {
    $product = makeVisibleProduct(['name' => 'Wireless Earbuds Pro'], ['price' => 1999, 'stock' => 5]);

    $html = $this->get(route('product.show', $product))->assertOk()->getContent();

    $blocks = collect(
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches) ? $matches[1] : []
    )->map(fn ($json) => json_decode($json, true));

    $productSchema = $blocks->firstWhere('@type', 'Product');

    expect($productSchema)->not->toBeNull();
    expect($productSchema['name'])->toBe('Wireless Earbuds Pro');
    expect($productSchema['offers']['priceCurrency'])->toBe('BDT');
    expect($productSchema['offers']['availability'])->toBe('https://schema.org/InStock');
    expect($productSchema)->not->toHaveKey('aggregateRating');

    $breadcrumbSchema = $blocks->firstWhere('@type', 'BreadcrumbList');
    expect($breadcrumbSchema)->not->toBeNull();
    expect($breadcrumbSchema['itemListElement'][0]['name'])->toBe('Home');
});

it('includes aggregateRating only once real reviews exist (TC-SET-006)', function () {
    $product = makeVisibleProduct(['name' => 'Wireless Earbuds Pro']);
    $product->forceFill(['rating_avg' => 4.5, 'rating_count' => 3])->save();

    $html = $this->get(route('product.show', $product))->assertOk()->getContent();

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
    $productSchema = collect($matches[1])->map(fn ($json) => json_decode($json, true))->firstWhere('@type', 'Product');

    expect($productSchema['aggregateRating'])->toBe([
        '@type' => 'AggregateRating',
        'ratingValue' => '4.50',
        'reviewCount' => 3,
    ]);
});

it('marks a sold-out product as OutOfStock in its JSON-LD', function () {
    $product = makeVisibleProduct([], ['stock' => 0]);
    $product->update(['in_stock' => false]);

    $html = $this->get(route('product.show', $product))->assertOk()->getContent();

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
    $productSchema = collect($matches[1])->map(fn ($json) => json_decode($json, true))->firstWhere('@type', 'Product');

    expect($productSchema['offers']['availability'])->toBe('https://schema.org/OutOfStock');
});
