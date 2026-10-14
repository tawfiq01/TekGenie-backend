<x-layouts.admin title="Pages">
    <h1 class="text-2xl font-bold text-ink">Pages</h1>

    <div class="mt-6">
        <x-admin.table :headers="['Title', 'Slug', 'Status', '']">
            @foreach ($pages as $page)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $page->title }}</td>
                    <td class="px-4 py-3 text-ink-soft">/page/{{ $page->slug }}</td>
                    <td class="px-4 py-3">
                        @if ($page->is_published)
                            <span class="rounded-[var(--radius-pill)] bg-success-soft px-2 py-1 text-xs text-success">Published</span>
                        @else
                            <span class="rounded-[var(--radius-pill)] bg-bg-subtle px-2 py-1 text-xs text-muted">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-link hover:underline">Edit</a>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    </div>
</x-layouts.admin>
