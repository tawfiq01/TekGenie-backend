<?php

namespace App\Services\Checkout;

/** One cart/order line as seen by PricingService — amounts in integer poisha. */
final class PricingLine
{
    public function __construct(
        public readonly int $variantId,
        public readonly int $unitPricePoisha,
        public readonly int $quantity,
        public readonly bool $couponEligible = true,
    ) {}

    public function lineTotalPoisha(): int
    {
        return $this->unitPricePoisha * $this->quantity;
    }
}
