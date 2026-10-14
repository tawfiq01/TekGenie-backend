<x-layouts.admin title="Add variant">
    <h1 class="text-2xl font-bold text-ink">Add variant — {{ $product->name }}</h1>

    <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="mt-6 max-w-xl">
        @csrf

        <x-admin.form-section>
            @foreach ($optionTypes as $optionType)
                <div>
                    <label for="option_value_{{ $optionType->id }}" class="block text-sm font-medium text-ink">{{ $optionType->name }}</label>
                    <select id="option_value_{{ $optionType->id }}" name="option_value_ids[]" required
                            class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
                        <option value="">— Choose {{ strtolower($optionType->name) }} —</option>
                        @foreach ($optionType->values as $value)
                            <option value="{{ $value->id }}">{{ $value->value }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            @error('option_value_ids')
                <p class="text-sm text-danger">{{ $message }}</p>
            @enderror

            <x-admin.input label="SKU" name="sku" :value="old('sku')" required />
            <x-admin.input label="Barcode (optional)" name="barcode" :value="old('barcode')" />
            <x-admin.input label="Price (৳)" name="price" type="number" step="0.01" :value="old('price')" required />
            <x-admin.input label="Compare-at price (optional, must be higher than price)" name="compare_at_price" type="number" step="0.01" :value="old('compare_at_price')" />
            <x-admin.input label="Initial stock" name="stock" type="number" :value="old('stock', 0)" required />
            <x-admin.input label="Low-stock threshold (optional)" name="low_stock_threshold" type="number" :value="old('low_stock_threshold')" />
            <x-admin.input label="Weight in grams (optional)" name="weight_grams" type="number" :value="old('weight_grams')" />
            <x-admin.checkbox label="Active" name="is_active" :checked="old('is_active', true)" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Create variant
        </button>
    </form>
</x-layouts.admin>
