<?php

use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Hash;

it('toggles a product on and off the wishlist (TC-WSH-001)', function () {
    $user = User::factory()->create();
    $product = makeVisibleProduct();

    $this->actingAs($user)->post(route('wishlist.store', $product))->assertRedirect();
    expect(WishlistItem::where('user_id', $user->id)->where('product_id', $product->id)->exists())->toBeTrue();

    $this->actingAs($user)->post(route('wishlist.store', $product))->assertRedirect();
    expect(WishlistItem::where('user_id', $user->id)->where('product_id', $product->id)->exists())->toBeFalse();
});

it('redirects a guest to login and back to the product (TC-WSH-001)', function () {
    $product = makeVisibleProduct();

    $this->post(route('wishlist.store', $product))->assertRedirect(route('login'));

    $productUrl = route('product.show', $product);
    $this->get(route('login', ['redirect' => $productUrl]))->assertSee('name="redirect" value="'.$productUrl.'"', false);

    $user = User::factory()->create(['password' => Hash::make('Password123!')]);
    $this->post('/login', ['email' => $user->email, 'password' => 'Password123!', 'redirect' => $productUrl])
        ->assertRedirect($productUrl);
});

it('shows current price and stock on the wishlist page (TC-WSH-002)', function () {
    $user = User::factory()->create();
    $product = makeVisibleProduct(['name' => 'Wishlist Item'], ['price' => 999, 'stock' => 0]);
    WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)->get(route('wishlist.index'))
        ->assertOk()
        ->assertSee('Wishlist Item')
        ->assertSee('999')
        ->assertSee('Out of stock');
});

it('removes an item from the wishlist', function () {
    $user = User::factory()->create();
    $product = makeVisibleProduct();
    WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)->delete(route('wishlist.destroy', $product))->assertRedirect();

    expect(WishlistItem::where('user_id', $user->id)->exists())->toBeFalse();
});
