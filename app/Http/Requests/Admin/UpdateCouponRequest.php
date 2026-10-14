<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique(Coupon::class, 'code')->ignore($this->route('coupon')),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['percentage', 'fixed', 'free_delivery'])],
            'value' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'max_discount' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'min_subtotal' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'applies_to' => ['required', Rule::in(['all', 'categories', 'products'])],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [Rule::exists(Category::class, 'id')],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => [Rule::exists(Product::class, 'id')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
