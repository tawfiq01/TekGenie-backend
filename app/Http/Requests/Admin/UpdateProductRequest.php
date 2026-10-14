<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable', 'string', 'max:220', 'regex:/^[a-z0-9-]+$/',
                Rule::unique(Product::class, 'slug')->ignore($this->route('product')),
            ],
            'category_id' => ['required', Rule::exists(Category::class, 'id')],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [Rule::exists(Category::class, 'id')],
            'brand_id' => ['nullable', Rule::exists(Brand::class, 'id')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
