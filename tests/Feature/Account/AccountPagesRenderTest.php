<?php

use App\Models\Address;
use App\Models\District;
use App\Models\User;
use App\Models\WishlistItem;
use Database\Seeders\BangladeshGeoSeeder;

beforeEach(function () {
    $this->seed(BangladeshGeoSeeder::class);
});

it('renders the account dashboard, profile, addresses and wishlist pages', function () {
    $user = User::factory()->create();
    $order = placeTestOrder();
    $order->update(['user_id' => $user->id]);

    $district = District::firstOrFail();
    $address = Address::create([
        'user_id' => $user->id, 'recipient_name' => 'Karim', 'phone' => '01712345678',
        'division_id' => $district->division_id, 'district_id' => $district->id,
        'address_line' => 'Somewhere', 'is_default' => true,
    ]);

    $product = makeVisibleProduct();
    WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)->get(route('account.dashboard'))->assertOk()->assertSee($order->order_number);
    $this->actingAs($user)->get(route('account.profile'))->assertOk();
    $this->actingAs($user)->get(route('account.addresses.index'))->assertOk()->assertSee('Karim');
    $this->actingAs($user)->get(route('account.addresses.create'))->assertOk();
    $this->actingAs($user)->get(route('account.addresses.edit', $address))->assertOk();
    $this->actingAs($user)->get(route('account.orders.index'))->assertOk()->assertSee($order->order_number);
    $this->actingAs($user)->get(route('wishlist.index'))->assertOk()->assertSee($product->name);
});

it('renders the guest order-tracking form', function () {
    $this->get(route('orders.track'))->assertOk();
});
