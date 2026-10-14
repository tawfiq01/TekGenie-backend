<x-layouts.guest title="New order">
    <h1 class="text-lg font-semibold text-ink">New order: {{ $order->order_number }}</h1>

    <dl class="mt-4 space-y-2 text-sm">
        <div><dt class="font-medium text-ink">Customer</dt><dd class="text-ink-soft">{{ $order->customer_name }} ({{ $order->phone }})</dd></div>
        <div><dt class="font-medium text-ink">Delivery</dt><dd class="text-ink-soft">{{ $order->address_line }}, {{ $order->area_name ? $order->area_name.', ' : '' }}{{ $order->district_name }}</dd></div>
        <div><dt class="font-medium text-ink">Items</dt>
            <dd class="text-ink-soft">
                @foreach ($order->items as $item)
                    {{ $item->product_name }} × {{ $item->quantity }}@if (! $loop->last), @endif
                @endforeach
            </dd>
        </div>
        <div><dt class="font-medium text-ink">Grand total</dt><dd class="text-ink-soft">৳{{ $order->grand_total }} ({{ $order->payment_method->label() }})</dd></div>
    </dl>
</x-layouts.guest>
