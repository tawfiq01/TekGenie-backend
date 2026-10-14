<x-layouts.admin title="Coupon report">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Coupon usage</h1>
        <a href="{{ route('admin.reports.coupons.export', request()->query()) }}" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
            Export CSV
        </a>
    </div>

    <x-admin.filters :action="route('admin.reports.coupons')">
        <x-admin.input label="From" name="from" type="date" :value="$from->toDateString()" />
        <x-admin.input label="To" name="to" type="date" :value="$to->toDateString()" />
    </x-admin.filters>

    <div class="mt-6">
        <x-admin.table :headers="['Code', 'Used', 'Usage limit', 'Total discount']">
            @forelse ($rows as $row)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $row['code'] }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $row['used_count'] }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $row['usage_limit'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-ink-soft">৳{{ $row['total_discount'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-ink-soft">No coupon usage in this period.</td></tr>
            @endforelse
        </x-admin.table>
    </div>
</x-layouts.admin>
