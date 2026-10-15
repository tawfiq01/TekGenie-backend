@php
    $announcementText = \App\Models\Setting::get('store', 'announcement_text');
    $announcementLink = \App\Models\Setting::get('store', 'announcement_link');
    $footerPages = \App\Models\Page::query()->where('is_published', true)->where('show_in_footer', true)->orderBy('title')->get();
    $contactPhone = \App\Models\Setting::get('store', 'contact_phone');
    $contactEmail = \App\Models\Setting::get('store', 'contact_email');
    $cartSummary = app(\App\Services\Cart\CartService::class)->summary();
    $seo = $seo ?? app(\App\Services\Seo\SeoMetaService::class)->forGeneric($title ?? config('app.name'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo->title }}</title>
    <meta name="description" content="{{ $seo->description }}">
    <link rel="canonical" href="{{ $seo->canonical }}">
    <meta property="og:title" content="{{ $seo->title }}">
    <meta property="og:description" content="{{ $seo->description }}">
    <meta property="og:type" content="{{ $seo->ogType }}">
    <meta property="og:url" content="{{ $seo->canonical }}">
    @if ($seo->ogImage)
        <meta property="og:image" content="{{ $seo->ogImage }}">
    @endif
    @foreach ($seo->jsonLd as $jsonLdBlock)
        <script type="application/ld+json">{!! json_encode($jsonLdBlock, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach

    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-bg text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-md focus:bg-ink focus:px-4 focus:py-2 focus:text-white">
        Skip to content
    </a>

    @if ($announcementText)
        <div class="bg-ink px-4 py-2 text-center text-sm text-white">
            @if ($announcementLink)
                <a href="{{ $announcementLink }}" class="hover:underline">{{ $announcementText }}</a>
            @else
                {{ $announcementText }}
            @endif
        </div>
    @endif

    <header class="border-b border-border">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="text-lg font-semibold text-ink">{{ config('app.name') }}</a>

            <a href="{{ route('shop.index') }}" class="text-sm text-ink-soft hover:text-ink">Shop</a>

            <form method="GET" action="{{ route('search.index') }}" class="order-last flex w-full flex-1 sm:order-none sm:w-auto">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products…"
                       class="w-full rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm focus:border-link focus:outline-none">
            </form>

            <div class="ml-auto flex items-center gap-4 text-sm">
                <a href="{{ route('cart.show') }}" class="flex items-center gap-1 text-ink-soft hover:text-ink">
                    Cart ({{ $cartSummary['count'] }})
                    @if ($cartSummary['count'] > 0)
                        <span class="text-ink">· ৳{{ $cartSummary['subtotal'] }}</span>
                    @endif
                </a>
                @auth('web')
                    <a href="{{ route('account.dashboard') }}" class="text-ink-soft hover:text-ink">My account</a>
                @else
                    <a href="{{ route('login') }}" class="text-ink-soft hover:text-ink">Log in</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="main" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-border">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 text-sm sm:grid-cols-3">
                <div>
                    <h2 class="font-semibold text-ink">Help</h2>
                    <ul class="mt-2 space-y-1 text-ink-soft">
                        @foreach ($footerPages as $page)
                            <li><a href="{{ $page->slug === 'contact' ? route('contact.show') : route('page.show', $page) }}" class="hover:text-ink">{{ $page->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h2 class="font-semibold text-ink">Contact</h2>
                    <ul class="mt-2 space-y-1 text-ink-soft">
                        @if ($contactPhone)
                            <li>{{ $contactPhone }}</li>
                        @endif
                        @if ($contactEmail)
                            <li>{{ $contactEmail }}</li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h2 class="font-semibold text-ink">Payment</h2>
                    <p class="mt-2 text-ink-soft">Cash on Delivery</p>
                </div>
            </div>

            <p class="mt-8 text-sm text-muted">&copy; {{ now()->year }} {{ config('app.name') }}</p>
        </div>
    </footer>
</body>
</html>
