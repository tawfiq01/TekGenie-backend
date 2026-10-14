@php($adminUser = auth('admin')->user())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} — {{ config('app.name') }}</title>

    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-bg-subtle text-ink antialiased">
    <header class="border-b border-border bg-bg">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-ink">{{ config('app.name') }} Admin</a>

                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="text-ink-soft hover:text-ink">Dashboard</a>

                    @if ($adminUser?->can('staff.manage'))
                        <a href="{{ route('admin.staff.index') }}" class="text-ink-soft hover:text-ink">Staff</a>
                        <a href="{{ route('admin.roles.index') }}" class="text-ink-soft hover:text-ink">Roles</a>
                    @endif

                    @if ($adminUser?->can('audit.view'))
                        <a href="{{ route('admin.audit-log.index') }}" class="text-ink-soft hover:text-ink">Audit log</a>
                    @endif
                </nav>
            </div>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="text-sm text-ink-soft hover:text-ink">
                    Log out ({{ $adminUser?->name }})
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('status'))
            <p class="mb-6 rounded-[var(--radius-sm)] bg-success-soft px-3 py-2 text-sm text-success">
                {{ session('status') }}
            </p>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
