<?php

namespace App\Services\Reviews;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;

/** FR-WSH-001/002. */
class WishlistService
{
    /** True if the product is now on the wishlist, false if it was just removed. */
    public function toggle(User $user, Product $product): bool
    {
        $existing = WishlistItem::where('user_id', $user->id)->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

        return true;
    }

    public function remove(User $user, Product $product): void
    {
        WishlistItem::where('user_id', $user->id)->where('product_id', $product->id)->delete();
    }

    public function contains(User $user, Product $product): bool
    {
        return WishlistItem::where('user_id', $user->id)->where('product_id', $product->id)->exists();
    }
}
