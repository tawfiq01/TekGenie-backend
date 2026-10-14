<x-layouts.app title="Contact Us">
    <h1 class="text-2xl font-bold text-ink">Contact Us</h1>

    <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-2">
        <div>
            @if ($page)
                <div class="prose prose-sm max-w-none text-ink-soft">{!! $page->body !!}</div>
            @endif

            <dl class="mt-4 space-y-2 text-sm">
                @if ($contactPhone)
                    <div><dt class="inline font-medium text-ink">Phone:</dt> <dd class="inline text-ink-soft">{{ $contactPhone }}</dd></div>
                @endif
                @if ($contactEmail)
                    <div><dt class="inline font-medium text-ink">Email:</dt> <dd class="inline text-ink-soft">{{ $contactEmail }}</dd></div>
                @endif
                @if ($contactAddress)
                    <div><dt class="inline font-medium text-ink">Address:</dt> <dd class="inline text-ink-soft">{{ $contactAddress }}</dd></div>
                @endif
                @if ($businessHours)
                    <div><dt class="inline font-medium text-ink">Hours:</dt> <dd class="inline text-ink-soft">{{ $businessHours }}</dd></div>
                @endif
            </dl>
        </div>

        <div>
            @if (session('status'))
                <p class="mb-4 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                @csrf

                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <label for="website">Leave blank</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-ink">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    @error('name') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-ink">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    @error('email') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-ink">Phone (optional)</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                           class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-ink">Subject</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required
                           class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">
                    @error('subject') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-ink">Message</label>
                    <textarea id="message" name="message" rows="5" required
                              class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                    Send message
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
