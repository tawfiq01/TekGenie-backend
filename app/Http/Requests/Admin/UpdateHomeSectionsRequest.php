<?php

namespace App\Http\Requests\Admin;

use App\Models\HomeSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomeSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'sections' => ['required', 'array'],
            'sections.*.id' => ['required', Rule::exists(HomeSection::class, 'id')],
            'sections.*.title' => ['required', 'string', 'max:120'],
            'sections.*.sort_order' => ['required', 'integer', 'min:0'],
            'sections.*.is_active' => ['sometimes', 'boolean'],
        ];
    }
}
