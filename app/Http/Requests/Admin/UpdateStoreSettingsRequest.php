<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** FR-SET-001. */
class UpdateStoreSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:191'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'business_hours' => ['nullable', 'string', 'max:255'],
            'order_number_prefix' => ['required', 'string', 'max:5', 'alpha'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'low_stock_default_threshold' => ['required', 'integer', 'min:0'],
            'announcement_text' => ['nullable', 'string', 'max:255'],
            'announcement_link' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
