<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\OptionTypeController;
use App\Http\Controllers\Admin\OptionValueController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderPaymentController;
use App\Http\Controllers\Admin\OrderReturnController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:admin', 'admin.idle'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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

        Route::middleware('permission:inventory.adjust')->group(function () {
            Route::get('inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
            Route::get('inventory/{variant}/history', [AdminInventoryController::class, 'history'])->name('inventory.history');
            Route::post('inventory/{variant}/adjust', [AdminInventoryController::class, 'adjust'])->name('inventory.adjust');
        });

        Route::middleware('permission:content.manage')->group(function () {
            Route::resource('banners', BannerController::class)->except(['show']);

            Route::get('home-sections', [HomeSectionController::class, 'index'])->name('home-sections.index');
            Route::put('home-sections', [HomeSectionController::class, 'update'])->name('home-sections.update');

            Route::get('pages', [PageController::class, 'index'])->name('pages.index');
            Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
            Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');
        });

        Route::middleware('permission:shipping.manage')->group(function () {
            Route::get('delivery-zones/districts', [DeliveryZoneController::class, 'districts'])->name('delivery-zones.districts');
            Route::put('delivery-zones/districts', [DeliveryZoneController::class, 'assignDistricts'])->name('delivery-zones.districts.assign');
            Route::post('delivery-zones/areas', [DeliveryZoneController::class, 'storeArea'])->name('delivery-zones.areas.store');

            Route::resource('delivery-zones', DeliveryZoneController::class)
                ->only(['index', 'create', 'store', 'edit', 'update'])
                ->parameters(['delivery-zones' => 'deliveryZone']);
        });

        Route::middleware('permission:coupons.manage')->group(function () {
            Route::resource('coupons', CouponController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        });

        Route::middleware('permission:orders.view')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('orders/{order}/packing-slip', [OrderController::class, 'packingSlip'])->name('orders.packing-slip');
        });

        Route::middleware('permission:orders.update-status')->group(function () {
            Route::post('orders/{order}/status', [OrderController::class, 'transition'])->name('orders.status.update');
            Route::post('orders/bulk-status', [OrderController::class, 'bulkTransition'])->name('orders.status.bulk');
            Route::post('orders/{order}/notes', [OrderController::class, 'storeNote'])->name('orders.notes.store');
            Route::put('orders/{order}/courier', [OrderController::class, 'updateCourier'])->name('orders.courier.update');
            Route::put('orders/{order}/address', [OrderController::class, 'updateAddress'])->name('orders.address.update');
        });

        Route::middleware('permission:orders.cancel')->group(function () {
            Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        });

        Route::middleware('permission:orders.returns')->group(function () {
            Route::post('orders/{order}/returns', [OrderReturnController::class, 'store'])->name('orders.returns.store');
        });

        Route::middleware('permission:orders.payments')->group(function () {
            Route::post('orders/{order}/collect', [OrderPaymentController::class, 'storeCollection'])->name('orders.payments.collect');
            Route::post('orders/{order}/refunds', [OrderPaymentController::class, 'storeRefund'])->name('orders.payments.refund');
        });

        Route::middleware('permission:reviews.moderate')->group(function () {
            Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
            Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
            Route::post('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
            Route::post('reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
        });

        Route::middleware('permission:reports.view')->prefix('reports')->name('reports.')->group(function () {
            Route::get('sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
            Route::get('products', [ReportController::class, 'products'])->name('products');
            Route::get('products/export', [ReportController::class, 'exportProducts'])->name('products.export');
            Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
            Route::get('inventory/export', [ReportController::class, 'exportInventory'])->name('inventory.export');
            Route::get('coupons', [ReportController::class, 'coupons'])->name('coupons');
            Route::get('coupons/export', [ReportController::class, 'exportCoupons'])->name('coupons.export');
        });

        Route::middleware('permission:settings.manage')->prefix('settings')->name('settings.')->group(function () {
            Route::get('store', [SettingController::class, 'store'])->name('store');
            Route::put('store', [SettingController::class, 'updateStore'])->name('store.update');
            Route::get('payments', [SettingController::class, 'payments'])->name('payments');
            Route::put('payments', [SettingController::class, 'updatePayments'])->name('payments.update');
            Route::get('notifications', [SettingController::class, 'notifications'])->name('notifications');
            Route::put('notifications', [SettingController::class, 'updateNotifications'])->name('notifications.update');
        });

        Route::middleware('permission:settings.seo')->prefix('settings')->name('settings.')->group(function () {
            Route::get('seo', [SettingController::class, 'seo'])->name('seo');
            Route::put('seo', [SettingController::class, 'updateSeo'])->name('seo.update');
        });
    });
});
