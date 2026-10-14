<?php

use App\Enums\HomeSectionType;
use App\Models\Admin;
use App\Models\Banner;
use App\Models\HomeSection;
use App\Models\Page;
use Database\Seeders\HomeSectionSeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('Content Editor');
    Storage::fake('public');
});

it('creates a banner with a desktop image (FR-STF-003)', function () {
    $this->actingAs($this->admin, 'admin')->post(route('admin.banners.store'), [
        'placement' => 'hero',
        'alt_text' => 'Summer sale',
        'image_desktop' => UploadedFile::fake()->image('hero.jpg', 1600, 600),
    ])->assertRedirect(route('admin.banners.index'));

    $banner = Banner::where('alt_text', 'Summer sale')->first();
    expect($banner)->not->toBeNull()->and($banner->placement)->toBe('hero');
    Storage::disk('public')->assertExists($banner->image_desktop_path);
});

it('requires an end date after the start date for a banner (VR-14)', function () {
    $this->actingAs($this->admin, 'admin')->post(route('admin.banners.store'), [
        'placement' => 'promo',
        'alt_text' => 'Promo',
        'image_desktop' => UploadedFile::fake()->image('promo.jpg', 800, 400),
        'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
        'ends_at' => now()->format('Y-m-d H:i:s'),
    ])->assertSessionHasErrors('ends_at');
});

it('reorders and toggles home page sections (FR-STF-001)', function () {
    $this->seed(HomeSectionSeeder::class);
    $sections = HomeSection::orderBy('sort_order')->get();

    $payload = $sections->map(fn ($s, $i) => [
        'id' => $s->id,
        'title' => $s->title,
        'sort_order' => $sections->count() - $i,
        'is_active' => $s->type !== HomeSectionType::BestSellers,
    ])->values()->all();

    $this->actingAs($this->admin, 'admin')->put(route('admin.home-sections.update'), [
        'sections' => $payload,
    ])->assertRedirect(route('admin.home-sections.index'));

    $bestSellers = HomeSection::where('type', HomeSectionType::BestSellers->value)->first();
    expect($bestSellers->is_active)->toBeFalse();
});

it('edits and publishes a static page (FR-STF-007)', function () {
    $this->seed(PageSeeder::class);
    $page = Page::where('slug', 'about')->firstOrFail();

    $this->actingAs($this->admin, 'admin')->put(route('admin.pages.update', $page), [
        'title' => 'About Us',
        'slug' => 'about',
        'body' => '<p>We are a Bangladesh-based store.</p><script>alert(1)</script>',
        'is_published' => true,
        'show_in_footer' => true,
    ])->assertRedirect(route('admin.pages.index'));

    $page->refresh();
    expect($page->is_published)->toBeTrue()
        ->and($page->body)->toContain('Bangladesh-based store')
        ->and($page->body)->not->toContain('<script>');
});

it('requires content.manage permission for banners and pages', function () {
    $other = Admin::factory()->create();
    $other->assignRole('Order Manager');

    $this->actingAs($other, 'admin')->get(route('admin.banners.index'))->assertForbidden();
    $this->actingAs($other, 'admin')->get(route('admin.pages.index'))->assertForbidden();
});
