@props(['product', 'lazy' => true])

@php($mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first())

<a href="{{ route('product.show', $product) }}" class="group block">
    <div class="aspect-square overflow-hidden rounded-[var(--radius-md)] bg-bg-subtle">
        @if ($mainImage)
            <img
                src="{{ $mainImage->url('card') }}"
                srcset="{{ $mainImage->srcset(['thumbnail', 'card']) }}"
                sizes="(min-width: 1024px) 23vw, (min-width: 640px) 31vw, 45vw"
                width="400" height="400"
                loading="{{ $lazy ? 'lazy' : 'eager' }}"
                alt="{{ $mainImage->alt_text ?? $product->name }}"
                class="h-full w-full object-cover transition group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-sm text-muted">No image</div>
        @endif
    </div>

    <h3 class="mt-2 text-sm font-medium text-ink group-hover:text-link">{{ $product->name }}</h3>

    <p class="mt-1 text-base font-bold text-ink">
        @if ($product->min_price === null)
            —
        @elseif ((float) $product->min_price === (float) $product->max_price)
            ৳{{ number_format((float) $product->min_price) }}
        @else
            ৳{{ number_format((float) $product->min_price) }} – ৳{{ number_format((float) $product->max_price) }}
        @endif
    </p>

    @unless ($product->in_stock)
        <p class="mt-0.5 text-xs text-muted">Out of stock</p>
    @endunless
</a>
