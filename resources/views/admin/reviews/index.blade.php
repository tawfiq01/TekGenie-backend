<x-layouts.admin title="Reviews">
    <h1 class="text-2xl font-bold text-ink">Reviews</h1>

    <x-admin.filters :action="route('admin.reviews.index')">
        <x-admin.select label="Status" name="status"
            :options="collect(\App\Enums\ReviewStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)])"
            :selected="$status->value" />
    </x-admin.filters>

    <div class="mt-6 space-y-4">
        @forelse ($reviews as $review)
            <div class="rounded-[var(--radius-md)] border border-border p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-ink">{{ $review->product->name }} — {{ $review->rating }}/5</p>
                        <p class="text-sm text-ink-soft">
                            {{ $review->user?->name ?? 'Deleted user' }}
                            @if ($review->isVerifiedPurchase())
                                <span class="ml-1 rounded-[var(--radius-pill)] bg-success-soft px-2 py-0.5 text-xs text-success">Verified purchase</span>
                            @endif
                        </p>
                        @if ($review->title)
                            <p class="mt-2 font-medium text-ink">{{ $review->title }}</p>
                        @endif
                        @if ($review->body)
                            <p class="mt-1 text-sm text-ink-soft">{{ $review->body }}</p>
                        @endif
                        @if ($review->reply)
                            <p class="mt-2 rounded-[var(--radius-sm)] bg-bg-subtle px-3 py-2 text-sm text-ink-soft"><strong>Store reply:</strong> {{ $review->reply }}</p>
                        @endif
                    </div>

                    @if ($review->status === \App\Enums\ReviewStatus::Pending)
                        <div class="flex shrink-0 gap-2">
                            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                                @csrf
                                <button type="submit" class="rounded-[var(--radius-pill)] bg-ink px-3 py-1.5 text-sm font-semibold text-white hover:bg-ink-soft">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                                @csrf
                                <button type="submit" class="rounded-[var(--radius-pill)] border border-danger px-3 py-1.5 text-sm font-semibold text-danger hover:bg-danger-soft">Reject</button>
                            </form>
                        </div>
                    @endif
                </div>

                @if (! $review->reply)
                    <form method="POST" action="{{ route('admin.reviews.reply', $review) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="reply" placeholder="Public reply (optional)" class="flex-1 rounded-[var(--radius-sm)] border border-border px-2 py-1.5 text-sm">
                        <button type="submit" class="rounded-[var(--radius-sm)] border border-border px-3 py-1.5 text-sm text-ink hover:bg-bg-subtle">Reply</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-sm text-ink-soft">No reviews in this status.</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $reviews->links() }}
    </div>
</x-layouts.admin>
