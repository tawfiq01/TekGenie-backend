<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\District;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Setting;
use Database\Seeders\BangladeshGeoSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('creates an Unpaid COD payment record when the order is placed (TC-PAY-001)', function () {
    $order = placeTestOrder();

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    expect($payment->method)->toBe(PaymentMethod::Cod)
        ->and($payment->status)->toBe(PaymentStatus::Unpaid)
        ->and((float) $payment->amount)->toBe((float) $order->grand_total);
});

it('blocks checkout when COD is disabled (TC-PAY-002)', function () {
    Setting::updateOrCreate(['group' => 'payments', 'key' => 'cod_enabled'], ['value' => '0']);

    $this->seed(BangladeshGeoSeeder::class);
    dhakaZone();
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(['stock' => 5]);
    [$cart, $cookie] = addToGuestCart($variant->id, 1);
    $token = checkoutToken($cart);

    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), [
        'token' => $token->token, 'name' => 'Karim Ahmed', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => (string) $variant->price],
    ])->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0);
});

it('records a COD collection matching the order total (FR-PAY-003/UC-22)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
        $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => $status->value]);
    }

    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.collect', $order), [
        'collected_amount' => $order->grand_total,
    ])->assertRedirect(route('admin.orders.show', $order));

    $payment = Payment::where('order_id', $order->id)->firstOrFail();
    expect($payment->status)->toBe(PaymentStatus::Paid)
        ->and($payment->recorded_by)->toBe($admin->id)
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

it('requires a reason when the collected amount differs from the order total (TC-PAY-003)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.collect', $order), [
        'collected_amount' => (float) $order->grand_total - 20,
    ])->assertSessionHasErrors('variance_note');

    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.collect', $order), [
        'collected_amount' => (float) $order->grand_total - 20,
        'variance_note' => 'Customer paid short, agreed to settle next time',
    ])->assertRedirect(route('admin.orders.show', $order));

    expect(Payment::where('order_id', $order->id)->first()->status)->toBe(PaymentStatus::Paid);
});

it('records a full refund and marks the order Refunded (TC-ORD-015)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');
    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.collect', $order), ['collected_amount' => $order->grand_total]);

    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.refund', $order), [
        'amount' => $order->grand_total,
        'method' => 'bkash_personal',
        'reference' => 'TXN123',
    ])->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Refunded)
        ->and(Refund::where('order_id', $order->id)->count())->toBe(1);
});

it('marks a partial refund as Partially Refunded and refuses an over-refund (TC-ORD-015)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');
    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.collect', $order), ['collected_amount' => $order->grand_total]);

    $half = round((float) $order->grand_total / 2, 2);

    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.refund', $order), [
        'amount' => $half, 'method' => 'cash',
    ])->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PartiallyRefunded);

    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.refund', $order), [
        'amount' => (float) $order->grand_total, 'method' => 'cash',
    ])->assertSessionHasErrors('amount');
});

it('refuses a refund when no payment has been collected yet', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.refund', $order), [
        'amount' => 100, 'method' => 'cash',
    ])->assertSessionHasErrors('amount');
});

it('requires orders.payments permission to record collection or refunds', function () {
    $order = placeTestOrder();
    $viewer = Admin::factory()->create();
    $viewer->assignRole('Catalog Manager');

    $this->actingAs($viewer, 'admin')->post(route('admin.orders.payments.collect', $order), ['collected_amount' => 100])->assertForbidden();
    $this->actingAs($viewer, 'admin')->post(route('admin.orders.payments.refund', $order), ['amount' => 10, 'method' => 'cash'])->assertForbidden();
});
