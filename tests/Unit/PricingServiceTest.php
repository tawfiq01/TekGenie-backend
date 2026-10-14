<?php

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Services\Checkout\PricingLine;
use App\Services\Checkout\PricingService;

beforeEach(function () {
    $this->pricing = new PricingService;
});

it('computes subtotal, delivery charge and grand total with no coupon', function () {
    $lines = [new PricingLine(variantId: 1, unitPricePoisha: 279000, quantity: 1)];
    $zone = DeliveryZone::make(['base_charge' => 60, 'free_delivery_threshold' => null]);

    $result = $this->pricing->calculate($lines, null, $zone);

    expect($result->subtotal())->toBe('2790.00')
        ->and($result->discount())->toBe('0.00')
        ->and($result->deliveryCharge())->toBe('60.00')
        ->and($result->grandTotal())->toBe('2850.00');
});

it('applies a percentage coupon with a cap (TC-PRM-001)', function () {
    $lines = [new PricingLine(variantId: 1, unitPricePoisha: 800000, quantity: 1)];
    $coupon = Coupon::make(['type' => CouponType::Percentage, 'value' => 10, 'max_discount' => 500]);

    $result = $this->pricing->calculate($lines, $coupon, null);

    expect($result->discount())->toBe('500.00');
});

it('applies a fixed coupon and a free-delivery coupon (TC-PRM-002)', function () {
    $lines = [new PricingLine(variantId: 1, unitPricePoisha: 150000, quantity: 1)];
    $zone = DeliveryZone::make(['base_charge' => 60, 'free_delivery_threshold' => null]);

    $fixed = Coupon::make(['type' => CouponType::Fixed, 'value' => 200]);
    $fixedResult = $this->pricing->calculate($lines, $fixed, $zone);
    expect($fixedResult->discount())->toBe('200.00');

    $freeDelivery = Coupon::make(['type' => CouponType::FreeDelivery, 'value' => 0]);
    $freeResult = $this->pricing->calculate($lines, $freeDelivery, $zone);
    expect($freeResult->deliveryCharge())->toBe('0.00');
});

it('waives delivery once the post-discount subtotal reaches the free threshold (TC-CHK-004)', function () {
    $lines = [new PricingLine(variantId: 1, unitPricePoisha: 474000, quantity: 1)];
    $zone = DeliveryZone::make(['base_charge' => 60, 'free_delivery_threshold' => 500000 / 100]);

    $below = $this->pricing->calculate($lines, null, $zone);
    expect($below->deliveryCharge())->toBe('60.00');

    $lines[] = new PricingLine(variantId: 2, unitPricePoisha: 36000, quantity: 1);
    $above = $this->pricing->calculate($lines, null, $zone);
    expect($above->deliveryCharge())->toBe('0.00');
});

it('never discounts more than the eligible subtotal', function () {
    $lines = [new PricingLine(variantId: 1, unitPricePoisha: 10000, quantity: 1)];
    $coupon = Coupon::make(['type' => CouponType::Fixed, 'value' => 999]);

    $result = $this->pricing->calculate($lines, $coupon, null);

    expect($result->discount())->toBe('100.00')
        ->and($result->grandTotal())->toBe('0.00');
});

it('only discounts coupon-eligible lines and allocates proportionally (TC-PRM-005)', function () {
    $lines = [
        new PricingLine(variantId: 1, unitPricePoisha: 279000, quantity: 1, couponEligible: true),
        new PricingLine(variantId: 2, unitPricePoisha: 195000, quantity: 1, couponEligible: false),
    ];
    $coupon = Coupon::make(['type' => CouponType::Percentage, 'value' => 10, 'max_discount' => null]);

    $result = $this->pricing->calculate($lines, $coupon, null);

    expect($result->discount())->toBe('279.00')
        ->and($result->lineDiscount(1))->toBe('279.00')
        ->and($result->lineDiscount(2))->toBe('0.00');
});

it('allocates discount across multiple eligible lines with the remainder on the last line', function () {
    $lines = [
        new PricingLine(variantId: 1, unitPricePoisha: 10000, quantity: 1),
        new PricingLine(variantId: 2, unitPricePoisha: 10000, quantity: 1),
        new PricingLine(variantId: 3, unitPricePoisha: 10000, quantity: 1),
    ];
    $coupon = Coupon::make(['type' => CouponType::Fixed, 'value' => 100]);

    $result = $this->pricing->calculate($lines, $coupon, null);

    $sum = (float) $result->lineDiscount(1) + (float) $result->lineDiscount(2) + (float) $result->lineDiscount(3);
    expect(number_format($sum, 2))->toBe('100.00');
});
