<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** FR-CAT-009. */
class SyncProductAttributesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'attributes' => ['nullable', 'array'],
            'attributes.*.label' => ['required_with:attributes', 'string', 'max:80'],
            'attributes.*.value' => ['required_with:attributes', 'string', 'max:255'],
        ];
    }
}
