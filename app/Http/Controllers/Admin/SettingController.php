<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateNotificationSettingsRequest;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Http\Requests\Admin\UpdateSeoSettingsRequest;
use App\Http\Requests\Admin\UpdateStoreSettingsRequest;
use App\Models\Setting;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** FR-SET-001/002; FR-PAY-002; FR-NTF-002/003, gated by settings.manage / settings.seo. */
class SettingController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function store(): View
    {
        return view('admin.settings.store', ['settings' => $this->group('store')]);
    }

    public function updateStore(UpdateStoreSettingsRequest $request): RedirectResponse
    {
        $this->saveGroup('store', $request->validated());

        return redirect()->route('admin.settings.store')->with('status', 'Store settings saved.');
    }

    public function payments(): View
    {
        return view('admin.settings.payments', [
            'settings' => $this->group('payments'),
            'codMaxOrderValue' => Setting::get('checkout', 'cod_max_order_value', '0'),
        ]);
    }

    public function updatePayments(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['cod_enabled'] = $request->boolean('cod_enabled') ? '1' : '0';
        $maxOrderValue = (string) ($data['cod_max_order_value'] ?? '0');
        unset($data['cod_max_order_value']);

        $this->saveGroup('payments', $data);
        Setting::put('checkout', 'cod_max_order_value', $maxOrderValue);

        return redirect()->route('admin.settings.payments')->with('status', 'Payment settings saved.');
    }

    public function notifications(): View
    {
        return view('admin.settings.notifications', ['settings' => $this->group('notifications')]);
    }

    public function updateNotifications(UpdateNotificationSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        foreach (['status_change_emails_enabled', 'notify_confirmed', 'notify_shipped', 'notify_delivered', 'notify_cancelled'] as $toggle) {
            $data[$toggle] = $request->boolean($toggle) ? '1' : '0';
        }

        $this->saveGroup('notifications', $data);

        return redirect()->route('admin.settings.notifications')->with('status', 'Notification settings saved.');
    }

    public function seo(): View
    {
        return view('admin.settings.seo', ['settings' => $this->group('seo')]);
    }

    public function updateSeo(UpdateSeoSettingsRequest $request): RedirectResponse
    {
        $this->saveGroup('seo', $request->validated());

        return redirect()->route('admin.settings.seo')->with('status', 'SEO settings saved.');
    }

    /** @return array<string, string> */
    private function group(string $group): array
    {
        return Setting::query()->where('group', $group)->pluck('value', 'key')->all();
    }

    /** @param  array<string, mixed>  $values */
    private function saveGroup(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::put($group, $key, (string) ($value ?? ''));
        }

        $this->audit->log('admin.settings.updated', null, ['group' => $group]);
    }
}
