<x-layouts.app title="My account">
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-ink">Welcome back, {{ auth()->user()->name }}</h1>

        @if (session('status'))
            <p class="mt-4 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">{{ session('status') }}</p>
        @endif

        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <a href="{{ route('account.orders.index') }}" class="rounded-[var(--radius-md)] border border-border p-4 text-center text-sm font-medium text-ink hover:bg-bg-subtle">My orders</a>
            <a href="{{ route('account.addresses.index') }}" class="rounded-[var(--radius-md)] border border-border p-4 text-center text-sm font-medium text-ink hover:bg-bg-subtle">Addresses</a>
            <a href="{{ route('wishlist.index') }}" class="rounded-[var(--radius-md)] border border-border p-4 text-center text-sm font-medium text-ink hover:bg-bg-subtle">Wishlist</a>
            <a href="{{ route('account.profile') }}" class="rounded-[var(--radius-md)] border border-border p-4 text-center text-sm font-medium text-ink hover:bg-bg-subtle">Profile</a>
        </div>

        <div class="mt-8">
            <h2 class="font-semibold text-ink">Recent orders</h2>
            <ul class="mt-3 space-y-2">
                @forelse ($recentOrders as $order)
                    <li class="flex items-center justify-between rounded-[var(--radius-sm)] border border-border px-4 py-3 text-sm">
                        <span class="text-ink">{{ $order->order_number }} — {{ $order->status->label() }}</span>
                        <a href="{{ route('checkout.confirmation', $order) }}" class="text-link hover:underline">View</a>
                    </li>
                @empty
                    <li class="text-sm text-ink-soft">You haven't placed any orders yet.</li>
                @endforelse
            </ul>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button type="submit" class="text-sm text-ink-soft hover:text-ink">Log out</button>
        </form>
    </div>
</x-layouts.app>
