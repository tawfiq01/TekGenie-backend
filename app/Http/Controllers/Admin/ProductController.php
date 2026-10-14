<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\SyncProductAttributesRequest;
use App\Http\Requests\Admin\SyncRelatedProductsRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OptionType;
use App\Models\Product;
use App\Services\Catalog\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** FR-CAT-003, gated by products.view (index) / products.edit (writes). */
class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        return view('admin.products.index', [
            'products' => Product::query()
                ->with('category')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'optionTypes' => OptionType::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $product = $this->products->create($data, $data['category_ids'] ?? [], $data['option_type_ids'] ?? [], $actor);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created — add its variants and images below.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load(['categories', 'optionTypes.values', 'images.optionValue', 'attributes', 'relatedProducts', 'variants']),
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'otherProducts' => Product::query()->where('id', '!=', $product->id)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->products->update($product, $data, $data['category_ids'] ?? [], $actor);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
    }

    public function updateAttributes(SyncProductAttributesRequest $request, Product $product): RedirectResponse
    {
        $this->products->syncAttributes($product, $request->validated()['attributes'] ?? []);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Specifications updated.');
    }

    public function updateRelated(SyncRelatedProductsRequest $request, Product $product): RedirectResponse
    {
        $this->products->syncRelated($product, $request->validated()['related_product_ids'] ?? []);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Related products updated.');
    }
}
