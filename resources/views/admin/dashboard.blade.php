<x-layouts.admin title="Dashboard">
    <h1 class="text-2xl font-bold text-ink">Dashboard</h1>
    <p class="mt-2 text-ink-soft">Foundation build — reports and widgets are built in a later phase (WBS §11).</p>

    @if ($failedJobsCount > 0)
        <div class="mt-6 rounded-[var(--radius-md)] border border-danger bg-danger-soft px-4 py-3 text-sm text-danger">
            {{ $failedJobsCount }} queued job(s) failed and need attention (emails, scheduled tasks). Run <code>php artisan queue:failed</code> on the server to inspect them.
        </div>
    @endif
</x-layouts.admin>
