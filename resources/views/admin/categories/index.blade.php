@php($canEdit = auth('admin')->user()?->can('products.edit'))
<x-layouts.admin title="Categories">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Categories</h1>
        @if ($canEdit)
            <a href="{{ route('admin.categories.create') }}"
               class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                Add category
            </a>
        @endif
    </div>

    <div class="mt-6">
        <x-admin.table :headers="['Name', 'Parent', 'Sort', 'Status', '']">
            @forelse ($categories as $category)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $category->name }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $category->parent?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $category->sort_order }}</td>
                    <td class="px-4 py-3">
                        @if ($category->is_active)
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">Active</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-bg-subtle px-2 py-1 text-xs text-muted">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if ($canEdit)
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-link hover:underline">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-ink-soft">No categories yet.</td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-6">
            {{ $categories->links() }}
        </div>
    </div>
</x-layouts.admin>
