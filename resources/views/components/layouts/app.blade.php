<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-bg text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-md focus:bg-ink focus:px-4 focus:py-2 focus:text-white">
        Skip to content
    </a>

    <header class="border-b border-border">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="text-lg font-semibold text-ink">{{ config('app.name') }}</a>
        </div>
    </header>

    <main id="main" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-border">
        <div class="mx-auto max-w-7xl px-4 py-8 text-sm text-muted sm:px-6 lg:px-8">
            &copy; {{ now()->year }} {{ config('app.name') }}
        </div>
    </footer>
</body>
</html>
