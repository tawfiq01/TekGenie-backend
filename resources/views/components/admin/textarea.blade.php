@props(['label', 'name', 'value' => null, 'rows' => 4])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-ink">{{ $label }}</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->except(['label', 'name', 'value', 'rows']) }}
        class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none"
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
