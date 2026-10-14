<x-layouts.admin title="Coupons">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Coupons</h1>
        <a href="{{ route('admin.coupons.create') }}" class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Add coupon
        </a>
    </div>

    <div class="mt-6">
        <x-admin.table :headers="['Code', 'Type', 'Value', 'Used', 'Status', '']">
            @foreach ($coupons as $coupon)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $coupon->code }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ ucfirst(str_replace('_', ' ', $coupon->type->value)) }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $coupon->type->value === 'percentage' ? $coupon->value.'%' : '৳'.$coupon->value }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                    <td class="px-4 py-3">
                        @if ($coupon->is_active)
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">Active</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-bg-subtle px-2 py-1 text-xs text-muted">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-link hover:underline">Edit</a>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        <div class="mt-6">{{ $coupons->links() }}</div>
    </div>
</x-layouts.admin>
