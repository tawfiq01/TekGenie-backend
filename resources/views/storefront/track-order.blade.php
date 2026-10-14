<x-layouts.app title="Track your order">
    <div class="mx-auto max-w-xl">
        <h1 class="text-2xl font-bold text-ink">Track your order</h1>
        <p class="mt-2 text-sm text-ink-soft">Enter your order number and the mobile number you used when ordering.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-[var(--radius-sm)] bg-danger-soft px-4 py-3 text-sm text-danger">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('orders.track.lookup') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="order_number" class="block text-sm font-medium text-ink">Order number</label>
                <input id="order_number" name="order_number" type="text" value="{{ old('order_number') }}" required class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-ink">Mobile number</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full rounded-[var(--radius-pill)] bg-ink px-6 py-3 text-sm font-semibold text-white hover:bg-ink-soft">
                Track order
            </button>
        </form>

        @isset($order)
            <div class="mt-8 rounded-[var(--radius-md)] border border-border p-4">
                <h2 class="font-semibold text-ink">{{ $order->order_number }} — {{ $order->status->label() }}</h2>

                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between text-ink-soft">
                            <span>{{ $item->product_name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }} × {{ $item->quantity }}</span>
                            <span>৳{{ $item->line_total }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-3 text-sm font-semibold text-ink">Total: ৳{{ $order->grand_total }}</p>
                <p class="mt-2 text-sm text-ink-soft">Delivering to: {{ $maskedAddress }}, {{ $order->district_name }}</p>

                @if ($order->statusHistories->isNotEmpty())
                    <ul class="mt-4 space-y-1 border-t border-border pt-3 text-sm text-ink-soft">
                        @foreach ($order->statusHistories as $history)
                            <li>{{ ucfirst($history->to_status) }} — {{ $history->created_at->format('Y-m-d H:i') }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endisset
    </div>
</x-layouts.app>
