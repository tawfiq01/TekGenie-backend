<?php

namespace App\Services\Checkout;

/** Result of a PricingService calculation — amounts in integer poisha. */
final class PricingResult
{
    /** @param  array<int, int>  $lineDiscountsPoisha  variantId => discount allocated to that line */
    public function __construct(
        public readonly int $subtotalPoisha,
        public readonly int $discountPoisha,
        public readonly int $deliveryChargePoisha,
        public readonly int $grandTotalPoisha,
        public readonly array $lineDiscountsPoisha,
    ) {}

    public function subtotal(): string
    {
        return self::toDecimal($this->subtotalPoisha);
    }

    public function discount(): string
    {
        return self::toDecimal($this->discountPoisha);
    }

    public function deliveryCharge(): string
    {
        return self::toDecimal($this->deliveryChargePoisha);
    }

    public function grandTotal(): string
    {
        return self::toDecimal($this->grandTotalPoisha);
    }

    public function lineDiscount(int $variantId): string
    {
        return self::toDecimal($this->lineDiscountsPoisha[$variantId] ?? 0);
    }

    public static function toDecimal(int $poisha): string
    {
        return number_format($poisha / 100, 2, '.', '');
    }
}
