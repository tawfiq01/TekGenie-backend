<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-CAT-010. */
class SyncRelatedProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'related_product_ids' => ['nullable', 'array'],
            'related_product_ids.*' => [Rule::exists(Product::class, 'id')],
        ];
    }
}
