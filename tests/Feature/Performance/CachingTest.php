<?php

use App\Enums\HomeSectionType;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Setting;
use Database\Seeders\HomeSectionSeeder;
use Illuminate\Support\Facades\DB;

/** @return int number of queries touching $table since the log was last flushed */
function queriesAgainst(string $table): int
{
    return collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], "`{$table}`"))->count();
}

it('reads the settings table once no matter how many Setting::get() calls follow (NFR-PERF-05)', function () {
    Setting::put('store', 'name', 'Cache Test Store');

    DB::flushQueryLog();
    DB::enableQueryLog();

    expect(Setting::get('store', 'name'))->toBe('Cache Test Store');
    Setting::get('store', 'name');
    Setting::get('checkout', 'cod_max_order_value', '0');

    expect(queriesAgainst('settings'))->toBe(1);
});

it('invalidates the settings cache immediately on write (TC-SET-001)', function () {
    Setting::put('store', 'name', 'Before');
    expect(Setting::get('store', 'name'))->toBe('Before');

    Setting::put('store', 'name', 'After');
    expect(Setting::get('store', 'name'))->toBe('After');
});

it('caches the active top-level category list and invalidates it when a category changes (NFR-PERF-05)', function () {
    $category = Category::factory()->create(['name' => 'Electronics', 'is_active' => true]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    Category::activeTopLevel();
    Category::activeTopLevel();

    expect(queriesAgainst('categories'))->toBe(1);

    $category->update(['name' => 'Electronics & Gadgets']);

    expect(Category::activeTopLevel()->firstWhere('id', $category->id)->name)->toBe('Electronics & Gadgets');
});

it('excludes a category deactivated after the cache was primed', function () {
    $category = Category::factory()->create(['is_active' => true]);
    Category::activeTopLevel();

    $category->update(['is_active' => false]);

    expect(Category::activeTopLevel()->pluck('id'))->not->toContain($category->id);
});

it('caches the homepage composition and invalidates it when a banner changes (NFR-PERF-05)', function () {
    $this->seed(HomeSectionSeeder::class);
    Banner::factory()->create(['placement' => 'hero', 'is_active' => true, 'alt_text' => 'First promo']);

    $this->get(route('home'))->assertOk()->assertSee('First promo');

    $updated = Banner::where('alt_text', 'First promo')->first();
    $updated->update(['alt_text' => 'Updated promo']);

    $this->get(route('home'))->assertOk()->assertSee('Updated promo')->assertDontSee('First promo');
});

it('invalidates the homepage cache when a home section is toggled off', function () {
    $this->seed(HomeSectionSeeder::class);
    makeVisibleProduct(['name' => 'Best Selling Widget']);

    $this->get(route('home'))->assertOk()->assertSee('Best sellers');

    HomeSection::where('type', HomeSectionType::BestSellers->value)->first()->update(['is_active' => false]);

    $this->get(route('home'))->assertOk()->assertDontSee('Best sellers');
});
