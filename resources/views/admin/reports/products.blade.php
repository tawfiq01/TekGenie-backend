<x-layouts.admin title="Product report">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Product performance</h1>
        <a href="{{ route('admin.reports.products.export', request()->query()) }}" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
            Export CSV
        </a>
    </div>

    <x-admin.filters :action="route('admin.reports.products')">
        <x-admin.input label="From" name="from" type="date" :value="$from->toDateString()" />
        <x-admin.input label="To" name="to" type="date" :value="$to->toDateString()" />
    </x-admin.filters>

    <div class="mt-6">
        <x-admin.table :headers="['Product', 'SKU', 'Units sold', 'Revenue', 'Returned units']">
            @forelse ($rows as $row)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $row['product_name'] }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $row['sku'] }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $row['units_sold'] }}</td>
                    <td class="px-4 py-3 text-ink-soft">৳{{ $row['revenue'] }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $row['returned_units'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-ink-soft">No sales in this period.</td></tr>
            @endforelse
        </x-admin.table>
    </div>
</x-layouts.admin>
