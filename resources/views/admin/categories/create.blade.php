<x-layouts.admin title="Add category">
    <h1 class="text-2xl font-bold text-ink">Add category</h1>

    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="mt-6 max-w-xl">
        @csrf

        <x-admin.form-section>
            <x-admin.input label="Name" name="name" :value="old('name')" required />
            <x-admin.input label="Slug (optional — auto-generated from name if left blank)" name="slug" :value="old('slug')" />
            <x-admin.select
                label="Parent category (optional — one level of subcategory only)"
                name="parent_id"
                :options="$topLevelCategories->pluck('name', 'id')"
                :selected="old('parent_id')"
                placeholder="— Top level —"
            />
            <x-admin.textarea label="Description" name="description" :value="old('description')" />
            <x-admin.input label="Image" name="image" type="file" accept="image/jpeg,image/png,image/webp" />
            <x-admin.input label="Meta title" name="meta_title" :value="old('meta_title')" />
            <x-admin.textarea label="Meta description" name="meta_description" :value="old('meta_description')" :rows="2" />
            <x-admin.input label="Sort order" name="sort_order" type="number" :value="old('sort_order', 0)" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Create category
        </button>
    </form>
</x-layouts.admin>
