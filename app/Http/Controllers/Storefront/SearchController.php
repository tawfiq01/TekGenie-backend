<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OptionType;
use App\Services\Storefront\ProductListingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** FR-SRC-001/003/005. */
class SearchController extends Controller
{
    public function __construct(private readonly ProductListingService $listing) {}

    public function index(Request $request): View
    {
        $term = trim((string) $request->input('q', ''));

        $filters = [
            'category_id' => $request->integer('category_id') ?: null,
            'brand_id' => $request->integer('brand_id') ?: null,
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'in_stock' => $request->boolean('in_stock'),
            'option_value_ids' => $request->input('option_value_ids', []),
            'sort' => $request->input('sort', $term !== '' ? 'relevance' : null),
        ];

        return view('storefront.listing', [
            'heading' => $term !== '' ? "Search results for \"{$term}\"" : 'Search',
            'products' => $term !== '' ? $this->listing->paginate($filters, $term) : null,
            'category' => null,
            'searchTerm' => $term,
            'categories' => Category::activeTopLevel()->sortBy('name')->values(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'optionTypes' => OptionType::query()->with('values')->orderBy('sort_order')->get(),
        ]);
    }
}
