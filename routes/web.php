<?php

use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\ContactController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\NewsletterController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\StaticPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->get('/account', function () {
    return view('account.placeholder');
})->name('account.dashboard');

Route::get('/shop', [CategoryController::class, 'all'])->name('shop.index');
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/page/{page:slug}', [StaticPageController::class, 'show'])->name('page.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->middleware('throttle:10,1')->name('newsletter.store');
