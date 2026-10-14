<x-layouts.admin title="Edit option type">
    <h1 class="text-2xl font-bold text-ink">Edit option type — {{ $optionType->name }}</h1>

    <form method="POST" action="{{ route('admin.option-types.update', $optionType) }}" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')

        <x-admin.form-section>
            <x-admin.input label="Name" name="name" :value="old('name', $optionType->name)" required />
            <x-admin.select
                label="Display style"
                name="display_style"
                :options="['chip' => 'Chip', 'swatch' => 'Swatch', 'dropdown' => 'Dropdown']"
                :selected="old('display_style', $optionType->display_style)"
            />
            <x-admin.input label="Sort order" name="sort_order" type="number" :value="old('sort_order', $optionType->sort_order)" />
        </x-admin.form-section>

        <button type="submit"
                class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save changes
        </button>
    </form>

    <div class="mt-10 max-w-xl">
        <h2 class="text-lg font-semibold text-ink">Values</h2>

        <x-admin.table :headers="['Value', 'Swatch', 'Sort', '']">
            @forelse ($optionType->values as $value)
                <tr>
                    <td class="px-4 py-3 text-ink">{{ $value->value }}</td>
                    <td class="px-4 py-3">
                        @if ($value->swatch_hex)
                            <span class="inline-block h-5 w-5 rounded-full border border-border" style="background-color: {{ $value->swatch_hex }}"></span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-ink-soft">{{ $value->sort_order }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.option-values.destroy', $value) }}"
                              onsubmit="return confirm('Remove this value? This only works if no variant uses it.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-danger hover:underline">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-ink-soft">No values yet.</td>
                </tr>
            @endforelse
        </x-admin.table>

        <form method="POST" action="{{ route('admin.option-values.store', $optionType) }}" class="mt-4 flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label for="value" class="block text-sm font-medium text-ink">New value</label>
                <input id="value" name="value" type="text"
                       class="mt-1 rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
            </div>
            <div>
                <label for="swatch_hex" class="block text-sm font-medium text-ink">Swatch (optional)</label>
                <input id="swatch_hex" name="swatch_hex" type="color" value="#000000"
                       class="mt-1 h-10 w-16 rounded-[var(--radius-sm)] border border-border">
            </div>
            <button type="submit"
                    class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
                Add value
            </button>
        </form>
    </div>
</x-layouts.admin>
