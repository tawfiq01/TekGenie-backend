<x-layouts.admin title="SEO settings">
    <h1 class="text-2xl font-bold text-ink">SEO settings</h1>
    <p class="mt-1 text-sm text-ink-soft">Defaults used when a product, category or page has no meta title/description of its own — set per-entity overrides on the product/category/page edit screens.</p>

    <form method="POST" action="{{ route('admin.settings.seo.update') }}" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')
        <x-admin.form-section title="Defaults">
            <x-admin.input label="Default meta title pattern" name="default_meta_title_pattern" :value="old('default_meta_title_pattern', $settings['default_meta_title_pattern'] ?? '')" required />
            <p class="-mt-2 text-xs text-ink-soft">Use <code>{title}</code> where the page's own title should appear.</p>
            <x-admin.textarea label="Default meta description (optional)" name="default_meta_description" :value="old('default_meta_description', $settings['default_meta_description'] ?? '')" />
        </x-admin.form-section>

        <x-admin.form-section title="robots.txt">
            <p class="text-xs text-ink-soft">/admin, /account, /cart and /checkout are always disallowed. Add any extra rules below (merged in as-is).</p>
            <x-admin.textarea label="Extra robots.txt rules (optional)" name="robots_txt_extra" :value="old('robots_txt_extra', $settings['robots_txt_extra'] ?? '')" :rows="3" />
        </x-admin.form-section>

        <x-admin.form-section title="Maintenance mode">
            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="maintenance_mode_enabled" value="1" {{ old('maintenance_mode_enabled', $settings['maintenance_mode_enabled'] ?? '0') ? 'checked' : '' }}>
                Take the storefront down for maintenance
            </label>
            <x-admin.input label="Secret (lets you preview the site while it's down)" name="maintenance_secret" :value="old('maintenance_secret', $settings['maintenance_secret'] ?? '')" />
            <p class="-mt-2 text-xs text-ink-soft">Visit the site with <code>?secret=&lt;your secret&gt;</code> once to unlock it for your browser. The admin panel is never affected.</p>
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save SEO settings
        </button>
    </form>

    <div class="mt-10 max-w-xl border-t border-border pt-6">
        <h2 class="text-sm font-semibold text-ink">Sitemap</h2>
        <p class="mt-1 text-xs text-ink-soft">Rebuilds automatically every few hours. Use this after a bulk catalogue change if you don't want to wait.</p>
        <form method="POST" action="{{ route('admin.settings.seo.regenerate-sitemap') }}" class="mt-3">
            @csrf
            <button type="submit" class="rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm font-semibold text-ink hover:bg-bg-subtle">
                Regenerate sitemap now
            </button>
        </form>
    </div>
</x-layouts.admin>
