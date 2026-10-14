@props(['title' => null])

<div class="rounded-[var(--radius-md)] border border-border bg-bg p-6">
    @if ($title)
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-soft">{{ $title }}</h2>
    @endif

    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>
