<?php

namespace App\Http\Requests\Admin;

use App\Rules\BdMobileNumber;
use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-ORD-006 (S): staff can fix the customer name, phone, address line,
 * postcode and delivery instructions while the order is Pending/Confirmed.
 * Changing the division/district/area would require re-resolving the
 * delivery zone and charge, so that's out of scope here — a genuine
 * relocation needs the order cancelled and re-placed.
 */
class UpdateOrderAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', new BdMobileNumber],
            'address_line' => ['required', 'string', 'max:255'],
            'postcode' => ['nullable', 'regex:/^\d{4}$/'],
            'delivery_instructions' => ['nullable', 'string', 'max:500'],
        ];
    }
}
