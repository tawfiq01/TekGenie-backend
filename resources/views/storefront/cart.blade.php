@php($blockingIssue = $items->first(fn ($item) => ! $item->variant->is_active || $item->variant->stock < 1))

<x-layouts.app title="Your cart">
    <h1 class="text-2xl font-bold text-ink">Your cart</h1>

    @if (session('status'))
        <p class="mt-4 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">{{ session('status') }}</p>
    @endif

    @if ($items->isEmpty())
        <p class="mt-6 text-ink-soft">Your cart is empty. <a href="{{ route('shop.index') }}" class="text-link hover:underline">Start shopping</a>.</p>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($items as $item)
                @php($unavailable = ! $item->variant->is_active || $item->variant->stock < 1)
                @php($priceChanged = (float) $item->variant->price !== (float) $item->price_at_add)

                <div class="flex items-center gap-4 rounded-[var(--radius-md)] border border-border p-4 {{ $unavailable ? 'opacity-60' : '' }}">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-[var(--radius-sm)] bg-bg-subtle">
                        @php($image = $item->variant->product->images->first())
                        @if ($image)
                            <img src="{{ $image->url('thumbnail') }}" alt="" class="h-full w-full object-cover">
                        @endif
                    </div>

                    <div class="flex-1">
                        <p class="font-medium text-ink">{{ $item->variant->product->name }}</p>
                        <p class="text-sm text-ink-soft">SKU {{ $item->variant->sku }} · ৳{{ $item->variant->price }}</p>

                        @if ($unavailable)
                            <p class="mt-1 text-sm text-danger">
                                {{ ! $item->variant->is_active ? 'No longer available' : 'Out of stock' }} — remove this item to continue to checkout.
                            </p>
                        @elseif ($priceChanged)
                            <p class="mt-1 text-sm text-warning">Price changed from ৳{{ $item->price_at_add }} to ৳{{ $item->variant->price }}.</p>
                        @endif

                        <form method="POST" action="{{ route('cart.items.update', $item) }}" class="mt-2 flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <label for="qty-{{ $item->id }}" class="text-sm text-ink-soft">Qty</label>
                            <input id="qty-{{ $item->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                   class="w-16 rounded-[var(--radius-sm)] border border-border px-2 py-1 text-sm">
                            <button type="submit" class="text-sm text-link hover:underline">Update</button>
                        </form>
                    </div>

                    <div class="text-right">
                        <p class="font-semibold text-ink">৳{{ number_format($item->variant->price * $item->quantity, 2) }}</p>
                        <form method="POST" action="{{ route('cart.items.destroy', $item) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-danger hover:underline">Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-end">
            @if ($blockingIssue)
                <button type="button" disabled class="cursor-not-allowed rounded-[var(--radius-pill)] bg-bg-subtle px-6 py-3 text-sm font-semibold text-muted">
                    Remove unavailable items to continue
                </button>
            @else
                <a href="{{ route('checkout.show') }}" class="rounded-[var(--radius-pill)] bg-ink px-6 py-3 text-sm font-semibold text-white hover:bg-ink-soft">
                    Proceed to checkout
                </a>
            @endif
        </div>
    @endif
</x-layouts.app>
