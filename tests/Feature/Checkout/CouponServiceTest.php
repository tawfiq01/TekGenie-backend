<?php

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Services\Checkout\CouponService;
use Illuminate\Validation\ValidationException;

function makeOrderForCoupon(): Order
{
    return Order::create([
        'order_number' => 'ST-'.random_int(100000, 999999).'-TEST1',
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'payment_method' => 'cod',
        'customer_name' => 'Test Customer',
        'phone' => '01712345678',
        'division_name' => 'Dhaka',
        'district_name' => 'Dhaka',
        'address_line' => 'Somewhere',
        'delivery_zone_name' => 'Inside Dhaka',
        'subtotal' => '1000.00',
        'discount_total' => '100.00',
        'delivery_charge' => '60.00',
        'grand_total' => '960.00',
        'placed_at' => now(),
    ]);
}

it('counts a usage and lets it be released on cancellation (TC-PRM-006)', function () {
    $coupon = Coupon::create(['code' => 'SAVE10', 'type' => CouponType::Percentage, 'value' => 10, 'usage_limit' => 5, 'is_active' => true]);
    $order = makeOrderForCoupon();

    app(CouponService::class)->recordUsage($coupon, $order, null, '01712345678', '100.00');

    expect($coupon->fresh()->used_count)->toBe(1)
        ->and(CouponUsage::where('order_id', $order->id)->whereNull('released_at')->exists())->toBeTrue();

    app(CouponService::class)->release($order);

    expect($coupon->fresh()->used_count)->toBe(0)
        ->and(CouponUsage::where('order_id', $order->id)->whereNull('released_at')->exists())->toBeFalse();
});

it('refuses to record a usage once the usage limit is already reached (TC-PRM-007)', function () {
    $coupon = Coupon::create(['code' => 'LASTSLOT', 'type' => CouponType::Fixed, 'value' => 50, 'usage_limit' => 1, 'used_count' => 1, 'is_active' => true]);
    $order = makeOrderForCoupon();

    expect(fn () => app(CouponService::class)->recordUsage($coupon, $order, null, '01712345678', '50.00'))
        ->toThrow(ValidationException::class);

    expect($coupon->fresh()->used_count)->toBe(1);
});

it('never lets concurrent usage recording push used_count past the usage limit (TC-PRM-007)', function () {
    $coupon = Coupon::create(['code' => 'RACE', 'type' => CouponType::Fixed, 'value' => 50, 'usage_limit' => 1, 'used_count' => 0, 'is_active' => true]);
    $orderA = makeOrderForCoupon();
    $orderB = makeOrderForCoupon();

    app(CouponService::class)->recordUsage($coupon, $orderA, null, '01712345671', '50.00');

    expect(fn () => app(CouponService::class)->recordUsage($coupon, $orderB, null, '01712345672', '50.00'))
        ->toThrow(ValidationException::class);

    expect($coupon->fresh()->used_count)->toBe(1)
        ->and(CouponUsage::where('coupon_id', $coupon->id)->count())->toBe(1);
});
