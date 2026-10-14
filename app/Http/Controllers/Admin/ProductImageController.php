<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductImageRequest;
use App\Http\Requests\Admin\UpdateProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Catalog\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** FR-CAT-007/008. */
class ProductImageController extends Controller
{
    public function __construct(private readonly ProductImageService $images) {}

    public function store(StoreProductImageRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $this->images->upload($product, $request->file('image'), $data['option_value_id'] ?? null, $data['alt_text'] ?? null);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Image uploaded.');
    }

    public function update(UpdateProductImageRequest $request, ProductImage $productImage): RedirectResponse
    {
        $this->images->updateMeta($productImage, $request->validated());

        return redirect()->route('admin.products.edit', $productImage->product)->with('status', 'Image updated.');
    }

    public function setMain(ProductImage $productImage): RedirectResponse
    {
        $this->images->setMain($productImage);

        return redirect()->route('admin.products.edit', $productImage->product)->with('status', 'Main image updated.');
    }

    public function reorder(Request $request, Product $product): RedirectResponse
    {
        $this->images->reorder($product, (array) $request->input('image_ids', []));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Image order updated.');
    }

    public function destroy(ProductImage $productImage): RedirectResponse
    {
        $product = $productImage->product;

        $this->images->destroy($productImage);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Image removed.');
    }
}
