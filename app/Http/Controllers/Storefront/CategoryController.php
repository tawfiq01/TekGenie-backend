<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OptionType;
use App\Services\Storefront\ProductListingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** FR-SRC-002/003/004. */
class CategoryController extends Controller
{
    public function __construct(private readonly ProductListingService $listing) {}

    public function all(Request $request): View
    {
        return view('storefront.listing', [
            'heading' => 'Shop all',
            'products' => $this->listing->paginate($this->filters($request)),
            'category' => null,
            'categories' => Category::query()->whereNull('parent_id')->where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'optionTypes' => OptionType::query()->with('values')->orderBy('sort_order')->get(),
        ]);
    }

    public function show(Request $request, Category $category): View
    {
        if (! $category->is_active) {
            throw new NotFoundHttpException;
        }

        $filters = $this->filters($request);
        $filters['category_id'] = $category->id;

        return view('storefront.listing', [
            'heading' => $category->name,
            'products' => $this->listing->paginate($filters),
            'category' => $category,
            'categories' => Category::query()->whereNull('parent_id')->where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'optionTypes' => OptionType::query()->with('values')->orderBy('sort_order')->get(),
        ]);
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        return [
            'category_id' => $request->integer('category_id') ?: null,
            'brand_id' => $request->integer('brand_id') ?: null,
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'in_stock' => $request->boolean('in_stock'),
            'option_value_ids' => $request->input('option_value_ids', []),
            'sort' => $request->input('sort'),
        ];
    }
}
