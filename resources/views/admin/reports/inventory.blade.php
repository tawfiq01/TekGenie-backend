<x-layouts.admin title="Inventory report">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Inventory report</h1>
        <a href="{{ route('admin.reports.inventory.export') }}" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
            Export CSV
        </a>
    </div>

    <div class="mt-6 grid grid-cols-3 gap-4">
        <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Stock value</p><p class="mt-1 text-xl font-bold text-ink">৳{{ $report['totals']['stock_value'] }}</p></div>
        <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Low stock</p><p class="mt-1 text-xl font-bold text-ink">{{ $report['totals']['low_stock_count'] }}</p></div>
        <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Out of stock</p><p class="mt-1 text-xl font-bold text-ink">{{ $report['totals']['out_of_stock_count'] }}</p></div>
    </div>

    <div class="mt-6">
        <x-admin.table :headers="['SKU', 'Product', 'Stock', 'Status', 'Stock value']">
            @forelse ($report['rows'] as $row)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $row['sku'] }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $row['product_name'] }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $row['stock'] }}</td>
                    <td class="px-4 py-3">
                        @if ($row['state'] === 'out_of_stock')
                            <span class="rounded-[var(--radius-pill)] bg-danger-soft px-2 py-1 text-xs text-danger">Out of stock</span>
                        @elseif ($row['state'] === 'low')
                            <span class="rounded-[var(--radius-pill)] bg-warning-soft px-2 py-1 text-xs text-warning">Low stock</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">In stock</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-ink-soft">৳{{ number_format($row['stock_value'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-ink-soft">No active variants.</td></tr>
            @endforelse
        </x-admin.table>
    </div>
</x-layouts.admin>
