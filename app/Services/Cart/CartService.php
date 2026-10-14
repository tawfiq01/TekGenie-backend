<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * FR-CRT-001..007. Guests are identified by a secure, HttpOnly cart-token
 * cookie; customers by their account. price_at_add is set only when a line
 * is first created — it is the baseline FR-CRT-004's "price changed" notice
 * compares against, never the charged price (which is always variant.price).
 */
class CartService
{
    private const COOKIE_NAME = 'cart_token';

    private ?Cart $cart = null;

    public function current(): Cart
    {
        if ($this->cart !== null) {
            return $this->cart;
        }

        $cart = Auth::guard('web')->check() ? $this->currentUserCart() : $this->currentGuestCart();
        $cart->forceFill(['last_activity_at' => now()])->save();

        return $this->cart = $cart;
    }

    /** Finds the cart without creating one — for header/mini-cart display only. */
    public function peek(): ?Cart
    {
        if (Auth::guard('web')->check()) {
            return Cart::query()->where('user_id', Auth::guard('web')->id())->first();
        }

        $token = request()->cookie(self::COOKIE_NAME);

        return $token ? Cart::query()->where('token', $token)->whereNull('user_id')->first() : null;
    }

    /** @return array{count: int, subtotal: string} */
    public function summary(): array
    {
        $cart = $this->peek();

        if (! $cart) {
            return ['count' => 0, 'subtotal' => '0.00'];
        }

        $items = $cart->items()->with('variant')->get();
        $subtotalPoisha = $items->sum(fn (CartItem $item) => (int) round(((float) $item->variant->price) * 100) * $item->quantity);

        return [
            'count' => (int) $items->sum('quantity'),
            'subtotal' => number_format($subtotalPoisha / 100, 2, '.', ''),
        ];
    }

    public function addItem(ProductVariant $variant, int $quantity): CartItem
    {
        $cart = $this->current();
        $existing = $cart->items()->where('product_variant_id', $variant->id)->first();
        $newQuantity = ($existing->quantity ?? 0) + $quantity;

        $this->assertQuantityAllowed($newQuantity, $variant);

        if ($existing) {
            $existing->update(['quantity' => $newQuantity]);

            return $existing;
        }

        return $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $newQuantity,
            'price_at_add' => $variant->price,
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        $this->assertQuantityAllowed($quantity, $item->variant);
        $item->update(['quantity' => $quantity]);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function mergeGuestCartIntoUser(User $user): void
    {
        $token = request()->cookie(self::COOKIE_NAME);

        if (! $token) {
            return;
        }

        $guestCart = Cart::query()->where('token', $token)->whereNull('user_id')->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::query()->firstOrCreate(['user_id' => $user->id], ['token' => (string) Str::random(40)]);

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items()->with('variant')->get() as $guestItem) {
                $existing = $userCart->items()->where('product_variant_id', $guestItem->product_variant_id)->first();
                $mergedQuantity = min(($existing->quantity ?? 0) + $guestItem->quantity, $guestItem->variant->stock);

                if ($mergedQuantity < 1) {
                    continue;
                }

                if ($existing) {
                    $existing->update(['quantity' => $mergedQuantity]);
                } else {
                    $userCart->items()->create([
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $mergedQuantity,
                        'price_at_add' => $guestItem->price_at_add,
                    ]);
                }
            }

            $guestCart->delete();
        });

        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
        $this->cart = null;
    }

    private function currentUserCart(): Cart
    {
        return Cart::query()->firstOrCreate(
            ['user_id' => Auth::guard('web')->id()],
            ['token' => (string) Str::random(40)]
        );
    }

    private function currentGuestCart(): Cart
    {
        $token = request()->cookie(self::COOKIE_NAME);
        $cart = $token ? Cart::query()->where('token', $token)->whereNull('user_id')->first() : null;

        if ($cart) {
            return $cart;
        }

        $cart = Cart::create(['token' => (string) Str::random(40)]);

        Cookie::queue(Cookie::make(
            self::COOKIE_NAME,
            $cart->token,
            60 * 24 * 30,
            httpOnly: true,
            sameSite: 'lax'
        ));

        return $cart;
    }

    private function assertQuantityAllowed(int $quantity, ProductVariant $variant): void
    {
        $perLineMax = (int) Setting::get('checkout', 'per_line_max_quantity', 10);

        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Enter a quantity of at least 1.']);
        }

        if ($quantity > $perLineMax) {
            throw ValidationException::withMessages(['quantity' => "You can order at most {$perLineMax} of this item."]);
        }

        if ($quantity > $variant->stock) {
            throw ValidationException::withMessages(['quantity' => "Only {$variant->stock} available."]);
        }
    }
}
