<x-layouts.admin title="Delivery zones">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Delivery zones</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.delivery-zones.districts') }}" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
                Assign districts
            </a>
            <a href="{{ route('admin.delivery-zones.create') }}" class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                Add zone
            </a>
        </div>
    </div>

    @if ($unassignedDistricts->isNotEmpty())
        <div class="mt-6 rounded-[var(--radius-sm)] bg-warning-soft px-4 py-3 text-sm text-warning">
            {{ $unassignedDistricts->count() }} district(s) have no delivery zone and cannot be delivered to yet:
            {{ $unassignedDistricts->pluck('name')->join(', ') }}.
            <a href="{{ route('admin.delivery-zones.districts') }}" class="underline">Assign now</a>
        </div>
    @endif

    <div class="mt-6">
        <x-admin.table :headers="['Name', 'Charge', 'Free over', 'Estimate', 'Districts', 'Status', '']">
            @foreach ($zones as $zone)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $zone->name }}</td>
                    <td class="px-4 py-3 text-ink-soft">৳{{ $zone->base_charge }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $zone->free_delivery_threshold ? '৳'.$zone->free_delivery_threshold : '—' }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $zone->estimate_text ?? '—' }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $zone->districts_count }}</td>
                    <td class="px-4 py-3">
                        @if ($zone->is_active)
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">Active</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-bg-subtle px-2 py-1 text-xs text-muted">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.delivery-zones.edit', $zone) }}" class="text-link hover:underline">Edit</a>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    </div>
</x-layouts.admin>
