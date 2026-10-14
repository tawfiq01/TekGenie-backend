<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** FR-PAY-003; UC-22. */
class StoreOrderCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'collected_amount' => ['required', 'numeric', 'min:0.01'],
            'variance_note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
