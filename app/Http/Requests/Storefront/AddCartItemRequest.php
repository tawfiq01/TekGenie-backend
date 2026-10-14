<?php

namespace App\Http\Requests\Storefront;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-CRT-001/003; VR-06. */
class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', Rule::exists(ProductVariant::class, 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
