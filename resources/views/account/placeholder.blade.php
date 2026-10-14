<x-layouts.app>
    <h1 class="text-3xl font-bold text-ink">My account</h1>
    <p class="mt-2 text-ink-soft">Welcome back, {{ auth()->user()->name }}. Account pages are built in the next phase.</p>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
            Log out
        </button>
    </form>
</x-layouts.app>
