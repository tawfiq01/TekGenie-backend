<?php

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\ReturnType;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('reconciles sales report totals against a direct order query (TC-RPT-002)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $kept = placeTestOrder(['price' => 1000], 2); // 2000
    $otherKept = placeTestOrder(['price' => 500], 1); // 500
    $cancelled = placeTestOrder(['price' => 999], 1);
    $this->actingAs($admin, 'admin')->post(route('admin.orders.cancel', $cancelled), ['reason' => 'x']);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.reports.sales'));

    $expectedGross = number_format((float) $kept->grand_total + (float) $otherKept->grand_total, 2, '.', '');
    $response->assertOk()->assertSee('৳'.$expectedGross)->assertSee('৳'.number_format((float) $cancelled->grand_total, 2, '.', ''));
});

it('shows units sold, revenue and returns per product (TC-RPT-003)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $order = placeTestOrder(['price' => 500, 'stock' => 10], 3);
    $item = $order->items->first();

    foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
        $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => $status->value]);
    }
    $this->actingAs($admin, 'admin')->post(route('admin.orders.returns.store', $order), [
        'type' => ReturnType::CustomerReturn->value,
        'reason' => 'Changed mind',
        'lines' => [$item->id => ['quantity' => 1, 'condition' => 'resaleable']],
    ]);

    $this->actingAs($admin, 'admin')->get(route('admin.reports.products'))
        ->assertOk()
        ->assertSee($item->product_name)
        ->assertSee('1500.00') // 3 x 500
        ->assertSeeInOrder([$item->product_name, '3']);
});

it('reports stock on hand, low/out of stock counts and stock value (TC-RPT-003)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $inStock = makeVariant(['stock' => 50, 'price' => 100, 'low_stock_threshold' => 5]);
    $low = makeVariant(['stock' => 3, 'price' => 200, 'low_stock_threshold' => 5]);
    $out = makeVariant(['stock' => 0, 'price' => 300]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.reports.inventory'));

    $response->assertOk()
        ->assertSee($inStock->sku)->assertSee($low->sku)->assertSee($out->sku)
        ->assertSee('৳'.number_format(50 * 100 + 3 * 200 + 0 * 300, 2, '.', ''));
});

it('shows coupon usage count and total discount matching recorded usages (TC-PRM-008)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $coupon = Coupon::create(['code' => 'SAVE10', 'type' => CouponType::Percentage, 'value' => 10, 'is_active' => true]);
    $orderA = placeTestOrder();
    $orderB = placeTestOrder();
    CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $orderA->id, 'phone' => '01712345671', 'discount_amount' => '474.00']);
    CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $orderB->id, 'phone' => '01712345672', 'discount_amount' => '300.00']);

    $this->actingAs($admin, 'admin')->get(route('admin.reports.coupons'))
        ->assertOk()
        ->assertSee('SAVE10')
        ->assertSeeInOrder(['SAVE10', '2'])
        ->assertSee('774.00');
});

it('excludes released coupon usages from the report', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $coupon = Coupon::create(['code' => 'RELEASED', 'type' => CouponType::Fixed, 'value' => 50, 'is_active' => true]);
    $order = placeTestOrder();
    CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $order->id, 'phone' => '01712345671', 'discount_amount' => '50.00', 'released_at' => now()]);

    $this->actingAs($admin, 'admin')->get(route('admin.reports.coupons'))->assertOk()->assertDontSee('RELEASED');
});

it('exports a CSV with formula-injection-safe cells and logs the export (TC-RPT-004)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $coupon = Coupon::create(['code' => '=cmd|/c calc', 'type' => CouponType::Fixed, 'value' => 10, 'is_active' => true]);
    $order = placeTestOrder();
    CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $order->id, 'phone' => '01712345671', 'discount_amount' => '10.00']);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.reports.coupons.export'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
    expect($response->streamedContent())->toContain("'=cmd|/c calc");

    expect(AuditLog::where('action', 'admin.report.exported')->where('actor_id', $admin->id)->exists())->toBeTrue();
});

it('refuses report access and export to staff without reports.view (TC-RPT-004)', function () {
    $contentEditor = Admin::factory()->create();
    $contentEditor->assignRole('Content Editor');

    $this->actingAs($contentEditor, 'admin')->get(route('admin.reports.sales'))->assertForbidden();
    $this->actingAs($contentEditor, 'admin')->get(route('admin.reports.sales.export'))->assertForbidden();
});
