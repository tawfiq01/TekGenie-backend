<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReturnCondition;
use App\Enums\ReturnType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-ORD-009. */
class StoreOrderReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * `lines` is keyed by order_item_id (one row per item in the form); a
     * line with no quantity simply means "not part of this return" — the
     * controller drops those, and ReturnService enforces the real business
     * rules (at least one line, quantity within what's still returnable).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ReturnType::class)],
            'reason' => ['required', 'string', 'max:255'],
            'lines' => ['required', 'array'],
            'lines.*.quantity' => ['nullable', 'integer', 'min:0'],
            'lines.*.condition' => ['nullable', Rule::enum(ReturnCondition::class)],
        ];
    }
}
