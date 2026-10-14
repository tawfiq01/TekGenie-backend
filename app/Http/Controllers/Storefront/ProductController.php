<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** FR-STF-005/006; FR-CAT-011. */
class ProductController extends Controller
{
    public function show(Product $product): View
    {
        $product->load(['images.optionValue', 'attributes', 'variants.optionValues.optionType', 'relatedProducts.images', 'optionTypes.values']);

        if (! $this->isVisible($product)) {
            throw new NotFoundHttpException;
        }

        $relatedProducts = $product->relatedProducts->isNotEmpty()
            ? $product->relatedProducts
            : Product::visible()->with('images')->where('category_id', $product->category_id)->where('id', '!=', $product->id)->limit(4)->get();

        return view('storefront.product', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'variantsJson' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
                'compareAtPrice' => $variant->hasGenuineCompareAtPrice() ? (float) $variant->compare_at_price : null,
                'stock' => $variant->stock,
                'isActive' => $variant->is_active,
                'optionValueIds' => $variant->optionValues->pluck('id')->sort()->values()->all(),
                'imageOptionValueId' => $variant->optionValues->pluck('id')->first(),
            ])->values(),
        ]);
    }

    private function isVisible(Product $product): bool
    {
        return $product->status === ProductStatus::Active
            && $product->variants->contains(fn ($v) => $v->is_active);
    }
}
