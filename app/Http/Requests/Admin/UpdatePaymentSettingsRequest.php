<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** FR-PAY-002. */
class UpdatePaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'cod_enabled' => ['nullable', 'boolean'],
            'cod_display_name' => ['required', 'string', 'max:60'],
            'cod_instructions' => ['nullable', 'string', 'max:500'],
            'cod_max_order_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
