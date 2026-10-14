<x-layouts.admin title="Add brand">
    <h1 class="text-2xl font-bold text-ink">Add brand</h1>

    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="mt-6 max-w-xl">
        @csrf

        <x-admin.form-section>
            <x-admin.input label="Name" name="name" :value="old('name')" required />
            <x-admin.input label="Slug (optional — auto-generated from name if left blank)" name="slug" :value="old('slug')" />
            <x-admin.input label="Logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Create brand
        </button>
    </form>
</x-layouts.admin>
