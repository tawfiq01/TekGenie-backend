<x-layouts.admin title="Home page sections">
    <h1 class="text-2xl font-bold text-ink">Home page sections</h1>
    <p class="mt-1 text-sm text-ink-soft">
        Enable, disable, rename and reorder the sections shown on the home page. Hero/promo content
        comes from <a href="{{ route('admin.banners.index') }}" class="text-link hover:underline">Banners</a>.
    </p>

    <form method="PUT" action="{{ route('admin.home-sections.update') }}" class="mt-6 max-w-2xl">
        @csrf
        @method('PUT')

        <x-admin.table :headers="['Type', 'Title', 'Order', 'Active']">
            @foreach ($sections as $i => $section)
                <tr>
                    <input type="hidden" name="sections[{{ $i }}][id]" value="{{ $section->id }}">
                    <td class="px-4 py-3 text-ink-soft">{{ $section->type->defaultTitle() }}</td>
                    <td class="px-4 py-3">
                        <input type="text" name="sections[{{ $i }}][title]" value="{{ old("sections.$i.title", $section->title) }}"
                               class="w-full rounded-[var(--radius-sm)] border border-border px-2 py-1 text-sm">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" name="sections[{{ $i }}][sort_order]" value="{{ old("sections.$i.sort_order", $section->sort_order) }}"
                               class="w-20 rounded-[var(--radius-sm)] border border-border px-2 py-1 text-sm">
                    </td>
                    <td class="px-4 py-3">
                        <input type="checkbox" name="sections[{{ $i }}][is_active]" value="1" {{ old("sections.$i.is_active", $section->is_active) ? 'checked' : '' }}
                               class="rounded border-border">
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save sections
        </button>
    </form>
</x-layouts.admin>
