<x-layouts.guest title="Order confirmed">
    <h1 class="text-lg font-semibold text-ink">Thanks for your order, {{ $order->customer_name }}!</h1>
    <p class="mt-2 text-sm text-ink-soft">
        Your order <strong>{{ $order->order_number }}</strong> has been placed and will be confirmed shortly.
        You'll pay <strong>৳{{ $order->grand_total }}</strong> by Cash on Delivery.
    </p>

    <dl class="mt-4 space-y-2 text-sm">
        @foreach ($order->items as $item)
            <div class="flex justify-between">
                <dt class="text-ink">{{ $item->product_name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }} × {{ $item->quantity }}</dt>
                <dd class="text-ink-soft">৳{{ $item->line_total }}</dd>
            </div>
        @endforeach
    </dl>

    <dl class="mt-4 space-y-1 border-t border-border pt-4 text-sm">
        <div class="flex justify-between"><dt class="text-ink-soft">Subtotal</dt><dd class="text-ink">৳{{ $order->subtotal }}</dd></div>
        @if ((float) $order->discount_total > 0)
            <div class="flex justify-between"><dt class="text-ink-soft">Discount</dt><dd class="text-ink">−৳{{ $order->discount_total }}</dd></div>
        @endif
        <div class="flex justify-between"><dt class="text-ink-soft">Delivery</dt><dd class="text-ink">৳{{ $order->delivery_charge }}</dd></div>
        <div class="flex justify-between font-semibold"><dt class="text-ink">Total</dt><dd class="text-ink">৳{{ $order->grand_total }}</dd></div>
    </dl>

    <p class="mt-4 text-sm text-ink-soft">Delivering to: {{ $order->address_line }}, {{ $order->area_name ? $order->area_name.', ' : '' }}{{ $order->district_name }}.</p>
</x-layouts.guest>
