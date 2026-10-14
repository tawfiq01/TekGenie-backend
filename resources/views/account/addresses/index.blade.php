<x-layouts.app title="Your addresses">
    <div class="mx-auto max-w-2xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-ink">Your addresses</h1>
            <a href="{{ route('account.addresses.create') }}" class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                Add address
            </a>
        </div>

        @if (session('status'))
            <p class="mt-4 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">{{ session('status') }}</p>
        @endif

        <div class="mt-6 space-y-4">
            @forelse ($addresses as $address)
                <div class="rounded-[var(--radius-md)] border border-border p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            @if ($address->is_default)
                                <span class="rounded-[var(--radius-pill)] bg-accent-soft px-2 py-0.5 text-xs font-medium text-link">Default</span>
                            @endif
                            <p class="mt-1 font-semibold text-ink">{{ $address->label ?: 'Address' }} — {{ $address->recipient_name }}</p>
                            <p class="text-sm text-ink-soft">{{ $address->phone }}</p>
                            <p class="text-sm text-ink-soft">{{ $address->address_line }}, {{ $address->area_text ? $address->area_text.', ' : '' }}{{ $address->district->name }}, {{ $address->division->name }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2 text-sm">
                            <a href="{{ route('account.addresses.edit', $address) }}" class="text-link hover:underline">Edit</a>
                            @unless ($address->is_default)
                                <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="text-link hover:underline">Make default</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger hover:underline">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-soft">You haven't saved any addresses yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
