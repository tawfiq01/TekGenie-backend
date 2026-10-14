<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('storefront.home');
})->name('home');

Route::middleware('auth')->get('/account', function () {
    return view('account.placeholder');
})->name('account.dashboard');
