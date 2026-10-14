<?php

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

it('adds, updates and removes a cart item as a logged-in customer (TC-CRT-001)', function () {
    $user = User::factory()->create();
    $variant = makeVariant(['stock' => 20]);

    $this->actingAs($user)->post(route('cart.store'), ['product_variant_id' => $variant->id, 'quantity' => 1])->assertRedirect();

    $item = $user->cart->items()->where('product_variant_id', $variant->id)->firstOrFail();
    expect($item->quantity)->toBe(1);

    $this->actingAs($user)->put(route('cart.items.update', $item), ['quantity' => 3])->assertRedirect();
    expect($item->refresh()->quantity)->toBe(3);

    $this->actingAs($user)->delete(route('cart.items.destroy', $item))->assertRedirect();
    expect(CartItem::find($item->id))->toBeNull();
});

it('refuses a quantity above available stock (TC-CRT-003)', function () {
    $user = User::factory()->create();
    $variant = makeVariant(['stock' => 4]);

    $this->actingAs($user)->post(route('cart.store'), ['product_variant_id' => $variant->id, 'quantity' => 5])
        ->assertSessionHasErrors('quantity');

    expect(CartItem::where('product_variant_id', $variant->id)->exists())->toBeFalse();
});

it('enforces the per-line maximum quantity (TC-CRT-004, VR-06)', function () {
    $user = User::factory()->create();
    $variant = makeVariant(['stock' => 100]);

    $this->actingAs($user)->post(route('cart.store'), ['product_variant_id' => $variant->id, 'quantity' => 11])
        ->assertSessionHasErrors('quantity');
});

it('flags an out-of-stock line in the cart and blocks the checkout button (TC-CRT-007)', function () {
    $user = User::factory()->create();
    $variant = makeVariant(['stock' => 2]);
    $this->actingAs($user)->post(route('cart.store'), ['product_variant_id' => $variant->id, 'quantity' => 2]);

    $variant->update(['stock' => 0]);

    $this->actingAs($user)->get(route('cart.show'))
        ->assertOk()
        ->assertSee('Out of stock')
        ->assertSee('Remove unavailable items to continue');
});

it('shows a notice when the price changed since the item was added (TC-CRT-005)', function () {
    $user = User::factory()->create();
    $variant = makeVariant(['stock' => 10, 'price' => 2790]);
    $this->actingAs($user)->post(route('cart.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);

    $variant->update(['price' => 2990]);

    $this->actingAs($user)->get(route('cart.show'))->assertOk()->assertSee('Price changed from ৳2790.00 to ৳2990.00');
});

it('persists a guest cart via its cookie across requests, and merges it into the customer cart on login (TC-CRT-002, TC-CRT-009)', function () {
    $black = makeVariant(['sku' => 'BLK', 'stock' => 24]);
    $white = makeVariant(['sku' => 'WHT', 'stock' => 24]);

    $user = User::factory()->create(['password' => Hash::make('Password123!')]);
    $user->cart()->create(['token' => (string) Str::random(40)]);
    $user->cart->items()->create(['product_variant_id' => $black->id, 'quantity' => 1, 'price_at_add' => $black->price]);

    $first = $this->post(route('cart.store'), ['product_variant_id' => $black->id, 'quantity' => 2]);
    $cookie = collect($first->headers->getCookies())->first(fn ($c) => $c->getName() === 'cart_token');
    expect($cookie)->not->toBeNull();
    expect($cookie->isHttpOnly())->toBeTrue();

    $this->withUnencryptedCookie('cart_token', $cookie->getValue())
        ->post(route('cart.store'), ['product_variant_id' => $white->id, 'quantity' => 1])
        ->assertRedirect();

    $this->withUnencryptedCookie('cart_token', $cookie->getValue())
        ->post('/login', ['email' => $user->email, 'password' => 'Password123!'])
        ->assertRedirect();

    $user->refresh();
    expect($user->cart->items()->where('product_variant_id', $black->id)->value('quantity'))->toBe(3)
        ->and($user->cart->items()->where('product_variant_id', $white->id)->value('quantity'))->toBe(1);
});
