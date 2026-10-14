<x-layouts.admin title="Store settings">
    <h1 class="text-2xl font-bold text-ink">Store settings</h1>

    <form method="POST" action="{{ route('admin.settings.store.update') }}" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')
        <x-admin.form-section title="Store">
            <x-admin.input label="Store name" name="name" :value="old('name', $settings['name'] ?? '')" required />
            <x-admin.input label="Contact phone" name="contact_phone" :value="old('contact_phone', $settings['contact_phone'] ?? '')" />
            <x-admin.input label="Contact email" name="contact_email" type="email" :value="old('contact_email', $settings['contact_email'] ?? '')" />
            <x-admin.textarea label="Contact address" name="contact_address" :value="old('contact_address', $settings['contact_address'] ?? '')" />
            <x-admin.input label="Business hours" name="business_hours" :value="old('business_hours', $settings['business_hours'] ?? '')" />
            <x-admin.input label="Order number prefix" name="order_number_prefix" :value="old('order_number_prefix', $settings['order_number_prefix'] ?? '')" required />
            <x-admin.input label="Currency symbol" name="currency_symbol" :value="old('currency_symbol', $settings['currency_symbol'] ?? '')" required />
            <x-admin.input label="Low-stock default threshold" name="low_stock_default_threshold" type="number" :value="old('low_stock_default_threshold', $settings['low_stock_default_threshold'] ?? '5')" required />
            <x-admin.input label="Announcement bar text (optional)" name="announcement_text" :value="old('announcement_text', $settings['announcement_text'] ?? '')" />
            <x-admin.input label="Announcement bar link (optional)" name="announcement_link" :value="old('announcement_link', $settings['announcement_link'] ?? '')" />
            <x-admin.input label="Facebook URL (optional)" name="facebook_url" :value="old('facebook_url', $settings['facebook_url'] ?? '')" />
            <x-admin.input label="Instagram URL (optional)" name="instagram_url" :value="old('instagram_url', $settings['instagram_url'] ?? '')" />
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save store settings
        </button>
    </form>
</x-layouts.admin>
