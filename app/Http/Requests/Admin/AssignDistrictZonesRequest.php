<?php

namespace App\Http\Requests\Admin;

use App\Models\DeliveryZone;
use App\Models\District;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AssignDistrictZonesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'zones' => ['required', 'array'],
            'zones.*' => ['nullable', Rule::exists(DeliveryZone::class, 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $districtIds = array_keys((array) $this->input('zones', []));

            if (District::whereIn('id', $districtIds)->count() !== count($districtIds)) {
                $validator->errors()->add('zones', 'One or more districts were not recognised.');
            }
        });
    }
}
