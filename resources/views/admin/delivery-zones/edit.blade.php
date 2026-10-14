<x-layouts.admin title="Edit delivery zone">
    <h1 class="text-2xl font-bold text-ink">Edit delivery zone — {{ $zone->name }}</h1>

    <form method="POST" action="{{ route('admin.delivery-zones.update', $zone) }}" class="mt-6 max-w-lg">
        @csrf
        @method('PUT')
        <x-admin.form-section>
            <x-admin.input label="Name" name="name" :value="old('name', $zone->name)" required />
            <x-admin.input label="Base charge (৳)" name="base_charge" type="number" step="0.01" :value="old('base_charge', $zone->base_charge)" required />
            <x-admin.input label="Free delivery threshold (৳, optional)" name="free_delivery_threshold" type="number" step="0.01" :value="old('free_delivery_threshold', $zone->free_delivery_threshold)" />
            <x-admin.input label="Estimate text (e.g. 1-2 days)" name="estimate_text" :value="old('estimate_text', $zone->estimate_text)" />
            <x-admin.input label="Sort order" name="sort_order" type="number" :value="old('sort_order', $zone->sort_order)" />
            <x-admin.checkbox label="Active" name="is_active" :checked="$zone->is_active" />
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>

    @if ($areas->isNotEmpty())
        <div class="mt-10 max-w-lg">
            <h2 class="text-lg font-semibold text-ink">Area overrides in this zone</h2>
            <ul class="mt-2 space-y-1 text-sm text-ink-soft">
                @foreach ($areas as $area)
                    <li>{{ $area->name }} ({{ $area->district->name }})</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-layouts.admin>
