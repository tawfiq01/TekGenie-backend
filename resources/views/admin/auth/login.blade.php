<x-layouts.guest title="Staff login">
    <h1 class="text-xl font-semibold text-ink">Staff login</h1>
    <p class="mt-1 text-sm text-muted">Admin panel — staff accounts only.</p>

    @if (session('status'))
        <p class="mt-4 rounded-[var(--radius-sm)] bg-warning-soft px-3 py-2 text-sm text-warning">
            {{ session('status') }}
        </p>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-ink">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
            @error('email')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ink">Password</label>
            <input id="password" name="password" type="password" required
                   class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
        </div>

        <label class="flex items-center gap-2 text-sm text-ink-soft">
            <input type="checkbox" name="remember" class="rounded border-border">
            Remember me
        </label>

        <button type="submit"
                class="w-full rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Log in
        </button>
    </form>
</x-layouts.guest>
