<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddCartItemRequest;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** FR-CRT-001..006. */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function show(): View
    {
        $cart = $this->cart->current();

        return view('storefront.cart', [
            'cart' => $cart,
            'items' => $cart->items()->with('variant.product.images')->get(),
        ]);
    }

    public function store(AddCartItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $variant = ProductVariant::query()->findOrFail($data['product_variant_id']);

        $this->cart->addItem($variant, (int) $data['quantity']);

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($cartItem);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        $this->cart->updateQuantity($cartItem, (int) $data['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($cartItem);

        $this->cart->removeItem($cartItem);

        return back()->with('status', 'Item removed.');
    }

    private function authorizeCartItem(CartItem $cartItem): void
    {
        if ($cartItem->cart_id !== $this->cart->current()->id) {
            throw new NotFoundHttpException;
        }
    }
}
