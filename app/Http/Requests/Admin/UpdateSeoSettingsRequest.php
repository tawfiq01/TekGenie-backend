<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** FR-SET-002 (global defaults; per-entity overrides already live on each product/category/page form); FR-SET-004; FR-SET-007. */
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
            'robots_txt_extra' => ['nullable', 'string', 'max:2000'],
            'maintenance_mode_enabled' => ['nullable', 'boolean'],
            'maintenance_secret' => ['nullable', 'string', 'max:64', 'required_if:maintenance_mode_enabled,1'],
        ];
    }
}
