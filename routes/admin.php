<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminStaffController;
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
    });
});
