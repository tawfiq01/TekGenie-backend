@php
    $imagesJson = $product->images->map(fn ($image) => [
        'url' => $image->url('detail'),
        'cardUrl' => $image->url('card'),
        'altText' => $image->alt_text ?? $product->name,
        'optionValueId' => $image->option_value_id,
    ])->values();
    $mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();
@endphp
<x-layouts.app :title="$product->name">
    <nav class="text-sm text-ink-soft">
        <a href="{{ route('category.show', $product->category) }}" class="hover:text-ink">{{ $product->category->name }}</a>
    </nav>

    <div
        x-data="{
            variants: {{ Illuminate\Support\Js::from($variantsJson) }},
            images: {{ Illuminate\Support\Js::from($imagesJson) }},
            selected: {},
            activeImage: {{ Illuminate\Support\Js::from($mainImage?->url('detail')) }},
            activeTab: 'description',
            get currentVariant() {
                const ids = Object.values(this.selected).filter(v => v).map(Number).sort((a, b) => a - b);
                return this.variants.find(v => JSON.stringify(v.optionValueIds) === JSON.stringify(ids)) ?? null;
            },
            isAvailable(optionTypeId, optionValueId) {
                const testSelected = { ...this.selected, [optionTypeId]: optionValueId };
                const ids = Object.values(testSelected).filter(v => v).map(Number).sort((a, b) => a - b);
                return this.variants.some(v => v.isActive && v.optionValueIds.length === ids.length && ids.every(id => v.optionValueIds.includes(id)));
            },
            select(optionTypeId, valueId) {
                this.selected[optionTypeId] = valueId;
                const match = this.images.find(img => img.optionValueId === valueId);
                if (match) this.activeImage = match.url;
            },
            stockLabel() {
                const v = this.currentVariant;
                if (!v) return 'Select options';
                if (v.stock <= 0) return 'Out of stock';
                return 'In stock';
            },
        }"
        class="mt-4 grid grid-cols-1 gap-10 lg:grid-cols-2"
    >
        <div>
            <div class="aspect-square overflow-hidden rounded-[var(--radius-lg)] bg-bg-subtle">
                <template x-if="activeImage">
                    <img :src="activeImage" alt="{{ $product->name }}" class="h-full w-full object-cover">
                </template>
            </div>
            @if ($product->images->count() > 1)
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach ($product->images as $image)
                        <button type="button" @click="activeImage = {{ Illuminate\Support\Js::from($image->url('detail')) }}"
                                class="aspect-square overflow-hidden rounded-[var(--radius-sm)] border border-border">
                            <img src="{{ $image->url('thumbnail') }}" alt="{{ $image->alt_text }}" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <p class="text-sm text-ink-soft">{{ $product->category->name }}@if($product->brand) &middot; {{ $product->brand->name }}@endif</p>
            <h1 class="mt-1 text-2xl font-bold text-ink">{{ $product->name }}</h1>

            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-ink" x-text="currentVariant ? '৳' + currentVariant.price.toLocaleString() : '—'"></span>
                <template x-if="currentVariant?.compareAtPrice">
                    <span class="text-base text-muted line-through" x-text="'৳' + currentVariant.compareAtPrice.toLocaleString()"></span>
                </template>
            </div>

            <p class="mt-1 text-sm" :class="currentVariant && currentVariant.stock > 0 ? 'text-success' : 'text-danger'" x-text="stockLabel()"></p>

            @foreach ($product->optionTypes as $optionType)
                <div class="mt-4">
                    <h2 class="text-sm font-medium text-ink">{{ $optionType->name }}</h2>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($optionType->values as $value)
                            <button
                                type="button"
                                @click="select({{ $optionType->id }}, {{ $value->id }})"
                                :disabled="!isAvailable({{ $optionType->id }}, {{ $value->id }})"
                                :class="selected[{{ $optionType->id }}] === {{ $value->id }} ? 'border-ink bg-ink text-white' : 'border-border text-ink-soft'"
                                class="rounded-[var(--radius-pill)] border px-3 py-1.5 text-sm disabled:cursor-not-allowed disabled:opacity-40"
                            >{{ $value->value }}</button>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if ($product->short_description)
                <p class="mt-6 text-ink-soft">{{ $product->short_description }}</p>
            @endif

            <form method="POST" action="{{ route('cart.store') }}" class="mt-6 flex items-center gap-3" x-data="{ quantity: 1 }">
                @csrf
                <input type="hidden" name="product_variant_id" :value="currentVariant?.id">

                <div class="flex items-center rounded-[var(--radius-pill)] border border-border">
                    <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="px-3 py-2 text-ink-soft">&minus;</button>
                    <input type="number" name="quantity" x-model.number="quantity" min="1" class="w-12 border-0 bg-transparent text-center text-sm focus:outline-none">
                    <button type="button" @click="quantity = quantity + 1" class="px-3 py-2 text-ink-soft">&plus;</button>
                </div>

                <button
                    type="submit"
                    :disabled="!currentVariant || currentVariant.stock < 1"
                    class="flex-1 rounded-[var(--radius-pill)] bg-ink px-6 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Add to cart
                </button>
            </form>
            @error('quantity')
                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
            @enderror
            @error('product_variant_id')
                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
            @enderror

            <div class="mt-8 border-t border-border">
                <div class="flex gap-6 border-b border-border text-sm">
                    <button type="button" @click="activeTab = 'description'" :class="activeTab === 'description' ? 'border-b-2 border-ink text-ink' : 'text-ink-soft'" class="py-3">Description</button>
                    @if ($product->attributes->isNotEmpty())
                        <button type="button" @click="activeTab = 'specs'" :class="activeTab === 'specs' ? 'border-b-2 border-ink text-ink' : 'text-ink-soft'" class="py-3">Specifications</button>
                    @endif
                </div>

                <div x-show="activeTab === 'description'" class="prose prose-sm mt-4 max-w-none text-ink-soft">
                    {!! $product->description !!}
                </div>

                @if ($product->attributes->isNotEmpty())
                    <div x-show="activeTab === 'specs'" x-cloak class="mt-4">
                        <dl class="divide-y divide-border text-sm">
                            @foreach ($product->attributes as $attribute)
                                <div class="flex justify-between py-2">
                                    <dt class="text-ink-soft">{{ $attribute->label }}</dt>
                                    <dd class="text-ink">{{ $attribute->value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($relatedProducts->isNotEmpty())
        <div class="mt-16">
            <h2 class="text-xl font-bold text-ink">Related products</h2>
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($relatedProducts as $related)
                    <x-storefront.product-card :product="$related" />
                @endforeach
            </div>
        </div>
    @endif
</x-layouts.app>
