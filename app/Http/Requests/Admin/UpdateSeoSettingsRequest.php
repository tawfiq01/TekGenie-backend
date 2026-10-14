<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** FR-SET-002 (global defaults only — per-entity overrides are WBS §12.1). */
class UpdateSeoSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'default_meta_title_pattern' => ['required', 'string', 'max:191'],
            'default_meta_description' => ['nullable', 'string', 'max:300'],
        ];
    }
}
