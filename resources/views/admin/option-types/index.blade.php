@php($canEdit = auth('admin')->user()?->can('products.edit'))
<x-layouts.admin title="Options">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Option types</h1>
        @if ($canEdit)
            <a href="{{ route('admin.option-types.create') }}"
               class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                Add option type
            </a>
        @endif
    </div>

    <div class="mt-6">
        <x-admin.table :headers="['Name', 'Display style', 'Values', '']">
            @forelse ($optionTypes as $optionType)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $optionType->name }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $optionType->display_style }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $optionType->values_count }}</td>
                    <td class="px-4 py-3 text-right">
                        @if ($canEdit)
                            <a href="{{ route('admin.option-types.edit', $optionType) }}" class="text-link hover:underline">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-ink-soft">No option types yet.</td>
                </tr>
            @endforelse
        </x-admin.table>

        <div class="mt-6">
            {{ $optionTypes->links() }}
        </div>
    </div>
</x-layouts.admin>
