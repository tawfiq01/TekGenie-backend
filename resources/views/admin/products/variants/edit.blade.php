<x-layouts.admin title="Edit variant">
    <h1 class="text-2xl font-bold text-ink">Edit variant — {{ $variant->sku }}</h1>
    <p class="mt-1 text-sm text-ink-soft">{{ $product->name }}{{ $variant->optionValues->isNotEmpty() ? ' — '.$variant->optionValues->pluck('value')->join(' / ') : '' }}</p>

    <form method="POST" action="{{ route('admin.products.variants.update', [$product, $variant]) }}" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')

        <x-admin.form-section>
            <x-admin.input label="SKU" name="sku" :value="old('sku', $variant->sku)" required />
            <x-admin.input label="Barcode (optional)" name="barcode" :value="old('barcode', $variant->barcode)" />
            <x-admin.input label="Price (৳)" name="price" type="number" step="0.01" :value="old('price', $variant->price)" required />
            <x-admin.input label="Compare-at price (optional, must be higher than price)" name="compare_at_price" type="number" step="0.01" :value="old('compare_at_price', $variant->compare_at_price)" />
            <x-admin.input label="Low-stock threshold (optional)" name="low_stock_threshold" type="number" :value="old('low_stock_threshold', $variant->low_stock_threshold)" />
            <x-admin.input label="Weight in grams (optional)" name="weight_grams" type="number" :value="old('weight_grams', $variant->weight_grams)" />
            <x-admin.checkbox label="Active" name="is_active" :checked="old('is_active', $variant->is_active)" />

            <p class="text-sm text-ink-soft">
                Current stock: <strong>{{ $variant->stock }}</strong> — stock is adjusted from the Inventory screen, not here.
            </p>
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>
</x-layouts.admin>
