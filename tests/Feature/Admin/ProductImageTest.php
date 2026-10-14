<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\OptionType;
use App\Models\Product;
use App\Models\ProductImage;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('Catalog Manager');
    Storage::fake('public');
});

it('uploads an image, resizes it to the standard WebP sizes with a fallback, and sets it main (TC-CAT-009, TC-CAT-010)', function () {
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.images.store', $product), [
        'image' => UploadedFile::fake()->image('photo.jpg', 3000, 3000),
        'alt_text' => 'Front view',
    ])->assertRedirect(route('admin.products.edit', $product));

    $image = $product->images()->firstOrFail();

    expect($image->is_main)->toBeTrue()
        ->and($image->alt_text)->toBe('Front view')
        ->and($image->width)->toBe(3000)
        ->and($image->height)->toBe(3000);

    foreach (['thumbnail', 'card', 'detail', 'zoom'] as $size) {
        Storage::disk('public')->assertExists("{$image->path}-{$size}.webp");
    }
    Storage::disk('public')->assertExists("{$image->path}-fallback.jpg");
});

it('links an image to an option value and reorders images', function () {
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);
    $colour = OptionType::create(['name' => 'Colour', 'display_style' => 'swatch']);
    $white = $colour->values()->create(['value' => 'White']);

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.images.store', $product), [
        'image' => UploadedFile::fake()->image('a.jpg', 500, 500),
    ]);
    $this->actingAs($this->admin, 'admin')->post(route('admin.products.images.store', $product), [
        'image' => UploadedFile::fake()->image('b.jpg', 500, 500),
    ]);

    [$first, $second] = $product->images()->orderBy('sort_order')->get()->all();

    $this->actingAs($this->admin, 'admin')->put(route('admin.products.images.update', $first), [
        'option_value_id' => $white->id,
        'alt_text' => 'White colourway',
    ])->assertRedirect();

    expect($first->refresh()->option_value_id)->toBe($white->id);

    $this->actingAs($this->admin, 'admin')->put(route('admin.products.images.reorder', $product), [
        'image_ids' => [$second->id, $first->id],
    ])->assertRedirect();

    expect($second->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1);

    $this->actingAs($this->admin, 'admin')->put(route('admin.products.images.set-main', $second))->assertRedirect();

    expect($second->refresh()->is_main)->toBeTrue()
        ->and($first->refresh()->is_main)->toBeFalse();
});

it('deletes an image and its stored files', function () {
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

    $this->actingAs($this->admin, 'admin')->post(route('admin.products.images.store', $product), [
        'image' => UploadedFile::fake()->image('a.jpg', 500, 500),
    ]);
    $image = $product->images()->firstOrFail();
    $path = $image->path;

    $this->actingAs($this->admin, 'admin')->delete(route('admin.products.images.destroy', $image))->assertRedirect();

    expect(ProductImage::find($image->id))->toBeNull();
    Storage::disk('public')->assertMissing("{$path}-card.webp");
});
