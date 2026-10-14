<?php

use App\Models\Admin;
use App\Models\Category;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    Storage::fake('public');
});

it('creates a category under a top-level parent with an auto-generated slug (TC-CAT-001, VR-12)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $audio = Category::factory()->create(['name' => 'Audio']);

    $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
        'name' => 'Earbuds',
        'parent_id' => $audio->id,
        'image' => UploadedFile::fake()->image('earbuds.jpg', 400, 400),
    ])->assertRedirect(route('admin.categories.index'));

    $category = Category::where('name', 'Earbuds')->first();

    expect($category)->not->toBeNull()
        ->and($category->slug)->toBe('earbuds')
        ->and($category->parent_id)->toBe($audio->id)
        ->and($category->image_path)->not->toBeNull();

    Storage::disk('public')->assertExists($category->image_path);
});

it('refuses more than one level of subcategory nesting', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $topLevel = Category::factory()->create();
    $subcategory = Category::factory()->create(['parent_id' => $topLevel->id]);

    $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
        'name' => 'Too Deep',
        'parent_id' => $subcategory->id,
    ])->assertSessionHasErrors('parent_id');

    expect(Category::where('name', 'Too Deep')->exists())->toBeFalse();
});

it('reorders and deactivates a category (TC-CAT-002)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $category = Category::factory()->create(['sort_order' => 0, 'is_active' => true]);

    $this->actingAs($admin, 'admin')->put(route('admin.categories.update', $category), [
        'name' => $category->name,
        'slug' => $category->slug,
        'sort_order' => 5,
        'is_active' => '0',
    ])->assertRedirect(route('admin.categories.index'));

    expect($category->refresh())
        ->sort_order->toBe(5)
        ->is_active->toBeFalse();
});

it('refuses to delete a category that has subcategories (TC-CAT-003, FR-CAT-002)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $parent = Category::factory()->create();
    Category::factory()->create(['parent_id' => $parent->id]);

    $this->actingAs($admin, 'admin')->delete(route('admin.categories.destroy', $parent))
        ->assertSessionHasErrors('category');

    expect($parent->fresh())->not->toBeNull();
});

it('enforces products.view (read) vs products.edit (write) on the catalogue routes', function () {
    $viewer = Admin::factory()->create();
    $viewer->assignRole('Content Editor');

    $this->actingAs($viewer, 'admin')->get(route('admin.categories.index'))->assertOk();
    $this->actingAs($viewer, 'admin')->get(route('admin.categories.create'))->assertForbidden();

    $noAccess = Admin::factory()->create();
    $noAccess->assignRole('Order Manager');

    $this->actingAs($noAccess, 'admin')->get(route('admin.categories.index'))->assertOk();
    $this->actingAs($noAccess, 'admin')->post(route('admin.categories.store'), ['name' => 'X'])->assertForbidden();
});
