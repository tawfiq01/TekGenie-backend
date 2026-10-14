<x-layouts.admin title="Orders">
    <h1 class="text-2xl font-bold text-ink">Orders</h1>

    <x-admin.filters :action="route('admin.orders.index')">
        <x-admin.input label="Search (order #, phone, name)" name="search" :value="request('search')" />
        <x-admin.select label="Status" name="status" :options="collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :selected="request('status')" placeholder="Any" />
        <x-admin.select label="Payment" name="payment_status" :options="collect($paymentStatuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :selected="request('payment_status')" placeholder="Any" />
        <x-admin.select label="Zone" name="zone" :options="$zones->pluck('name', 'id')" :selected="request('zone')" placeholder="Any" />
        <x-admin.input label="From" name="date_from" type="date" :value="request('date_from')" />
        <x-admin.input label="To" name="date_to" type="date" :value="request('date_to')" />
    </x-admin.filters>

    <div class="mt-6">
        <x-admin.table :headers="['Order #', 'Customer', 'Placed', 'Status', 'Payment', 'Total', '']">
            @forelse ($orders as $order)
                <tr>
                    <td class="px-4 py-3 font-medium text-ink">{{ $order->order_number }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $order->customer_name }}<br><span class="text-xs">{{ $order->phone }}</span></td>
                    <td class="px-4 py-3 text-ink-soft">{{ $order->placed_at->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3"><span class="rounded-[var(--radius-pill)] bg-accent-soft px-2 py-1 text-xs text-ink">{{ $order->status->label() }}</span></td>
                    <td class="px-4 py-3 text-ink-soft">{{ $order->payment_status->label() }}</td>
                    <td class="px-4 py-3 text-ink-soft">৳{{ $order->grand_total }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-link hover:underline">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-ink-soft">No orders match these filters.</td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</x-layouts.admin>
