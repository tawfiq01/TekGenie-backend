<x-layouts.app title="My orders">
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-ink">My orders</h1>

        <div class="mt-6 space-y-3">
            @forelse ($orders as $order)
                <a href="{{ route('checkout.confirmation', $order) }}" class="block rounded-[var(--radius-md)] border border-border p-4 hover:bg-bg-subtle">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-ink">{{ $order->order_number }}</p>
                            <p class="text-sm text-ink-soft">{{ $order->placed_at->format('Y-m-d') }} — {{ $order->status->label() }}</p>
                        </div>
                        <p class="text-sm font-medium text-ink">৳{{ $order->grand_total }}</p>
                    </div>
                </a>
            @empty
                <p class="text-sm text-ink-soft">You haven't placed any orders yet.</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</x-layouts.app>
