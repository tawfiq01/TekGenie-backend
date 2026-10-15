<x-layouts.app :title="$page->title" :seo="$seo">
    <h1 class="text-2xl font-bold text-ink">{{ $page->title }}</h1>

    <div class="prose prose-sm mt-6 max-w-none text-ink-soft">
        {!! $page->body !!}
    </div>
</x-layouts.app>
