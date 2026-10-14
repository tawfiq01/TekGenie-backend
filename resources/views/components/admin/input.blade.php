@props(['label', 'name', 'type' => 'text', 'value' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-ink">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->except(['label', 'name', 'type', 'value']) }}
        class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none"
    >
    @error($name)
        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
