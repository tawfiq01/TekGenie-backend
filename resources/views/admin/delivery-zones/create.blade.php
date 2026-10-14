<x-layouts.admin title="Add delivery zone">
    <h1 class="text-2xl font-bold text-ink">Add delivery zone</h1>

    <form method="POST" action="{{ route('admin.delivery-zones.store') }}" class="mt-6 max-w-lg">
        @csrf
        <x-admin.form-section>
            <x-admin.input label="Name" name="name" :value="old('name')" required />
            <x-admin.input label="Base charge (৳)" name="base_charge" type="number" step="0.01" :value="old('base_charge')" required />
            <x-admin.input label="Free delivery threshold (৳, optional)" name="free_delivery_threshold" type="number" step="0.01" :value="old('free_delivery_threshold')" />
            <x-admin.input label="Estimate text (e.g. 1-2 days)" name="estimate_text" :value="old('estimate_text')" />
            <x-admin.input label="Sort order" name="sort_order" type="number" :value="old('sort_order', 0)" />
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Create zone
        </button>
    </form>
</x-layouts.admin>
