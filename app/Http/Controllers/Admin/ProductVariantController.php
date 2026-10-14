<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantRequest;
use App\Http\Requests\Admin\UpdateVariantRequest;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\VariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** FR-CAT-005. {variant} is scoped to {product} via ProductVariant::product(). */
class ProductVariantController extends Controller
{
    public function __construct(private readonly VariantService $variants) {}

    public function index(Product $product): View
    {
        return view('admin.products.variants.index', [
            'product' => $product,
            'variants' => $product->variants()->with('optionValues')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(Product $product): View
    {
        return view('admin.products.variants.create', [
            'product' => $product,
            'optionTypes' => $product->optionTypes()->with('values')->get(),
        ]);
    }

    public function store(StoreVariantRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->variants->create($product, $data, $data['option_value_ids'] ?? [], $actor);

        return redirect()->route('admin.products.variants.index', $product)->with('status', 'Variant created.');
    }

    public function edit(Product $product, ProductVariant $variant): View
    {
        return view('admin.products.variants.edit', [
            'product' => $product,
            'variant' => $variant->load('optionValues'),
        ]);
    }

    public function update(UpdateVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->variants->update($variant, $request->validated());

        return redirect()->route('admin.products.variants.index', $product)->with('status', 'Variant updated.');
    }
}
