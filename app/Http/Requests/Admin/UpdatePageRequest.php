<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-STF-007; VR-11 (rich text); VR-12 (slug). */
class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/',
                Rule::unique(Page::class, 'slug')->ignore($this->route('page')),
            ],
            'body' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
            'show_in_footer' => ['sometimes', 'boolean'],
        ];
    }
}
