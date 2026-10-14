<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-STF-003; VR-10 (image); VR-14 (dates). */
class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'placement' => ['required', Rule::in(['hero', 'promo'])],
            'title' => ['nullable', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:40'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['required', 'string', 'max:200'],
            'image_desktop' => ['required', 'file', 'mimes:jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'image_mobile' => ['nullable', 'file', 'mimes:jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
