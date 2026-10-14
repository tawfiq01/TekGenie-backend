<?php

namespace App\Http\Requests\Admin;

use App\Models\DeliveryZone;
use App\Models\District;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-SHP-001: staff can add areas/thanas under districts. */
class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'district_id' => ['required', Rule::exists(District::class, 'id')],
            'name' => ['required', 'string', 'max:60'],
            'name_bn' => ['nullable', 'string', 'max:60'],
            'delivery_zone_id' => ['nullable', Rule::exists(DeliveryZone::class, 'id')],
        ];
    }
}
