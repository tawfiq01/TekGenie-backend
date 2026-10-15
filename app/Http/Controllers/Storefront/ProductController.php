<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ProductStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Reviews\ReviewService;
use App\Services\Reviews\WishlistService;
use App\Services\Seo\SeoMetaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** FR-STF-005/006; FR-CAT-011; FR-WSH-001/002; FR-REV-001/003; FR-SET-002/006. */
class ProductController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlist,
        private readonly ReviewService $reviews,
        private readonly SeoMetaService $seoMeta,
    ) {}

    public function show(Product $product): View
    {
        $product->load(['category', 'brand', 'images.optionValue', 'attributes', 'variants.optionValues.optionType', 'relatedProducts.images', 'optionTypes.values']);

        if (! $this->isVisible($product)) {
            throw new NotFoundHttpException;
        }

        $relatedProducts = $product->relatedProducts->isNotEmpty()
            ? $product->relatedProducts
            : Product::visible()->with('images')->where('category_id', $product->category_id)->where('id', '!=', $product->id)->limit(4)->get();

        // Explicit guard: the storefront is always 'web', regardless of
        // which guard a request elsewhere happens to have defaulted to.
        $user = Auth::guard('web')->user();

        $seo = $this->seoMeta->forProduct($product)->withJsonLd([
            $this->seoMeta->productJsonLd($product),
            $this->seoMeta->breadcrumbJsonLd([
                ['name' => 'Home', 'url' => route('home')],
                ['name' => $product->category->name, 'url' => route('category.show', $product->category)],
                ['name' => $product->name],
            ]),
        ]);

        return view('storefront.product', [
            'product' => $product,
            'seo' => $seo,
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
            'isWishlisted' => $user !== null && $this->wishlist->contains($user, $product),
            'approvedReviews' => $product->reviews()->where('status', ReviewStatus::Approved)->with('user')->latest('id')->paginate(10, pageName: 'reviews_page'),
            'canReview' => $user !== null && $this->reviews->canReview($user, $product),
        ]);
    }

    private function isVisible(Product $product): bool
    {
        return $product->status === ProductStatus::Active
            && $product->variants->contains(fn ($v) => $v->is_active);
    }
}
