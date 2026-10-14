<?php

use App\Models\Admin;
use App\Models\Brand;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    Storage::fake('public');
});

it('creates a brand with an auto-generated slug and logo', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $this->actingAs($admin, 'admin')->post(route('admin.brands.store'), [
        'name' => 'Acme Audio',
        'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
    ])->assertRedirect(route('admin.brands.index'));

    $brand = Brand::where('name', 'Acme Audio')->first();

    expect($brand)->not->toBeNull()
        ->and($brand->slug)->toBe('acme-audio')
        ->and($brand->logo_path)->not->toBeNull();

    Storage::disk('public')->assertExists($brand->logo_path);
});

it('refuses a duplicate brand name', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    Brand::factory()->create(['name' => 'Acme Audio']);

    $this->actingAs($admin, 'admin')->post(route('admin.brands.store'), [
        'name' => 'Acme Audio',
    ])->assertSessionHasErrors('name');
});

it('deactivates a brand without deleting it', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $brand = Brand::factory()->create(['is_active' => true]);

    $this->actingAs($admin, 'admin')->put(route('admin.brands.update', $brand), [
        'name' => $brand->name,
        'slug' => $brand->slug,
        'is_active' => '0',
    ])->assertRedirect(route('admin.brands.index'));

    expect($brand->refresh()->is_active)->toBeFalse();
});
