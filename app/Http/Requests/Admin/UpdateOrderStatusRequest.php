<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-ORD-002. */
class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Cancelling is excluded here — it needs the separate orders.cancel
     * permission and goes through OrderController::cancel() instead.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)->except(OrderStatus::Cancelled)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
