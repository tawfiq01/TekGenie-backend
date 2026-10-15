<?php

use App\Enums\HomeSectionType;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Setting;
use Database\Seeders\HomeSectionSeeder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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

/**
 * Tests above run under the 'array' cache store (phpunit.xml), which never
 * actually serializes anything — so a value could round-trip there while
 * still being broken under 'database'/'file', which do serialize. That
 * exact gap let a real bug through once already: config/cache.php's secure
 * -by-default `serializable_classes => false` makes the database store's
 * unserialize() silently return __PHP_Incomplete_Class for any cached
 * object instead of throwing, which is exactly what production uses
 * (CACHE_STORE=database). These force the real store to catch it again.
 */
it('round-trips a real Collection through the database cache store without __PHP_Incomplete_Class', function () {
    Setting::put('store', 'name', 'DB Store Test');
    Cache::store('database')->forget('settings.all.dbtest');

    $cached = Cache::store('database')->rememberForever(
        'settings.all.dbtest',
        fn () => Setting::query()->get()->mapWithKeys(fn ($s) => [$s->group.'.'.$s->key => $s->value])
    );
    expect($cached)->toBeInstanceOf(Collection::class);

    $reread = Cache::store('database')->get('settings.all.dbtest');
    expect($reread)->toBeInstanceOf(Collection::class);
    expect($reread->get('store.name'))->toBe('DB Store Test');
});

it('round-trips a cached Eloquent collection of categories through the database cache store', function () {
    Category::factory()->create(['name' => 'DB Store Category', 'is_active' => true]);

    Cache::store('database')->forget('storefront.categories.dbtest');
    Cache::store('database')->rememberForever(
        'storefront.categories.dbtest',
        fn () => Category::query()->where('is_active', true)->get()
    );

    $reread = Cache::store('database')->get('storefront.categories.dbtest');
    expect($reread)->toBeInstanceOf(EloquentCollection::class);
    expect($reread->first())->toBeInstanceOf(Category::class);
    expect($reread->first()->name)->toBe('DB Store Category');
});
