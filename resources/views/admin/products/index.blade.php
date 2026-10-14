@php($canEdit = auth('admin')->user()?->can('products.edit'))
<x-layouts.admin title="Products">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Products</h1>
        @if ($canEdit)
            <a href="{{ route('admin.products.create') }}"
               class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                Add product
            </a>
        @endif
    </div>

    <x-admin.filters :action="route('admin.products.index')">
        <x-admin.select label="Status" name="status" :options="['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived']"
                        :selected="request('status')" placeholder="Any" />
        <x-admin.select label="Category" name="category_id" :options="$categories->pluck('name', 'id')"
                        :selected="request('category_id')" placeholder="Any" />
    </x-admin.filters>

    <div class="mt-6">
        <x-admin.table :headers="['Name', 'Category', 'Price', 'Stock', 'Status', '']">
            @forelse ($products as $product)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 text-ink-soft">
                        @if ($product->min_price !== null)
                            {{ $product->min_price == $product->max_price ? '৳'.$product->min_price : '৳'.$product->min_price.' – ৳'.$product->max_price }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($product->in_stock)
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">In stock</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-bg-subtle px-2 py-1 text-xs text-muted">Out of stock</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-ink-soft">{{ ucfirst($product->status->value) }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($canEdit)
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-link hover:underline">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-ink-soft">No products yet.</td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.admin>
