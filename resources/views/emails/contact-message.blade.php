<x-layouts.guest title="New contact message">
    <h1 class="text-lg font-semibold text-ink">New contact message</h1>

    <dl class="mt-4 space-y-2 text-sm">
        <div><dt class="font-medium text-ink">From</dt><dd class="text-ink-soft">{{ $contactMessage->name }} ({{ $contactMessage->email }})</dd></div>
        @if ($contactMessage->phone)
            <div><dt class="font-medium text-ink">Phone</dt><dd class="text-ink-soft">{{ $contactMessage->phone }}</dd></div>
        @endif
        <div><dt class="font-medium text-ink">Subject</dt><dd class="text-ink-soft">{{ $contactMessage->subject }}</dd></div>
        <div><dt class="font-medium text-ink">Message</dt><dd class="whitespace-pre-line text-ink-soft">{{ $contactMessage->message }}</dd></div>
    </dl>
</x-layouts.guest>
