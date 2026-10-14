@props(['label', 'name', 'options' => [], 'selected' => null, 'placeholder' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-ink">{{ $label }}</label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->except(['label', 'name', 'options', 'selected', 'placeholder']) }}
        class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none"
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $selected) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
