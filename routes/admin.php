<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OptionTypeController;
use App\Http\Controllers\Admin\OptionValueController;
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
        });
    });
});
