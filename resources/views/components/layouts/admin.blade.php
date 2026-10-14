@php
    $adminUser = auth('admin')->user();

    $navGroups = collect([
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'show' => true],
        [
            'label' => 'Catalogue',
            'items' => collect([
                ['label' => 'Products', 'route' => 'admin.products.index', 'show' => $adminUser?->can('products.view')],
                ['label' => 'Categories', 'route' => 'admin.categories.index', 'show' => $adminUser?->can('products.view')],
                ['label' => 'Brands', 'route' => 'admin.brands.index', 'show' => $adminUser?->can('products.view')],
                ['label' => 'Options', 'route' => 'admin.option-types.index', 'show' => $adminUser?->can('products.view')],
            ])->filter(fn ($item) => $item['show']),
        ],
        ['label' => 'Inventory', 'route' => 'admin.inventory.index', 'show' => $adminUser?->can('inventory.adjust')],
        [
            'label' => 'Access',
            'items' => collect([
                ['label' => 'Staff', 'route' => 'admin.staff.index', 'show' => $adminUser?->can('staff.manage')],
                ['label' => 'Roles', 'route' => 'admin.roles.index', 'show' => $adminUser?->can('staff.manage')],
                ['label' => 'Audit log', 'route' => 'admin.audit-log.index', 'show' => $adminUser?->can('audit.view')],
            ])->filter(fn ($item) => $item['show']),
        ],
    ])->filter(fn ($group) => ($group['show'] ?? null) || ($group['items'] ?? collect())->isNotEmpty());
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} — {{ config('app.name') }}</title>

    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-bg-subtle text-ink antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-full">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-ink/30 lg:hidden"></div>

        <aside
            x-cloak
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 transform border-r border-border bg-bg transition-transform duration-200 lg:static lg:translate-x-0"
        >
            <div class="flex h-16 items-center border-b border-border px-6">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-ink">{{ config('app.name') }} Admin</a>
            </div>

            <nav class="space-y-6 px-4 py-6">
                @foreach ($navGroups as $group)
                    @if (isset($group['route']))
                        <a href="{{ route($group['route']) }}"
                           class="block rounded-[var(--radius-sm)] px-3 py-2 text-sm font-medium {{ request()->routeIs($group['route']) ? 'bg-accent-soft text-link' : 'text-ink-soft hover:bg-bg-subtle hover:text-ink' }}">
                            {{ $group['label'] }}
                        </a>
                    @else
                        <div>
                            <p class="px-3 text-xs font-semibold uppercase tracking-wide text-muted">{{ $group['label'] }}</p>
                            <div class="mt-1 space-y-1">
                                @foreach ($group['items'] as $item)
                                    <a href="{{ route($item['route']) }}"
                                       class="block rounded-[var(--radius-sm)] px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route'].'*') ? 'bg-accent-soft text-link' : 'text-ink-soft hover:bg-bg-subtle hover:text-ink' }}">
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-border bg-bg px-4 sm:px-6 lg:px-8">
                <button type="button" @click="sidebarOpen = true" class="text-ink-soft hover:text-ink lg:hidden" aria-label="Open menu">
                    ☰
                </button>

                <div class="flex-1"></div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-ink-soft hover:text-ink">
                        Log out ({{ $adminUser?->name }})
                    </button>
                </form>
            </header>

            <main class="flex-1 px-4 py-10 sm:px-6 lg:px-8">
                @if (session('status'))
                    <p class="mb-6 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">
                        {{ session('status') }}
                    </p>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-[var(--radius-sm)] bg-danger-soft px-3 py-2 text-sm text-danger">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
