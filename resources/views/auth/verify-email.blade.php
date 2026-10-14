<x-layouts.guest title="Verify your email">
    <h1 class="text-xl font-semibold text-ink">Verify your email</h1>
    <p class="mt-2 text-sm text-ink-soft">
        Thanks for signing up! Before writing a review you'll need to verify your email address.
        We've emailed you a verification link — click it to continue.
    </p>

    @if (session('status') === 'verification-link-sent')
        <p class="mt-4 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">
            A new verification link has been sent to your email address.
        </p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <button type="submit"
                class="w-full rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="w-full text-center text-sm text-muted hover:underline">
            Log out
        </button>
    </form>
</x-layouts.guest>
