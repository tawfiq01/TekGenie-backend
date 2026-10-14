<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Reviews\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** FR-WSH-001/002, requires login (routes/web.php gates this group with 'auth'). */
class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist) {}

    public function index(): View
    {
        $items = Auth::guard('web')->user()->wishlistItems()->with('product')->latest('id')->get();

        return view('account.wishlist', ['items' => $items]);
    }

    public function store(Product $product): RedirectResponse
    {
        $added = $this->wishlist->toggle(Auth::guard('web')->user(), $product);

        return back()->with('status', $added ? 'Added to your wishlist.' : 'Removed from your wishlist.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->wishlist->remove(Auth::guard('web')->user(), $product);

        return back()->with('status', 'Removed from your wishlist.');
    }
}
