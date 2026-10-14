@php($canEdit = auth('admin')->user()?->can('content.manage'))
<x-layouts.admin title="Banners">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Banners</h1>
        @if ($canEdit)
            <a href="{{ route('admin.banners.create') }}"
               class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                Add banner
            </a>
        @endif
    </div>

    <div class="mt-6">
        <x-admin.table :headers="['Image', 'Placement', 'Title', 'Schedule', 'Status', '']">
            @forelse ($banners as $banner)
                <tr>
                    <td class="px-4 py-3">
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_desktop_path) }}" alt="" class="h-10 w-16 rounded-[var(--radius-sm)] object-cover">
                    </td>
                    <td class="px-4 py-3 text-ink-soft">{{ ucfirst($banner->placement) }}</td>
                    <td class="px-4 py-3 text-ink">{{ $banner->title ?? '—' }}</td>
                    <td class="px-4 py-3 text-ink-soft">
                        {{ $banner->starts_at?->format('Y-m-d') ?? '—' }} – {{ $banner->ends_at?->format('Y-m-d') ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($banner->is_active)
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">Active</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-bg-subtle px-2 py-1 text-xs text-muted">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if ($canEdit)
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="text-link hover:underline">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-ink-soft">No banners yet.</td>
                </tr>
            @endforelse
        </x-admin.table>
    </div>
</x-layouts.admin>
