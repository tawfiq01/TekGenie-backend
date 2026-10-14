<?php

use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\AccountOrderController;
use App\Http\Controllers\Storefront\AddressController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\ContactController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\NewsletterController;
use App\Http\Controllers\Storefront\OrderConfirmationController;
use App\Http\Controllers\Storefront\OrderTrackingController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\StaticPageController;
use App\Http\Controllers\Storefront\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/track-order', [OrderTrackingController::class, 'show'])->name('orders.track');
Route::post('/track-order', [OrderTrackingController::class, 'lookup'])->middleware('throttle:7,1')->name('orders.track.lookup');

Route::middleware(['auth', 'auth.session'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::delete('/', [AccountController::class, 'destroy'])->name('destroy');

    Route::get('/orders', [AccountOrderController::class, 'index'])->name('orders.index');

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::get('/addresses/create', [AddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::put('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');
});

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::post('/product/{product:slug}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::get('/shop', [CategoryController::class, 'all'])->name('shop.index');
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/page/{page:slug}', [StaticPageController::class, 'show'])->name('page.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->middleware('throttle:10,1')->name('newsletter.store');

Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.store');
Route::put('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:20,1')->name('checkout.store');
Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
Route::delete('/checkout/coupon', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
Route::get('/checkout/confirmation/{order}', [OrderConfirmationController::class, 'show'])->name('checkout.confirmation');
Route::post('/orders/{order}/cancel', [OrderConfirmationController::class, 'cancel'])->name('orders.cancel');
