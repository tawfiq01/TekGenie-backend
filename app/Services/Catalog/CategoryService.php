<?php

namespace App\Services\Catalog;

use App\Models\Category;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Category management (FR-CAT-001/002). Only one level of subcategory is
 * supported (SRS §5.1), and a category with products or children can't be
 * deleted — only deactivated.
 */
class CategoryService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param  array<string, mixed>  $data */
    public function create(array $data, ?UploadedFile $image = null): Category
    {
        $this->assertValidParent($data['parent_id'] ?? null, null);

        $category = DB::transaction(function () use ($data, $image) {
            $category = Category::create([
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name'], null),
                'description' => $data['description'] ?? null,
                'image_path' => $image ? $this->storeImage($image) : null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => true,
            ]);

            return $category;
        });

        $this->audit->log('admin.category.created', $category, ['name' => $category->name, 'slug' => $category->slug]);

        return $category;
    }

    /** @param  array<string, mixed>  $data */
    public function update(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        $this->assertValidParent($data['parent_id'] ?? null, $category);

        $before = $category->only(['name', 'slug', 'parent_id', 'is_active']);

        DB::transaction(function () use ($category, $data, $image) {
            $category->forceFill([
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name'], $category),
                'description' => $data['description'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'sort_order' => $data['sort_order'] ?? $category->sort_order,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $category->is_active,
            ]);

            if ($image) {
                $category->image_path = $this->storeImage($image);
            }

            $category->save();
        });

        $this->audit->log('admin.category.updated', $category, [
            'before' => $before,
            'after' => $category->only(['name', 'slug', 'parent_id', 'is_active']),
        ]);

        return $category;
    }

    public function destroy(Category $category): void
    {
        if ($category->children()->exists() || $category->primaryProducts()->exists() || $category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'A category with products or child categories cannot be deleted — deactivate it instead.',
            ]);
        }

        $category->delete();

        $this->audit->log('admin.category.deleted', $category);
    }

    private function assertValidParent(?int $parentId, ?Category $self): void
    {
        if ($parentId === null) {
            return;
        }

        if ($self && $parentId === $self->id) {
            throw ValidationException::withMessages(['parent_id' => 'A category cannot be its own parent.']);
        }

        $parent = Category::query()->find($parentId);

        if ($parent && $parent->parent_id !== null) {
            throw ValidationException::withMessages(['parent_id' => 'Only one level of subcategory is supported.']);
        }

        if ($self && $self->children()->exists()) {
            throw ValidationException::withMessages(['parent_id' => 'A category with subcategories cannot itself become a subcategory.']);
        }
    }

    private function resolveSlug(?string $slug, string $name, ?Category $self): string
    {
        $base = Str::slug($slug ?: $name);
        $candidate = $base;
        $suffix = 2;

        while (
            Category::query()
                ->where('slug', $candidate)
                ->when($self, fn ($q) => $q->where('id', '!=', $self->id))
                ->exists()
        ) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    private function storeImage(UploadedFile $image): string
    {
        return Storage::disk('public')->putFile('categories', $image);
    }
}
