<x-layouts.admin title="Notification settings">
    <h1 class="text-2xl font-bold text-ink">Notification settings</h1>

    <form method="POST" action="{{ route('admin.settings.notifications.update') }}" class="mt-6 max-w-xl">
        @csrf
        @method('PUT')
        <x-admin.form-section title="Emails">
            <x-admin.input label="New-order alert address (optional)" name="staff_new_order_email" type="email" :value="old('staff_new_order_email', $settings['staff_new_order_email'] ?? '')" />

            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="status_change_emails_enabled" value="1" {{ old('status_change_emails_enabled', $settings['status_change_emails_enabled'] ?? '1') ? 'checked' : '' }}>
                Send status-change emails to customers
            </label>

            <p class="text-xs text-ink-soft">The switches below only apply while the master switch above is on.</p>

            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="notify_confirmed" value="1" {{ old('notify_confirmed', $settings['notify_confirmed'] ?? '1') ? 'checked' : '' }}>
                Order confirmed
            </label>
            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="notify_shipped" value="1" {{ old('notify_shipped', $settings['notify_shipped'] ?? '1') ? 'checked' : '' }}>
                Order shipped
            </label>
            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="notify_delivered" value="1" {{ old('notify_delivered', $settings['notify_delivered'] ?? '1') ? 'checked' : '' }}>
                Order delivered
            </label>
            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="notify_cancelled" value="1" {{ old('notify_cancelled', $settings['notify_cancelled'] ?? '1') ? 'checked' : '' }}>
                Order cancelled
            </label>
        </x-admin.form-section>

        <button type="submit" class="mt-6 rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
            Save notification settings
        </button>
    </form>
</x-layouts.admin>
