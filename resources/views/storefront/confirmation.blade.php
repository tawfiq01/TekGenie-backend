<x-layouts.app title="Order confirmed">
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-ink">Thank you — your order is confirmed!</h1>
        <p class="mt-2 text-ink-soft">
            Order number <strong class="text-ink">{{ $order->order_number }}</strong>. We will call {{ $order->phone }} to confirm before dispatch.
        </p>

        <div class="mt-6 rounded-[var(--radius-md)] border border-border p-4">
            <h2 class="font-semibold text-ink">Order summary</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between text-ink-soft">
                        <span>{{ $item->product_name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }} × {{ $item->quantity }}</span>
                        <span>৳{{ number_format((float) $item->line_total, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            <dl class="mt-4 space-y-1 border-t border-border pt-4 text-sm">
                <div class="flex justify-between"><dt class="text-ink-soft">Subtotal</dt><dd>৳{{ $order->subtotal }}</dd></div>
                @if ((float) $order->discount_total > 0)
                    <div class="flex justify-between"><dt class="text-ink-soft">Discount</dt><dd class="text-success">&minus;৳{{ $order->discount_total }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-ink-soft">Delivery ({{ $order->delivery_zone_name }})</dt><dd>৳{{ $order->delivery_charge }}</dd></div>
                <div class="flex justify-between border-t border-border pt-2 text-base font-semibold"><dt>Total</dt><dd>৳{{ $order->grand_total }}</dd></div>
            </dl>
        </div>

        <div class="mt-6 rounded-[var(--radius-md)] border border-border p-4 text-sm text-ink-soft">
            <p><strong class="text-ink">Delivery address:</strong> {{ $order->address_line }}, {{ $order->area_name }}, {{ $order->district_name }}, {{ $order->division_name }}</p>
            <p class="mt-1"><strong class="text-ink">Payment method:</strong> Cash on Delivery</p>
        </div>

        <p class="mt-6 text-sm text-ink-soft">
            You can track this order anytime using your order number and phone number.
        </p>
        <a href="{{ url('/') }}" class="mt-4 inline-block rounded-[var(--radius-pill)] bg-ink px-6 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">
            Continue shopping
        </a>
    </div>
</x-layouts.app>
