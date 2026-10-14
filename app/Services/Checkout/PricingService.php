<?php

namespace App\Services\Checkout;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\DeliveryZone;

/**
 * SRS §6.1 price calculation. BR-02: the server is the only authority for
 * prices — every amount here is computed server-side from DB values, never
 * from client input. All arithmetic is done in integer poisha (never
 * floats); decimal strings are only parsed in and formatted out at the
 * boundary.
 */
class PricingService
{
    /** @param  array<int, PricingLine>  $lines */
    public function calculate(array $lines, ?Coupon $coupon, ?DeliveryZone $zone): PricingResult
    {
        $subtotal = array_sum(array_map(fn (PricingLine $l) => $l->lineTotalPoisha(), $lines));

        $eligibleSubtotal = array_sum(array_map(
            fn (PricingLine $l) => $l->couponEligible ? $l->lineTotalPoisha() : 0,
            $lines
        ));

        $discount = 0;
        $lineDiscounts = [];

        if ($coupon !== null && $eligibleSubtotal > 0) {
            $discount = match ($coupon->type) {
                CouponType::Percentage => $this->cappedDiscount(
                    intdiv($eligibleSubtotal * $this->scaleByHundred($coupon->value), 100 * 100),
                    $coupon->max_discount !== null ? $this->scaleByHundred($coupon->max_discount) : null,
                    $eligibleSubtotal
                ),
                CouponType::Fixed => min($this->scaleByHundred($coupon->value), $eligibleSubtotal),
                CouponType::FreeDelivery => 0,
            };

            if ($discount > 0) {
                $lineDiscounts = $this->allocateDiscount($lines, $discount, $eligibleSubtotal);
            }
        }

        $afterDiscount = $subtotal - $discount;

        $deliveryCharge = $this->resolveDeliveryCharge($zone, $coupon, $afterDiscount);

        $grandTotal = max(0, $afterDiscount + $deliveryCharge);

        return new PricingResult($subtotal, $discount, $deliveryCharge, $grandTotal, $lineDiscounts);
    }

    private function resolveDeliveryCharge(?DeliveryZone $zone, ?Coupon $coupon, int $afterDiscountPoisha): int
    {
        if ($zone === null) {
            return 0;
        }

        if ($coupon?->type === CouponType::FreeDelivery) {
            return 0;
        }

        if ($zone->free_delivery_threshold !== null && $afterDiscountPoisha >= $this->scaleByHundred($zone->free_delivery_threshold)) {
            return 0;
        }

        return $this->scaleByHundred($zone->base_charge);
    }

    private function cappedDiscount(int $amount, ?int $cap, int $eligibleSubtotal): int
    {
        if ($cap !== null) {
            $amount = min($amount, $cap);
        }

        return min($amount, $eligibleSubtotal);
    }

    /**
     * @param  array<int, PricingLine>  $lines
     * @return array<int, int> variantId => discount poisha, proportional to each
     *                         eligible line's share of the eligible subtotal
     *                         (remainder assigned to the last line so the sum
     *                         always equals $discount exactly).
     */
    private function allocateDiscount(array $lines, int $discount, int $eligibleSubtotal): array
    {
        $eligibleLines = array_values(array_filter($lines, fn (PricingLine $l) => $l->couponEligible));
        $allocations = [];
        $allocated = 0;

        foreach ($eligibleLines as $i => $line) {
            if ($i === count($eligibleLines) - 1) {
                $allocations[$line->variantId] = $discount - $allocated;

                break;
            }

            $share = intdiv($line->lineTotalPoisha() * $discount, $eligibleSubtotal);
            $allocations[$line->variantId] = $share;
            $allocated += $share;
        }

        return $allocations;
    }

    /** Converts a DECIMAL(12,2) string (money, or a percentage number) to an integer scaled by 100. */
    private function scaleByHundred(string $decimal): int
    {
        return (int) round(((float) $decimal) * 100);
    }
}
