<x-layouts.admin title="Edit banner">
    <h1 class="text-2xl font-bold text-ink">Edit banner</h1>

    <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')

        <x-admin.form-section>
            <x-admin.select label="Placement" name="placement" :options="['hero' => 'Hero (home page top)', 'promo' => 'Promotional band']"
                            :selected="old('placement', $banner->placement)" />
            <x-admin.input label="Title (optional)" name="title" :value="old('title', $banner->title)" />
            <x-admin.input label="Subtitle (optional)" name="subtitle" :value="old('subtitle', $banner->subtitle)" />
            <x-admin.input label="Button label (optional)" name="button_label" :value="old('button_label', $banner->button_label)" />
            <x-admin.input label="Link URL (optional)" name="link_url" :value="old('link_url', $banner->link_url)" />
            <x-admin.input label="Alt text" name="alt_text" :value="old('alt_text', $banner->alt_text)" required />

            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_desktop_path) }}" alt="" class="h-16 w-28 rounded-[var(--radius-sm)] object-cover">
            <x-admin.input label="Replace desktop image" name="image_desktop" type="file" accept="image/jpeg,image/png,image/webp" />
            <x-admin.input label="Replace mobile image" name="image_mobile" type="file" accept="image/jpeg,image/png,image/webp" />

            <x-admin.input label="Starts at (optional)" name="starts_at" type="datetime-local" :value="old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i'))" />
            <x-admin.input label="Ends at (optional)" name="ends_at" type="datetime-local" :value="old('ends_at', $banner->ends_at?->format('Y-m-d\TH:i'))" />
            <x-admin.input label="Sort order" name="sort_order" type="number" :value="old('sort_order', $banner->sort_order)" />
            <x-admin.checkbox label="Active" name="is_active" :checked="$banner->is_active" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>

    <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" class="mt-4" onsubmit="return confirm('Delete this banner?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-danger hover:underline">Delete banner</button>
    </form>
</x-layouts.admin>
