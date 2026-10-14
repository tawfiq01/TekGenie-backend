<x-layouts.admin title="Dashboard">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">Dashboard</h1>

        <x-admin.filters :action="route('admin.dashboard')">
            <x-admin.select label="Period" name="period" :options="['today' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days']" :selected="$period" />
        </x-admin.filters>
    </div>

    @if ($failedJobsCount > 0)
        <div class="mt-6 rounded-[var(--radius-md)] border border-danger bg-danger-soft px-4 py-3 text-sm text-danger">
            {{ $failedJobsCount }} queued job(s) failed and need attention (emails, scheduled tasks). Run <code>php artisan queue:failed</code> on the server to inspect them.
        </div>
    @endif

    @if ($summary['order_count'] === 0)
        <p class="mt-8 text-sm text-ink-soft">No orders in this period yet.</p>
    @else
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-[var(--radius-md)] border border-border p-4">
                <p class="text-xs text-ink-soft">Orders</p>
                <p class="mt-1 text-2xl font-bold text-ink">{{ $summary['order_count'] }}</p>
            </div>
            <div class="rounded-[var(--radius-md)] border border-border p-4">
                <p class="text-xs text-ink-soft">Gross sales</p>
                <p class="mt-1 text-2xl font-bold text-ink">৳{{ $summary['gross_sales'] }}</p>
            </div>
            <div class="rounded-[var(--radius-md)] border border-border p-4">
                <p class="text-xs text-ink-soft">Average order value</p>
                <p class="mt-1 text-2xl font-bold text-ink">৳{{ $summary['average_order_value'] }}</p>
            </div>
            <div class="rounded-[var(--radius-md)] border border-border p-4">
                <p class="text-xs text-ink-soft">Low stock variants</p>
                <p class="mt-1 text-2xl font-bold text-ink">{{ $summary['low_stock_count'] }}</p>
            </div>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-[var(--radius-md)] border border-border p-4">
            <h2 class="font-semibold text-ink">Orders by status</h2>
            @if ($summary['orders_by_status']->isEmpty())
                <p class="mt-2 text-sm text-ink-soft">No orders in this period yet.</p>
            @else
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach ($summary['orders_by_status'] as $status => $count)
                        <li class="flex justify-between"><span class="text-ink-soft">{{ ucfirst($status) }}</span><span class="text-ink">{{ $count }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-[var(--radius-md)] border border-border p-4">
            <h2 class="font-semibold text-ink">Pending reviews</h2>
            <p class="mt-2 text-2xl font-bold text-ink">{{ $summary['pending_reviews_count'] }}</p>
            @if ($summary['pending_reviews_count'] > 0)
                <a href="{{ route('admin.reviews.index') }}" class="mt-2 inline-block text-sm text-link hover:underline">Moderate reviews</a>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <h2 class="font-semibold text-ink">Latest orders</h2>
        @if ($summary['latest_orders']->isEmpty())
            <p class="mt-2 text-sm text-ink-soft">No orders yet.</p>
        @else
            <ul class="mt-3 space-y-2">
                @foreach ($summary['latest_orders'] as $order)
                    <li class="flex items-center justify-between rounded-[var(--radius-sm)] border border-border px-4 py-3 text-sm">
                        <span class="text-ink">{{ $order->order_number }} — {{ $order->customer_name }}</span>
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-link hover:underline">View</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.admin>
