<?php

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CheckoutToken;
use App\Models\Coupon;
use App\Models\District;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\BangladeshGeoSeeder;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->seed(BangladeshGeoSeeder::class);
});

it('completes a guest COD checkout happy path (TC-CHK-001)', function () {
    $zone = dhakaZone(60);
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(['price' => 1500, 'stock' => 5]);

    [$cart, $cookie] = addToGuestCart($variant->id, 2);
    $token = checkoutToken($cart);

    $response = $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), [
        'token' => $token->token,
        'name' => 'Rahim Uddin',
        'phone' => '01712345678',
        'district_id' => $district->id,
        'address_line' => 'House 12, Road 5, Dhanmondi',
        'expected_prices' => [$variant->id => '1500.00'],
    ]);

    $order = Order::where('phone', '01712345678')->firstOrFail();
    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->grand_total)->toBe('3060.00')
        ->and($order->order_number)->toMatch('/^ST-\d{6}-[A-Z0-9]{5}$/');

    $response->assertRedirect();
    expect(CartItem::where('cart_id', $cart->id)->count())->toBe(0);
});

it('resolves delivery charge from the zone and waives it above the free threshold (TC-CHK-004)', function () {
    $zone = dhakaZone(60, 3000);
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(['price' => 2000, 'stock' => 10]);

    [$cart, $cookie] = addToGuestCart($variant->id, 1);
    $token = checkoutToken($cart);

    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), [
        'token' => $token->token, 'name' => 'Karim Ahmed', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => '2000.00'],
    ]);

    expect(Order::first()->delivery_charge)->toBe('60.00');

    [$cart2, $cookie2] = addToGuestCart($variant->id, 2);
    $token2 = checkoutToken($cart2);

    $this->withUnencryptedCookie('cart_token', $cookie2)->post(route('checkout.store'), [
        'token' => $token2->token, 'name' => 'Nasrin Akter', 'phone' => '01712345679',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => '2000.00'],
    ]);

    expect(Order::where('phone', '01712345679')->first()->delivery_charge)->toBe('0.00');
});

it('refuses to place an order for an undeliverable district (TC-CHK-005)', function () {
    $district = District::whereNull('delivery_zone_id')->firstOrFail();
    $variant = makeVariant(['stock' => 5]);

    [$cart, $cookie] = addToGuestCart($variant->id, 1);
    $token = checkoutToken($cart);

    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), [
        'token' => $token->token, 'name' => 'Karim Ahmed', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
    ])->assertSessionHasErrors('district_id');

    expect(Order::count())->toBe(0);
});

it('refuses to place an order when stock dropped below the requested quantity, and adjusts the cart (TC-CHK-007)', function () {
    dhakaZone();
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(['stock' => 3]);

    [$cart, $cookie] = addToGuestCart($variant->id, 3);
    $token = checkoutToken($cart);

    $variant->update(['stock' => 1]);

    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), [
        'token' => $token->token, 'name' => 'Karim Ahmed', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => (string) $variant->price],
    ])->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0)
        ->and($variant->fresh()->stock)->toBe(1)
        ->and(CartItem::where('cart_id', $cart->id)->value('quantity'))->toBe(1);
});

it('refuses to place an order when the price changed since the summary was shown (TC-CHK-008)', function () {
    dhakaZone();
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(['price' => 2790, 'stock' => 10]);

    [$cart, $cookie] = addToGuestCart($variant->id, 1);
    $token = checkoutToken($cart);

    $variant->update(['price' => 2990]);

    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), [
        'token' => $token->token, 'name' => 'Karim Ahmed', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => '2790.00'],
    ])->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0);

    $token2 = checkoutToken($cart);
    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), [
        'token' => $token2->token, 'name' => 'Karim Ahmed', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => '2990.00'],
    ])->assertRedirect();

    expect(Order::first()->subtotal)->toBe('2990.00');
});

it('creates exactly one order when the same idempotency token is submitted twice (TC-CHK-010)', function () {
    dhakaZone();
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(['stock' => 10]);

    [$cart, $cookie] = addToGuestCart($variant->id, 1);
    $token = checkoutToken($cart);

    $payload = [
        'token' => $token->token, 'name' => 'Karim Ahmed', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => (string) $variant->price],
    ];

    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), $payload)->assertRedirect();
    $firstOrderId = Order::first()->id;

    // Cart was cleared by the first successful submission; replaying the same
    // token must not try to re-validate an empty cart — it should just redirect
    // to the same order (FR-CHK-008).
    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), $payload)->assertRedirect();

    expect(Order::count())->toBe(1)
        ->and(CheckoutToken::where('token', $token->token)->value('order_id'))->toBe($firstOrderId);
});

it('rejects an expired or invalid coupon with a specific reason (TC-PRM-003)', function () {
    dhakaZone();
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(['price' => 1000, 'stock' => 10]);
    Coupon::create(['code' => 'EXPIRED10', 'type' => CouponType::Percentage, 'value' => 10, 'ends_at' => now()->subDay(), 'is_active' => true]);

    [$cart, $cookie] = addToGuestCart($variant->id, 1);
    $token = checkoutToken($cart);

    $this->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), [
        'token' => $token->token, 'name' => 'Karim Ahmed', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'coupon_code' => 'expired10',
        'expected_prices' => [$variant->id => '1000.00'],
    ])->assertSessionHasErrors('coupon');

    expect(Order::count())->toBe(0);
});

it('only lets the order owner view the confirmation page (TC-CHK-012, BR-19)', function () {
    dhakaZone();
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(['stock' => 10]);
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('cart.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);
    $cart = Cart::where('user_id', $owner->id)->firstOrFail();
    $token = checkoutToken($cart);

    $this->actingAs($owner)->post(route('checkout.store'), [
        'token' => $token->token, 'name' => 'Owner Name', 'phone' => '01712345678',
        'district_id' => $district->id, 'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => (string) $variant->price],
    ])->assertRedirect(route('checkout.confirmation', Order::first()));

    $order = Order::first();

    $this->actingAs($owner)->get(route('checkout.confirmation', $order))->assertOk();

    $other = User::factory()->create();
    $this->actingAs($other)->get(route('checkout.confirmation', $order))->assertNotFound();

    $this->get(route('checkout.confirmation', $order))->assertNotFound();

    $signed = URL::temporarySignedRoute('checkout.confirmation', now()->addHours(24), ['order' => $order->id]);
    $this->get($signed)->assertOk();
});
