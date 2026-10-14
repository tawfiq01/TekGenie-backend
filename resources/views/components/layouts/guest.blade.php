<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-bg-subtle text-ink antialiased">
    <div class="flex min-h-full flex-col items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="mb-6 text-lg font-semibold text-ink">{{ config('app.name') }}</a>

        <div class="w-full max-w-sm rounded-[var(--radius-lg)] border border-border bg-bg p-8 shadow-sm">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
