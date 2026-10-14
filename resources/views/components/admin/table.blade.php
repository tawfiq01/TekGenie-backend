@props(['headers' => []])

<div class="overflow-hidden rounded-[var(--radius-md)] border border-border">
    <table class="w-full text-left text-sm">
        <thead class="bg-bg-subtle text-ink-soft">
            <tr>
                @foreach ($headers as $header)
                    <th class="px-4 py-3">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-border bg-bg">
            {{ $slot }}
        </tbody>
    </table>
</div>
