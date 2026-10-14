<?php

namespace App\Http\Requests\Admin;

use App\Models\OptionValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-CAT-007; VR-10. */
class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'mimes:jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'option_value_id' => ['nullable', Rule::exists(OptionValue::class, 'id')],
            'alt_text' => ['nullable', 'string', 'max:200'],
        ];
    }
}
