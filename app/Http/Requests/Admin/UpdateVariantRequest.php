<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Stock is intentionally not editable here — adjustments go through the
 * Inventory module (WBS §6) so every change is recorded as a movement.
 */
class UpdateVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'sku' => [
                'required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_.-]+$/',
                Rule::unique(ProductVariant::class, 'sku')->ignore($this->route('variant')),
            ],
            'barcode' => ['nullable', 'string', 'max:64'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99', 'gt:price'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
