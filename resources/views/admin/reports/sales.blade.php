<x-layouts.admin title="Sales report">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Sales report</h1>
        <a href="{{ route('admin.reports.sales.export', request()->query()) }}" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
            Export CSV
        </a>
    </div>

    <x-admin.filters :action="route('admin.reports.sales')">
        <x-admin.input label="From" name="from" type="date" :value="$from->toDateString()" />
        <x-admin.input label="To" name="to" type="date" :value="$to->toDateString()" />
        <x-admin.select label="Zone" name="zone_id" :options="$zones->pluck('name', 'id')" :selected="$zoneId" placeholder="Any" />
    </x-admin.filters>

    @if ($report['totals']['orders'] === 0)
        <p class="mt-8 text-sm text-ink-soft">No orders in this period.</p>
    @else
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Orders</p><p class="mt-1 text-xl font-bold text-ink">{{ $report['totals']['orders'] }}</p></div>
            <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Gross</p><p class="mt-1 text-xl font-bold text-ink">৳{{ $report['totals']['gross'] }}</p></div>
            <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Discount</p><p class="mt-1 text-xl font-bold text-ink">৳{{ $report['totals']['discount'] }}</p></div>
            <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Delivery</p><p class="mt-1 text-xl font-bold text-ink">৳{{ $report['totals']['delivery'] }}</p></div>
            <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Net</p><p class="mt-1 text-xl font-bold text-ink">৳{{ $report['totals']['net'] }}</p></div>
            <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Cancelled value</p><p class="mt-1 text-xl font-bold text-ink">৳{{ $report['totals']['cancelled_value'] }}</p></div>
            <div class="rounded-[var(--radius-md)] border border-border p-4"><p class="text-xs text-ink-soft">Returned value</p><p class="mt-1 text-xl font-bold text-ink">৳{{ $report['totals']['returned_value'] }}</p></div>
        </div>

        <div class="mt-6">
            <x-admin.table :headers="['Date', 'Orders', 'Gross', 'Discount', 'Delivery', 'Net']">
                @foreach ($report['rows'] as $row)
                    <tr>
                        <td class="px-4 py-3 text-ink">{{ $row['period'] }}</td>
                        <td class="px-4 py-3 text-ink-soft">{{ $row['orders'] }}</td>
                        <td class="px-4 py-3 text-ink-soft">৳{{ $row['gross'] }}</td>
                        <td class="px-4 py-3 text-ink-soft">৳{{ $row['discount'] }}</td>
                        <td class="px-4 py-3 text-ink-soft">৳{{ $row['delivery'] }}</td>
                        <td class="px-4 py-3 text-ink-soft">৳{{ $row['net'] }}</td>
                    </tr>
                @endforeach
            </x-admin.table>
        </div>
    @endif
</x-layouts.admin>
