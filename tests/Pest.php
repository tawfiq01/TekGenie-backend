<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\CheckoutToken;
use App\Models\DeliveryZone;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\VariantService;
use Database\Seeders\BangladeshGeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Shared Test Helpers
|--------------------------------------------------------------------------
*/

/**
 * A storefront-visible Product (Active status, one active variant) — the
 * minimum fixture every listing/search/detail test needs (Product::visible()).
 *
 * @param  array<string, mixed>  $attributes
 * @param  array<string, mixed>  $variantAttributes
 */
function makeVisibleProduct(array $attributes = [], array $variantAttributes = []): Product
{
    $product = Product::factory()->active()->create($attributes);
    $product->variants()->create(array_merge([
        'sku' => 'SKU-'.$product->id, 'price' => 500, 'stock' => 10, 'is_active' => true, 'option_signature' => '',
    ], $variantAttributes));

    // Production code always creates variants through VariantService, which
    // recalculates these — do the same here so min_price/max_price/in_stock
    // aren't left null and listing filters behave as they would for real.
    app(VariantService::class)->recalcProductAggregates($product);

    return $product->refresh();
}

/**
 * A simple, saleable ProductVariant on its own fresh product/category — for
 * cart/checkout tests that only care about one variant's price and stock.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeVariant(array $attributes = []): ProductVariant
{
    $product = Product::factory()->active()->create(['category_id' => Category::factory()->create()->id]);

    return $product->variants()->create(array_merge([
        'sku' => 'SKU-'.$product->id, 'price' => 500, 'stock' => 10, 'is_active' => true, 'option_signature' => '',
    ], $attributes));
}

/** A delivery zone covering "Dhaka" (seeded by BangladeshGeoSeeder), for checkout/order tests. */
function dhakaZone(int $baseCharge = 60, ?float $freeThreshold = null): DeliveryZone
{
    $zone = DeliveryZone::create(['name' => 'Inside Dhaka '.uniqid(), 'base_charge' => $baseCharge, 'free_delivery_threshold' => $freeThreshold, 'is_active' => true]);
    District::where('name', 'Dhaka')->update(['delivery_zone_id' => $zone->id]);

    return $zone;
}

function checkoutToken(Cart $cart): CheckoutToken
{
    return CheckoutToken::create(['token' => Str::random(40), 'cart_id' => $cart->id, 'session_id' => 'test', 'expires_at' => now()->addHours(2)]);
}

/**
 * Adds an item to a fresh guest cart and returns it along with the
 * cart_token cookie value — the test client does not persist custom
 * cookies across requests on its own, so every later guest request for
 * this same cart must resend it via withUnencryptedCookie('cart_token',
 * $cookie): the captured value is already the encrypted wire value, and
 * withCookie() would encrypt it a second time.
 *
 * @return array{0: Cart, 1: string}
 */
function addToGuestCart(int $variantId, int $quantity): array
{
    $response = test()->post(route('cart.store'), ['product_variant_id' => $variantId, 'quantity' => $quantity]);
    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'cart_token');
    $cart = Cart::whereNull('user_id')->latest('id')->first();

    return [$cart, $cookie->getValue()];
}

/**
 * Places a real guest COD order through the checkout flow (seeding BD
 * geography and a Dhaka delivery zone first) — for Orders/Payments/Returns
 * tests that need a realistic Order with its items and Payment row already
 * in place, rather than re-deriving CheckoutService's own logic by hand.
 *
 * @param  array<string, mixed>  $variantAttributes
 * @param  array<string, mixed>  $customerOverrides
 */
function placeTestOrder(array $variantAttributes = [], int $quantity = 1, array $customerOverrides = []): Order
{
    if (District::count() === 0) {
        test()->seed(BangladeshGeoSeeder::class);
    }

    if (DeliveryZone::where('name', 'like', 'Inside Dhaka%')->doesntExist()) {
        dhakaZone();
    }
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $variant = makeVariant(array_merge(['stock' => 10], $variantAttributes));

    [$cart, $cookie] = addToGuestCart($variant->id, $quantity);
    $token = checkoutToken($cart);

    test()->withUnencryptedCookie('cart_token', $cookie)->post(route('checkout.store'), array_merge([
        'token' => $token->token,
        'name' => 'Karim Ahmed',
        'phone' => '01712345678',
        'district_id' => $district->id,
        'address_line' => 'Somewhere',
        'expected_prices' => [$variant->id => (string) $variant->price],
    ], $customerOverrides))->assertRedirect();

    return Order::with('items')->latest('id')->firstOrFail();
}
