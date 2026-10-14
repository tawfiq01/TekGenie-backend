<?php

use App\Models\District;
use App\Models\Division;
use App\Models\ProductVariant;
use Database\Seeders\BangladeshGeoSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

it('seeds all 8 divisions and 64 districts (TC-SHP-001)', function () {
    $this->seed(BangladeshGeoSeeder::class);

    expect(Division::count())->toBe(8)
        ->and(District::count())->toBe(64);
});

it('seeds the six default admin roles with admin-guard permissions', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $superAdmin = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();

    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->permissions()->count())->toBe(21);
});

it('only displays a compare-at price when it is genuinely higher (FR-CAT-006, BR-17)', function () {
    $higher = ProductVariant::make(['price' => 2790, 'compare_at_price' => 3490]);
    $equal = ProductVariant::make(['price' => 999, 'compare_at_price' => 999]);
    $none = ProductVariant::make(['price' => 500, 'compare_at_price' => null]);

    expect($higher->hasGenuineCompareAtPrice())->toBeTrue()
        ->and($equal->hasGenuineCompareAtPrice())->toBeFalse()
        ->and($none->hasGenuineCompareAtPrice())->toBeFalse();
});
