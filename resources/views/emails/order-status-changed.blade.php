<x-layouts.guest title="Order update">
    <h1 class="text-lg font-semibold text-ink">Your order is now {{ $status->label() }}</h1>
    <p class="mt-2 text-sm text-ink-soft">
        Order <strong>{{ $order->order_number }}</strong> has been updated to <strong>{{ $status->label() }}</strong>.
    </p>

    @if ($status === \App\Enums\OrderStatus::Cancelled)
        <p class="mt-2 text-sm text-ink-soft">If you were charged anything in error, please contact us and we'll help right away.</p>
    @elseif ($status === \App\Enums\OrderStatus::Shipped)
        <p class="mt-2 text-sm text-ink-soft">Your order is on its way{{ $order->courier_name ? ' with '.$order->courier_name : '' }}.</p>
    @elseif ($status === \App\Enums\OrderStatus::Delivered)
        <p class="mt-2 text-sm text-ink-soft">We hope you enjoy your purchase!</p>
    @endif
</x-layouts.guest>
