<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** FR-ORD-005: courier name and tracking reference are free text. */
class UpdateOrderCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'courier_name' => ['nullable', 'string', 'max:60'],
            'tracking_reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
