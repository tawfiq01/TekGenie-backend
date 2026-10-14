<x-layouts.guest title="Confirm password">
    <h1 class="text-xl font-semibold text-ink">Confirm your password</h1>
    <p class="mt-1 text-sm text-muted">Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="password" class="block text-sm font-medium text-ink">Password</label>
            <input id="password" name="password" type="password" required autofocus
                   class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
            @error('password')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Confirm
        </button>
    </form>
</x-layouts.guest>
