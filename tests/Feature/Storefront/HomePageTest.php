<?php

use App\Enums\HomeSectionType;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use Database\Seeders\HomeSectionSeeder;

beforeEach(function () {
    $this->seed(HomeSectionSeeder::class);
});

it('renders only active home sections, in order (FR-STF-001)', function () {
    Category::factory()->create();
    HomeSection::where('type', HomeSectionType::BestSellers->value)->first()->update(['is_active' => false]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Shop by category')
        ->assertDontSee('Best sellers');
});

it('shows a scheduled hero banner but not an expired one', function () {
    Banner::factory()->create([
        'placement' => 'hero', 'is_active' => true, 'alt_text' => 'Current promo',
        'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(),
    ]);
    Banner::factory()->create([
        'placement' => 'hero', 'is_active' => true, 'alt_text' => 'Expired promo',
        'starts_at' => now()->subMonth(), 'ends_at' => now()->subWeek(),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk()->assertSee('Current promo')->assertDontSee('Expired promo');
});

it('lists featured products on the home page', function () {
    $category = Category::factory()->create();
    $featured = Product::factory()->active()->create(['category_id' => $category->id, 'is_featured' => true, 'name' => 'Featured Widget']);
    $featured->variants()->create(['sku' => 'FW-1', 'price' => 100, 'stock' => 5, 'is_active' => true, 'option_signature' => '']);

    $response = $this->get(route('home'));

    $response->assertOk()->assertSee('Featured Widget');
});
