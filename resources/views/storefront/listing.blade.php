<x-layouts.app :title="$heading">
    <h1 class="text-2xl font-bold text-ink">{{ $heading }}</h1>

    <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-[240px_1fr]">
        <aside class="space-y-6">
            <form method="GET">
                @if (isset($searchTerm))
                    <input type="hidden" name="q" value="{{ $searchTerm }}">
                @endif

                <div>
                    <h2 class="text-sm font-semibold text-ink">Category</h2>
                    <div class="mt-2 space-y-1">
                        @foreach ($categories as $cat)
                            <label class="flex items-center gap-2 text-sm text-ink-soft">
                                <input type="radio" name="category_id" value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'checked' : '' }}>
                                {{ $cat->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                @if ($brands->isNotEmpty())
                    <div class="mt-4">
                        <h2 class="text-sm font-semibold text-ink">Brand</h2>
                        <div class="mt-2 space-y-1">
                            @foreach ($brands as $brand)
                                <label class="flex items-center gap-2 text-sm text-ink-soft">
                                    <input type="radio" name="brand_id" value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'checked' : '' }}>
                                    {{ $brand->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <h2 class="text-sm font-semibold text-ink">Price (৳)</h2>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full rounded-[var(--radius-sm)] border border-border px-2 py-1 text-sm">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full rounded-[var(--radius-sm)] border border-border px-2 py-1 text-sm">
                    </div>
                </div>

                @foreach ($optionTypes as $optionType)
                    @if ($optionType->values->isNotEmpty())
                        <div class="mt-4">
                            <h2 class="text-sm font-semibold text-ink">{{ $optionType->name }}</h2>
                            <div class="mt-2 space-y-1">
                                @foreach ($optionType->values as $value)
                                    <label class="flex items-center gap-2 text-sm text-ink-soft">
                                        <input type="checkbox" name="option_value_ids[]" value="{{ $value->id }}"
                                               {{ in_array($value->id, (array) request('option_value_ids', [])) ? 'checked' : '' }}>
                                        {{ $value->value }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                <label class="mt-4 flex items-center gap-2 text-sm text-ink-soft">
                    <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }}>
                    In stock only
                </label>

                <button type="submit" class="mt-4 w-full rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
                    Apply filters
                </button>
            </form>
        </aside>

        <div>
            <div class="flex items-center justify-end">
                <form method="GET">
                    @if (isset($searchTerm))
                        <input type="hidden" name="q" value="{{ $searchTerm }}">
                    @endif
                    <select name="sort" onchange="this.form.submit()" class="rounded-[var(--radius-sm)] border border-border px-3 py-1.5 text-sm">
                        @if (isset($searchTerm) && $searchTerm !== '')
                            <option value="relevance" @selected(request('sort', 'relevance') === 'relevance')>Relevance</option>
                        @endif
                        <option value="newest" @selected(request('sort') === 'newest' || !request('sort'))>Newest</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
                        <option value="best_selling" @selected(request('sort') === 'best_selling')>Best selling</option>
                    </select>
                </form>
            </div>

            @if ($products === null)
                <p class="mt-6 text-ink-soft">Type a search term above to find products.</p>
            @elseif ($products->isEmpty())
                <div class="mt-6 rounded-[var(--radius-lg)] border border-border p-8 text-center">
                    <p class="text-ink-soft">No products match these filters.</p>
                    <a href="{{ url()->current() }}" class="mt-2 inline-block text-sm text-link hover:underline">Clear filters</a>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        @foreach ($categories->take(5) as $cat)
                            <a href="{{ route('category.show', $cat) }}" class="rounded-[var(--radius-pill)] border border-border px-3 py-1 text-xs text-ink-soft hover:bg-bg-subtle">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($products as $product)
                        <x-storefront.product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
