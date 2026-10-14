@props(['label', 'name', 'checked' => false])

<div>
    <label class="flex items-center gap-2 text-sm text-ink-soft">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" {{ old($name, $checked) ? 'checked' : '' }} class="rounded border-border">
        {{ $label }}
    </label>
    @error($name)
        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
