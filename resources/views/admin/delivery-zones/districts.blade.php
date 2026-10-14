<x-layouts.admin title="Assign districts">
    <h1 class="text-2xl font-bold text-ink">Assign districts to zones</h1>
    <p class="mt-1 text-sm text-ink-soft">Every district must resolve to a zone before it can be delivered to.</p>

    <form method="POST" action="{{ route('admin.delivery-zones.districts.assign') }}" class="mt-6">
        @csrf
        @method('PUT')

        <x-admin.table :headers="['Division', 'District', 'Zone']">
            @foreach ($districts as $district)
                <tr>
                    <td class="px-4 py-3 text-ink-soft">{{ $district->division->name }}</td>
                    <td class="px-4 py-3 text-ink">{{ $district->name }}</td>
                    <td class="px-4 py-3">
                        <select name="zones[{{ $district->id }}]" class="rounded-[var(--radius-sm)] border border-border px-2 py-1 text-sm">
                            <option value="">— Unassigned —</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}" @selected($district->delivery_zone_id === $zone->id)>{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save assignments
        </button>
    </form>

    <div class="mt-10 max-w-lg">
        <h2 class="text-lg font-semibold text-ink">Add an area / thana override</h2>
        <form method="POST" action="{{ route('admin.delivery-zones.areas.store') }}" class="mt-4">
            @csrf
            <x-admin.form-section>
                <x-admin.select label="District" name="district_id" :options="$districts->pluck('name', 'id')" placeholder="— Choose —" />
                <x-admin.input label="Area / thana name" name="name" required />
                <x-admin.select label="Zone (optional override)" name="delivery_zone_id" :options="$zones->pluck('name', 'id')" placeholder="— Use district's zone —" />
            </x-admin.form-section>
            <button type="submit" class="mt-4 rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
                Add area
            </button>
        </form>
    </div>
</x-layouts.admin>
