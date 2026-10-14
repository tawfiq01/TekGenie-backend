@props(['action'])

<form method="GET" action="{{ $action }}" class="mt-6 flex flex-wrap items-end gap-3">
    {{ $slot }}
    <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-1.5 text-sm font-semibold text-ink hover:bg-bg-subtle">
        Filter
    </button>
</form>
