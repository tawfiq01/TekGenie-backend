<x-layouts.guest title="Log in">
    <h1 class="text-xl font-semibold text-ink">Log in</h1>
    <p class="mt-1 text-sm text-muted">Use your email or mobile number.</p>

    @if (session('status'))
        <p class="mt-4 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">
            {{ session('status') }}
        </p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-ink">Email or mobile number</label>
            <input id="email" name="email" type="text" value="{{ old('email') }}" required autofocus
                   class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
            @error('email')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ink">Password</label>
            <input id="password" name="password" type="password" required
                   class="mt-1 block w-full rounded-[var(--radius-sm)] border border-border px-3 py-2 text-sm focus:border-link focus:outline-none">
            @error('password')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="remember" class="rounded border-border">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-link hover:underline">Forgot password?</a>
        </div>

        <button type="submit"
                class="w-full rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Log in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        New here? <a href="{{ route('register') }}" class="text-link hover:underline">Create an account</a>
    </p>
</x-layouts.guest>
