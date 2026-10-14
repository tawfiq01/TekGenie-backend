<x-layouts.admin title="Edit brand">
    <h1 class="text-2xl font-bold text-ink">Edit brand — {{ $brand->name }}</h1>

    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')

        <x-admin.form-section>
            <x-admin.input label="Name" name="name" :value="old('name', $brand->name)" required />
            <x-admin.input label="Slug" name="slug" :value="old('slug', $brand->slug)" />

            @if ($brand->logo_path)
                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($brand->logo_path) }}" alt="" class="h-16 w-16 rounded-[var(--radius-sm)] object-cover">
            @endif
            <x-admin.input label="Replace logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" />

            <x-admin.checkbox label="Active" name="is_active" :checked="$brand->is_active" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>

    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="mt-4"
          onsubmit="return confirm('Delete this brand? This only works if it has no products.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-danger hover:underline">Delete brand</button>
    </form>
</x-layouts.admin>
