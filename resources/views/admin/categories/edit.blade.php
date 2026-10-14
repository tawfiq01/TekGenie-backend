<x-layouts.admin title="Edit category">
    <h1 class="text-2xl font-bold text-ink">Edit category — {{ $category->name }}</h1>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')

        <x-admin.form-section>
            <x-admin.input label="Name" name="name" :value="old('name', $category->name)" required />
            <x-admin.input label="Slug" name="slug" :value="old('slug', $category->slug)" />
            <x-admin.select
                label="Parent category (optional — one level of subcategory only)"
                name="parent_id"
                :options="$topLevelCategories->pluck('name', 'id')"
                :selected="old('parent_id', $category->parent_id)"
                placeholder="— Top level —"
            />
            <x-admin.textarea label="Description" name="description" :value="old('description', $category->description)" />

            @if ($category->image_path)
                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($category->image_path) }}" alt="" class="h-16 w-16 rounded-[var(--radius-sm)] object-cover">
            @endif
            <x-admin.input label="Replace image" name="image" type="file" accept="image/jpeg,image/png,image/webp" />

            <x-admin.input label="Meta title" name="meta_title" :value="old('meta_title', $category->meta_title)" />
            <x-admin.textarea label="Meta description" name="meta_description" :value="old('meta_description', $category->meta_description)" :rows="2" />
            <x-admin.input label="Sort order" name="sort_order" type="number" :value="old('sort_order', $category->sort_order)" />
            <x-admin.checkbox label="Active" name="is_active" :checked="$category->is_active" />
        </x-admin.form-section>

        <div class="mt-6 flex items-center gap-4">
            <button type="submit"
                    class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                Save changes
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="mt-4"
          onsubmit="return confirm('Delete this category? This only works if it has no products or subcategories.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-danger hover:underline">Delete category</button>
    </form>
</x-layouts.admin>
