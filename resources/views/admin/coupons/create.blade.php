<x-layouts.admin title="Add coupon">
    <h1 class="text-2xl font-bold text-ink">Add coupon</h1>

    <form method="POST" action="{{ route('admin.coupons.store') }}" class="mt-6 max-w-xl">
        @csrf
        <x-admin.form-section>
            <x-admin.input label="Code" name="code" :value="old('code')" required />
            <x-admin.input label="Description (optional)" name="description" :value="old('description')" />
            <x-admin.select label="Type" name="type" :options="['percentage' => 'Percentage', 'fixed' => 'Fixed amount', 'free_delivery' => 'Free delivery']" :selected="old('type')" placeholder="— Choose —" />
            <x-admin.input label="Value (% or ৳, ignored for free delivery)" name="value" type="number" step="0.01" :value="old('value', 0)" required />
            <x-admin.input label="Max discount cap (৳, percentage only)" name="max_discount" type="number" step="0.01" :value="old('max_discount')" />
            <x-admin.input label="Minimum order subtotal (৳, optional)" name="min_subtotal" type="number" step="0.01" :value="old('min_subtotal')" />
            <x-admin.input label="Starts at (optional)" name="starts_at" type="datetime-local" :value="old('starts_at')" />
            <x-admin.input label="Ends at (optional)" name="ends_at" type="datetime-local" :value="old('ends_at')" />
            <x-admin.input label="Total usage limit (optional)" name="usage_limit" type="number" :value="old('usage_limit')" />
            <x-admin.input label="Per-customer limit (optional)" name="per_customer_limit" type="number" :value="old('per_customer_limit')" />

            <x-admin.select label="Applies to" name="applies_to" :options="['all' => 'All products', 'categories' => 'Selected categories', 'products' => 'Selected products']" :selected="old('applies_to', 'all')" />

            <fieldset>
                <legend class="text-sm font-medium text-ink">Categories (if restricted)</legend>
                <div class="mt-2 max-h-40 space-y-1 overflow-y-auto">
                    @foreach ($categories as $category)
                        <label class="flex items-center gap-2 text-sm text-ink-soft">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="rounded border-border">
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
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="rounded border-border">
                            {{ $product->name }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Create coupon
        </button>
    </form>
</x-layouts.admin>
