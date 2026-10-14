<x-layouts.admin title="Add option type">
    <h1 class="text-2xl font-bold text-ink">Add option type</h1>

    <form method="POST" action="{{ route('admin.option-types.store') }}" class="mt-6 max-w-xl">
        @csrf

        <x-admin.form-section>
            <x-admin.input label="Name (e.g. Colour, Size)" name="name" :value="old('name')" required />
            <x-admin.select
                label="Display style"
                name="display_style"
                :options="['chip' => 'Chip', 'swatch' => 'Swatch', 'dropdown' => 'Dropdown']"
                :selected="old('display_style', 'chip')"
            />
            <x-admin.input label="Sort order" name="sort_order" type="number" :value="old('sort_order', 0)" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Create option type
        </button>
    </form>
</x-layouts.admin>
