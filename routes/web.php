<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('storefront.home');
})->name('home');
