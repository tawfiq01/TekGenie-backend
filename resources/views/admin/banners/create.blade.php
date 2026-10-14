<x-layouts.admin title="Add banner">
    <h1 class="text-2xl font-bold text-ink">Add banner</h1>

    <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="mt-6 max-w-xl">
        @csrf

        <x-admin.form-section>
            <x-admin.select label="Placement" name="placement" :options="['hero' => 'Hero (home page top)', 'promo' => 'Promotional band']"
                            :selected="old('placement')" placeholder="— Choose —" />
            <x-admin.input label="Title (optional)" name="title" :value="old('title')" />
            <x-admin.input label="Subtitle (optional)" name="subtitle" :value="old('subtitle')" />
            <x-admin.input label="Button label (optional)" name="button_label" :value="old('button_label')" />
            <x-admin.input label="Link URL (optional)" name="link_url" :value="old('link_url')" />
            <x-admin.input label="Alt text" name="alt_text" :value="old('alt_text')" required />
            <x-admin.input label="Desktop image" name="image_desktop" type="file" accept="image/jpeg,image/png,image/webp" required />
            <x-admin.input label="Mobile image (optional)" name="image_mobile" type="file" accept="image/jpeg,image/png,image/webp" />
            <x-admin.input label="Starts at (optional)" name="starts_at" type="datetime-local" :value="old('starts_at')" />
            <x-admin.input label="Ends at (optional)" name="ends_at" type="datetime-local" :value="old('ends_at')" />
            <x-admin.input label="Sort order" name="sort_order" type="number" :value="old('sort_order', 0)" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Create banner
        </button>
    </form>
</x-layouts.admin>
