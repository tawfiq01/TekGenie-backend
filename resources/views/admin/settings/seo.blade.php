<x-layouts.admin title="SEO settings">
    <h1 class="text-2xl font-bold text-ink">SEO settings</h1>
    <p class="mt-1 text-sm text-ink-soft">Defaults used when a product, category or page has no meta title/description of its own. Per-page overrides, sitemap, robots.txt and structured data are built in a later phase.</p>

    <form method="POST" action="{{ route('admin.settings.seo.update') }}" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')
        <x-admin.form-section title="Defaults">
            <x-admin.input label="Default meta title pattern" name="default_meta_title_pattern" :value="old('default_meta_title_pattern', $settings['default_meta_title_pattern'] ?? '')" required />
            <p class="-mt-2 text-xs text-ink-soft">Use <code>{title}</code> where the page's own title should appear.</p>
            <x-admin.textarea label="Default meta description (optional)" name="default_meta_description" :value="old('default_meta_description', $settings['default_meta_description'] ?? '')" />
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save SEO settings
        </button>
    </form>
</x-layouts.admin>
