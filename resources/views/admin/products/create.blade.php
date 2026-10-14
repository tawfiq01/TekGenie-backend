<x-layouts.admin title="Add product">
    <h1 class="text-2xl font-bold text-ink">Add product</h1>

    <form method="POST" action="{{ route('admin.products.store') }}" class="mt-6 max-w-2xl space-y-6">
        @csrf

        <x-admin.form-section title="Basics">
            <x-admin.input label="Name" name="name" :value="old('name')" required />
            <x-admin.input label="Slug (optional — auto-generated from name if left blank)" name="slug" :value="old('slug')" />
            <x-admin.select label="Primary category" name="category_id" :options="$categories->pluck('name', 'id')"
                            :selected="old('category_id')" placeholder="— Choose —" />
            <x-admin.select label="Brand (optional)" name="brand_id" :options="$brands->pluck('name', 'id')"
                            :selected="old('brand_id')" placeholder="— None —" />
            <x-admin.textarea label="Short description" name="short_description" :value="old('short_description')" :rows="2" />
            <x-admin.textarea label="Full description" name="description" :value="old('description')" :rows="6" />
        </x-admin.form-section>

        <x-admin.form-section title="Status">
            <x-admin.select label="Status" name="status" :options="['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived']"
                            :selected="old('status', 'draft')" />
            <x-admin.checkbox label="Featured" name="is_featured" :checked="old('is_featured', false)" />
        </x-admin.form-section>

        <x-admin.form-section title="Variant options">
            <p class="text-sm text-ink-soft">
                Choose the option types this product varies by (e.g. Colour, Size). Leave blank for a
                single-variant product. This cannot be changed after creation.
            </p>
            @foreach ($optionTypes as $optionType)
                <label class="flex items-center gap-2 text-sm text-ink-soft">
                    <input type="checkbox" name="option_type_ids[]" value="{{ $optionType->id }}"
                           {{ in_array($optionType->id, old('option_type_ids', [])) ? 'checked' : '' }}
                           class="rounded border-border">
                    {{ $optionType->name }}
                </label>
            @endforeach
        </x-admin.form-section>

        <x-admin.form-section title="SEO">
            <x-admin.input label="Meta title" name="meta_title" :value="old('meta_title')" />
            <x-admin.textarea label="Meta description" name="meta_description" :value="old('meta_description')" :rows="2" />
        </x-admin.form-section>

        <button type="submit"
                class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Create product
        </button>
    </form>
</x-layouts.admin>
