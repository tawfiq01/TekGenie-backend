<x-layouts.app title="Your wishlist">
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-ink">Your wishlist</h1>

        @if (session('status'))
            <p class="mt-4 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">{{ session('status') }}</p>
        @endif

        <div class="mt-6 space-y-3">
            @forelse ($items as $item)
                @php($product = $item->product)
                <div class="flex items-center justify-between rounded-[var(--radius-md)] border border-border p-4">
                    <div>
                        <a href="{{ route('product.show', $product) }}" class="font-semibold text-ink hover:underline">{{ $product->name }}</a>
                        <p class="text-sm text-ink-soft">
                            @if ($product->min_price !== null)
                                ৳{{ $product->min_price }}{{ $product->max_price && $product->max_price != $product->min_price ? ' – ৳'.$product->max_price : '' }}
                            @endif
                            — {{ $product->in_stock ? 'In stock' : 'Out of stock' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <a href="{{ route('product.show', $product) }}" class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 font-semibold text-white hover:bg-ink-soft">
                            View product
                        </a>
                        <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger hover:underline">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-soft">Your wishlist is empty.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
