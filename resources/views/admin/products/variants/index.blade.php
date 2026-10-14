@php($canAddMore = $product->optionTypes->isNotEmpty() || $variants->isEmpty())
<x-layouts.admin title="Variants">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Variants — {{ $product->name }}</h1>
        @if ($canAddMore)
            <a href="{{ route('admin.products.variants.create', $product) }}"
               class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                Add variant
            </a>
        @endif
    </div>

    <a href="{{ route('admin.products.edit', $product) }}" class="mt-2 inline-block text-sm text-link hover:underline">&larr; Back to product</a>

    <div class="mt-6">
        <x-admin.table :headers="['SKU', 'Options', 'Price', 'Stock', 'Status', '']">
            @forelse ($variants as $variant)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $variant->sku }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $variant->optionValues->pluck('value')->join(' / ') ?: '—' }}</td>
                    <td class="px-4 py-3 text-ink-soft">
                        ৳{{ $variant->price }}
                        @if ($variant->hasGenuineCompareAtPrice())
                            <span class="text-muted line-through">৳{{ $variant->compare_at_price }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-ink-soft">{{ $variant->stock }}</td>
                    <td class="px-4 py-3">
                        @if ($variant->is_active)
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">Active</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-bg-subtle px-2 py-1 text-xs text-muted">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.products.variants.edit', [$product, $variant]) }}" class="text-link hover:underline">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-ink-soft">No variants yet.</td>
                </tr>
            @endforelse
        </x-admin.table>
    </div>
</x-layouts.admin>
