<?php

namespace App\Http\Requests\Storefront;

use App\Models\Area;
use App\Models\District;
use App\Models\Division;
use App\Rules\BdMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-ACC-006; VR-05. */
class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:30'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', new BdMobileNumber],
            'division_id' => ['required', Rule::exists(Division::class, 'id')],
            'district_id' => ['required', Rule::exists(District::class, 'id')],
            'area_id' => ['nullable', Rule::exists(Area::class, 'id')],
            'area_text' => ['nullable', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'min:5', 'max:255'],
            'postcode' => ['nullable', 'regex:/^\d{4}$/'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
