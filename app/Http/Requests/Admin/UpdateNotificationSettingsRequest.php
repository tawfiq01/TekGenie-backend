<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** FR-NTF-002/003. */
class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'staff_new_order_email' => ['nullable', 'email', 'max:191'],
            'status_change_emails_enabled' => ['nullable', 'boolean'],
            'notify_confirmed' => ['nullable', 'boolean'],
            'notify_shipped' => ['nullable', 'boolean'],
            'notify_delivered' => ['nullable', 'boolean'],
            'notify_cancelled' => ['nullable', 'boolean'],
        ];
    }
}
