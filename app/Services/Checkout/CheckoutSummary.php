<?php

namespace App\Services\Checkout;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use Illuminate\Support\Collection;

/** What the checkout page shows and place-order recomputes to compare against. */
final class CheckoutSummary
{
    /** @param  Collection<int, CartItem>  $items */
    public function __construct(
        public readonly Collection $items,
        public readonly ?DeliveryZone $zone,
        public readonly ?Coupon $coupon,
        public readonly ?string $couponError,
        public readonly PricingResult $pricing,
    ) {}
}
