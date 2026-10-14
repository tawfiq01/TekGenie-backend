<x-layouts.admin title="Payment settings">
    <h1 class="text-2xl font-bold text-ink">Payment settings</h1>

    <form method="POST" action="{{ route('admin.settings.payments.update') }}" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')
        <x-admin.form-section title="Cash on Delivery">
            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="cod_enabled" value="1" {{ old('cod_enabled', $settings['cod_enabled'] ?? '1') ? 'checked' : '' }}>
                Cash on Delivery enabled
            </label>
            <x-admin.input label="Display name" name="cod_display_name" :value="old('cod_display_name', $settings['cod_display_name'] ?? '')" required />
            <x-admin.textarea label="Instructions shown at checkout (optional)" name="cod_instructions" :value="old('cod_instructions', $settings['cod_instructions'] ?? '')" />
            <x-admin.input label="Maximum order value for COD (optional, 0 = no limit)" name="cod_max_order_value" type="number" step="0.01" :value="old('cod_max_order_value', $codMaxOrderValue)" />
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save payment settings
        </button>
    </form>
</x-layouts.admin>
