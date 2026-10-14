<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OptionTypeController;
use App\Http\Controllers\Admin\OptionValueController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:admin', 'admin.idle'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::middleware('permission:staff.manage')->group(function () {
            Route::resource('staff', AdminStaffController::class)
                ->only(['index', 'create', 'store', 'edit', 'update'])
                ->parameters(['staff' => 'admin']);

            Route::resource('roles', AdminRoleController::class)
                ->only(['index', 'create', 'store', 'edit', 'update']);
        });

        Route::middleware('permission:audit.view')->group(function () {
            Route::get('audit-log', [AdminAuditLogController::class, 'index'])->name('audit-log.index');
        });

        Route::middleware('permission:products.view')->group(function () {
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
            Route::get('option-types', [OptionTypeController::class, 'index'])->name('option-types.index');
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
        });

        Route::middleware('permission:products.edit')->group(function () {
            Route::resource('categories', CategoryController::class)->except(['index', 'show']);
            Route::resource('brands', BrandController::class)->except(['index', 'show']);

            Route::get('option-types/create', [OptionTypeController::class, 'create'])->name('option-types.create');
            Route::post('option-types', [OptionTypeController::class, 'store'])->name('option-types.store');
            Route::get('option-types/{optionType}/edit', [OptionTypeController::class, 'edit'])->name('option-types.edit');
            Route::put('option-types/{optionType}', [OptionTypeController::class, 'update'])->name('option-types.update');

            Route::post('option-types/{optionType}/values', [OptionValueController::class, 'store'])->name('option-values.store');
            Route::delete('option-values/{optionValue}', [OptionValueController::class, 'destroy'])->name('option-values.destroy');

            Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
            Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::put('products/{product}/attributes', [ProductController::class, 'updateAttributes'])->name('products.attributes.update');
            Route::put('products/{product}/related', [ProductController::class, 'updateRelated'])->name('products.related.update');

            Route::prefix('products/{product}/variants')->name('products.variants.')->group(function () {
                Route::get('/', [ProductVariantController::class, 'index'])->name('index');
                Route::get('create', [ProductVariantController::class, 'create'])->name('create');
                Route::post('/', [ProductVariantController::class, 'store'])->name('store');
                Route::get('{variant}/edit', [ProductVariantController::class, 'edit'])->name('edit');
                Route::put('{variant}', [ProductVariantController::class, 'update'])->name('update');
            });

            Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
            Route::put('products/{product}/images/reorder', [ProductImageController::class, 'reorder'])->name('products.images.reorder');
            Route::put('images/{productImage}', [ProductImageController::class, 'update'])->name('products.images.update');
            Route::put('images/{productImage}/main', [ProductImageController::class, 'setMain'])->name('products.images.set-main');
            Route::delete('images/{productImage}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        });
    });
});
