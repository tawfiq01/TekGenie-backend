<?php

namespace App\Http\Requests\Admin;

use App\Models\DeliveryZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-SHP-003. */
class StoreDeliveryZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', Rule::unique(DeliveryZone::class, 'name')],
            'base_charge' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'estimate_text' => ['nullable', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
