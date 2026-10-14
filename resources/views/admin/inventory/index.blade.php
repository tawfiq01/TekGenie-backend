<x-layouts.admin title="Inventory">
    <h1 class="text-2xl font-bold text-ink">Inventory</h1>

    <x-admin.filters :action="route('admin.inventory.index')">
        <x-admin.input label="Search (SKU or product)" name="search" :value="request('search')" />
        <x-admin.select label="Status" name="filter" :options="['low_stock' => 'Low stock', 'out_of_stock' => 'Out of stock']"
                        :selected="request('filter')" placeholder="Any" />
    </x-admin.filters>

    <div class="mt-6">
        <x-admin.table :headers="['SKU', 'Product', 'Stock', 'Threshold', 'Status', '']">
            @forelse ($variants as $variant)
                @php($state = $variant->stockState($defaultThreshold))
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $variant->sku }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $variant->product->name }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $variant->stock }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $variant->low_stock_threshold ?? $defaultThreshold.' (default)' }}</td>
                    <td class="px-4 py-3">
                        @if ($state === 'out_of_stock')
                            <span class="rounded-[var(--radius-pill)] bg-danger-soft px-2 py-1 text-xs text-danger">Out of stock</span>
                        @elseif ($state === 'low')
                            <span class="rounded-[var(--radius-pill)] bg-warning-soft px-2 py-1 text-xs text-warning">Low stock</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">In stock</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.inventory.history', $variant) }}" class="text-link hover:underline">History / adjust</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-ink-soft">No variants match these filters.</td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-6">
            {{ $variants->links() }}
        </div>
    </div>
</x-layouts.admin>
