<?php

namespace App\Http\Requests\Admin;

use App\Models\OptionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOptionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique(OptionType::class)->ignore($this->route('optionType'))],
            'display_style' => ['required', 'string', Rule::in(['chip', 'swatch', 'dropdown'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
