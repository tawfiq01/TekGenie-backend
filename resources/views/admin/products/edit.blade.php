<x-layouts.admin title="Edit product">
    <h1 class="text-2xl font-bold text-ink">Edit product — {{ $product->name }}</h1>

    @php($additionalCategoryIds = old('category_ids', $product->categories->pluck('id')->reject(fn ($id) => $id === $product->category_id)->all()))

    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="mt-6 max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <x-admin.form-section title="Basics">
            <x-admin.input label="Name" name="name" :value="old('name', $product->name)" required />
            <x-admin.input label="Slug" name="slug" :value="old('slug', $product->slug)" />
            <x-admin.select label="Primary category" name="category_id" :options="$categories->pluck('name', 'id')"
                            :selected="old('category_id', $product->category_id)" placeholder="— Choose —" />
            <x-admin.select label="Brand (optional)" name="brand_id" :options="$brands->pluck('name', 'id')"
                            :selected="old('brand_id', $product->brand_id)" placeholder="— None —" />

            <fieldset>
                <legend class="text-sm font-medium text-ink">Additional categories</legend>
                <div class="mt-2 space-y-1">
                    @foreach ($categories as $category)
                        @if ($category->id !== (int) old('category_id', $product->category_id))
                            <label class="flex items-center gap-2 text-sm text-ink-soft">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                       {{ in_array($category->id, $additionalCategoryIds) ? 'checked' : '' }}
                                       class="rounded border-border">
                                {{ $category->name }}
                            </label>
                        @endif
                    @endforeach
                </div>
            </fieldset>

            <x-admin.textarea label="Short description" name="short_description" :value="old('short_description', $product->short_description)" :rows="2" />
            <x-admin.textarea label="Full description" name="description" :value="old('description', $product->description)" :rows="6" />
        </x-admin.form-section>

        <x-admin.form-section title="Status">
            <x-admin.select label="Status" name="status" :options="['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived']"
                            :selected="old('status', $product->status->value)" />
            <x-admin.checkbox label="Featured" name="is_featured" :checked="old('is_featured', $product->is_featured)" />
        </x-admin.form-section>

        <x-admin.form-section title="SEO">
            <x-admin.input label="Meta title" name="meta_title" :value="old('meta_title', $product->meta_title)" />
            <x-admin.textarea label="Meta description" name="meta_description" :value="old('meta_description', $product->meta_description)" :rows="2" />
        </x-admin.form-section>

        <button type="submit"
                class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>

    <div class="mt-10 max-w-2xl">
        <h2 class="text-lg font-semibold text-ink">Variants</h2>
        <p class="mt-1 text-sm text-ink-soft">
            {{ $product->variants->count() }} variant(s)
            @if ($product->optionTypes->isNotEmpty())
                — varies by {{ $product->optionTypes->pluck('name')->join(', ') }}
            @endif
        </p>
        <a href="{{ route('admin.products.variants.index', $product) }}"
           class="mt-2 inline-block rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
            Manage variants
        </a>
    </div>

    <div class="mt-10 max-w-2xl">
        <h2 class="text-lg font-semibold text-ink">Images</h2>

        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach ($product->images as $image)
                <div class="rounded-[var(--radius-md)] border border-border p-2">
                    <img src="{{ $image->url('card') }}" alt="{{ $image->alt_text }}" class="aspect-square w-full rounded-[var(--radius-sm)] object-cover">
                    <p class="mt-1 text-xs text-ink-soft">
                        {{ $image->is_main ? 'Main' : 'Position '.($image->sort_order + 1) }}
                        @if ($image->optionValue)
                            · {{ $image->optionValue->value }}
                        @endif
                    </p>
                    <div class="mt-1 flex items-center justify-between text-xs">
                        @unless ($image->is_main)
                            <form method="POST" action="{{ route('admin.products.images.set-main', $image) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="text-link hover:underline">Set main</button>
                            </form>
                        @else
                            <span></span>
                        @endunless
                        <form method="POST" action="{{ route('admin.products.images.destroy', $image) }}"
                              onsubmit="return confirm('Remove this image?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger hover:underline">Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-3">
            @csrf
            <x-admin.input label="Add image" name="image" type="file" accept="image/jpeg,image/png,image/webp" />
            @if ($product->optionTypes->isNotEmpty())
                <x-admin.select label="Link to option value (optional)" name="option_value_id"
                                :options="$product->optionTypes->flatMap->values->pluck('value', 'id')" placeholder="— None —" />
            @endif
            <x-admin.input label="Alt text" name="alt_text" />
            <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
                Upload
            </button>
        </form>
    </div>

    <div class="mt-10 max-w-2xl">
        <h2 class="text-lg font-semibold text-ink">Specifications</h2>

        <form method="POST" action="{{ route('admin.products.attributes.update', $product) }}" class="mt-4 space-y-3">
            @csrf
            @method('PUT')

            @php($specs = old('attributes', $product->attributes->map(fn ($a) => ['label' => $a->label, 'value' => $a->value])->all()))
            @php($specs = count($specs) ? $specs : [['label' => '', 'value' => '']])

            @foreach ($specs as $i => $spec)
                <div class="flex items-center gap-3">
                    <input type="text" name="attributes[{{ $i }}][label]" value="{{ $spec['label'] }}" placeholder="Label (e.g. Battery)"
                           class="w-1/3 rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    <input type="text" name="attributes[{{ $i }}][value]" value="{{ $spec['value'] }}" placeholder="Value (e.g. 30 h)"
                           class="flex-1 rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                </div>
            @endforeach

            <p class="text-xs text-ink-soft">Leave a row blank to remove it. Add more rows by editing the URL-free form below in a future pass.</p>

            <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
                Save specifications
            </button>
        </form>
    </div>

    <div class="mt-10 max-w-2xl">
        <h2 class="text-lg font-semibold text-ink">Related products</h2>

        <form method="POST" action="{{ route('admin.products.related.update', $product) }}" class="mt-4">
            @csrf
            @method('PUT')

            @php($relatedIds = old('related_product_ids', $product->relatedProducts->pluck('id')->all()))

            <div class="max-h-64 space-y-1 overflow-y-auto rounded-[var(--radius-md)] border border-border p-3">
                @foreach ($otherProducts as $other)
                    <label class="flex items-center gap-2 text-sm text-ink-soft">
                        <input type="checkbox" name="related_product_ids[]" value="{{ $other->id }}"
                               {{ in_array($other->id, $relatedIds) ? 'checked' : '' }}
                               class="rounded border-border">
                        {{ $other->name }}
                    </label>
                @endforeach
            </div>

            <button type="submit" class="mt-3 rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
                Save related products
            </button>
        </form>
    </div>
</x-layouts.admin>
