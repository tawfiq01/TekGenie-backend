<?php

namespace App\Http\Requests\Storefront;

use App\Models\Address;
use App\Models\Area;
use App\Models\District;
use App\Rules\BdMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-CHK-002; VR-01/02/05. */
class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'address_id' => ['nullable', Rule::exists(Address::class, 'id')],
            'name' => ['required_without:address_id', 'nullable', 'string', 'min:2', 'max:100'],
            'phone' => ['required_without:address_id', 'nullable', 'string', new BdMobileNumber],
            'email' => ['nullable', 'string', 'email', 'max:191'],
            'district_id' => ['required_without:address_id', 'nullable', Rule::exists(District::class, 'id')],
            'area_id' => ['nullable', Rule::exists(Area::class, 'id')],
            'area_text' => ['nullable', 'string', 'max:100'],
            'address_line' => ['required_without:address_id', 'nullable', 'string', 'min:5', 'max:255'],
            'postcode' => ['nullable', 'regex:/^\d{4}$/'],
            'delivery_instructions' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string', 'max:32'],
            'expected_prices' => ['nullable', 'array'],
        ];
    }
}
