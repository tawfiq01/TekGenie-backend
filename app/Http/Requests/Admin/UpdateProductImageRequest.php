<?php

namespace App\Http\Requests\Admin;

use App\Models\OptionValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'option_value_id' => ['nullable', Rule::exists(OptionValue::class, 'id')],
            'alt_text' => ['nullable', 'string', 'max:200'],
        ];
    }
}
