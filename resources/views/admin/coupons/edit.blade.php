<x-layouts.admin title="Edit coupon">
    <h1 class="text-2xl font-bold text-ink">Edit coupon — {{ $coupon->code }}</h1>
    <p class="mt-1 text-sm text-ink-soft">Used {{ $coupon->used_count }} time(s){{ $coupon->usage_limit ? ' of '.$coupon->usage_limit : '' }} — total discount given: ৳{{ number_format((float) $totalDiscount, 2) }}</p>

    @php($currentCategoryIds = $coupon->categories->pluck('id')->all())
    @php($currentProductIds = $coupon->products->pluck('id')->all())

    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')
        <x-admin.form-section>
            <x-admin.input label="Code" name="code" :value="old('code', $coupon->code)" required />
            <x-admin.input label="Description (optional)" name="description" :value="old('description', $coupon->description)" />
            <x-admin.select label="Type" name="type" :options="['percentage' => 'Percentage', 'fixed' => 'Fixed amount', 'free_delivery' => 'Free delivery']" :selected="old('type', $coupon->type->value)" />
            <x-admin.input label="Value (% or ৳, ignored for free delivery)" name="value" type="number" step="0.01" :value="old('value', $coupon->value)" required />
            <x-admin.input label="Max discount cap (৳, percentage only)" name="max_discount" type="number" step="0.01" :value="old('max_discount', $coupon->max_discount)" />
            <x-admin.input label="Minimum order subtotal (৳, optional)" name="min_subtotal" type="number" step="0.01" :value="old('min_subtotal', $coupon->min_subtotal)" />
            <x-admin.input label="Starts at (optional)" name="starts_at" type="datetime-local" :value="old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i'))" />
            <x-admin.input label="Ends at (optional)" name="ends_at" type="datetime-local" :value="old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i'))" />
            <x-admin.input label="Total usage limit (optional)" name="usage_limit" type="number" :value="old('usage_limit', $coupon->usage_limit)" />
            <x-admin.input label="Per-customer limit (optional)" name="per_customer_limit" type="number" :value="old('per_customer_limit', $coupon->per_customer_limit)" />

            <x-admin.select label="Applies to" name="applies_to" :options="['all' => 'All products', 'categories' => 'Selected categories', 'products' => 'Selected products']" :selected="old('applies_to', $coupon->applies_to)" />

            <fieldset>
                <legend class="text-sm font-medium text-ink">Categories (if restricted)</legend>
                <div class="mt-2 max-h-40 space-y-1 overflow-y-auto">
                    @foreach ($categories as $category)
                        <label class="flex items-center gap-2 text-sm text-ink-soft">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ in_array($category->id, $currentCategoryIds) ? 'checked' : '' }} class="rounded border-border">
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset>
                <legend class="text-sm font-medium text-ink">Products (if restricted)</legend>
                <div class="mt-2 max-h-40 space-y-1 overflow-y-auto">
                    @foreach ($products as $product)
                        <label class="flex items-center gap-2 text-sm text-ink-soft">
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" {{ in_array($product->id, $currentProductIds) ? 'checked' : '' }} class="rounded border-border">
                            {{ $product->name }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <x-admin.checkbox label="Active" name="is_active" :checked="$coupon->is_active" />
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>
</x-layouts.admin>
