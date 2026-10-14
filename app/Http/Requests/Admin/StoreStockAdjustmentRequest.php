<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-INV-005: stock adjustments always need a reason. */
class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', Rule::notIn([0])],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
