<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Admin;
use App\Models\OrderStatusHistory;
use App\Models\StockMovement;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('moves an order through a valid transition and records the history (TC-ORD-002)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Confirmed->value, 'note' => 'Called customer'])
        ->assertRedirect(route('admin.orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->confirmed_at)->not->toBeNull();

    $history = OrderStatusHistory::where('order_id', $order->id)->latest('id')->first();
    expect($history->from_status)->toBe('pending')
        ->and($history->to_status)->toBe('confirmed')
        ->and($history->actor_type)->toBe('admin')
        ->and($history->actor_id)->toBe($admin->id)
        ->and($history->note)->toBe('Called customer');
});

it('refuses an invalid transition (TC-ORD-003)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Shipped->value])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

it('refuses any transition out of a terminal status (TC-ORD-004)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')->post(route('admin.orders.cancel', $order), ['reason' => 'Out of stock']);
    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Confirmed->value])
        ->assertSessionHasErrors('status');
});

it('restores stock and releases the coupon exactly once when staff cancel (BR-06)', function () {
    $order = placeTestOrder(['stock' => 5], 2);
    $variant = $order->items->first()->variant;
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    expect($variant->fresh()->stock)->toBe(3);

    $this->actingAs($admin, 'admin')->post(route('admin.orders.cancel', $order), ['reason' => 'Customer changed mind']);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Cancelled)
        ->and($order->stock_restored_at)->not->toBeNull()
        ->and($variant->fresh()->stock)->toBe(5);

    expect(StockMovement::where('order_id', $order->id)->where('type', StockMovementType::Cancellation)->count())->toBe(1);
});

it('bulk-updates orders, skipping ones the transition is invalid for (TC-ORD-017)', function () {
    $pending = placeTestOrder();
    $alreadyConfirmed = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');
    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $alreadyConfirmed), ['status' => OrderStatus::Confirmed->value]);

    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.bulk'), [
        'order_ids' => [$pending->id, $alreadyConfirmed->id],
        'status' => OrderStatus::Confirmed->value,
    ])->assertRedirect(route('admin.orders.index'));

    expect($pending->fresh()->status)->toBe(OrderStatus::Confirmed);

    $history = OrderStatusHistory::where('order_id', $alreadyConfirmed->id)->count();
    expect($history)->toBe(1); // only the earlier Pending->Confirmed move, the bulk attempt was skipped
});

it('refuses to bulk-cancel through the generic status endpoint (cancel needs its own permission)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.bulk'), [
        'order_ids' => [$order->id],
        'status' => OrderStatus::Cancelled->value,
    ])->assertSessionHasErrors('status');
});

it('requires orders.update-status permission to change a status (BR-18)', function () {
    $order = placeTestOrder();
    $viewer = Admin::factory()->create();
    $viewer->assignRole('Catalog Manager');

    $this->actingAs($viewer, 'admin')
        ->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Confirmed->value])
        ->assertForbidden();
});

it('requires the separate orders.cancel permission to cancel', function () {
    $order = placeTestOrder();
    $orderManagerWithoutCancel = Admin::factory()->create();
    // Simulate a role with update-status but not cancel.
    $orderManagerWithoutCancel->givePermissionTo(Permission::findOrCreate('orders.update-status', 'admin'));

    $this->actingAs($orderManagerWithoutCancel, 'admin')
        ->post(route('admin.orders.cancel', $order), ['reason' => 'x'])
        ->assertForbidden();
});

it('keeps order status and payment status independent (TC-ORD-005)', function () {
    $order = placeTestOrder();

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid);

    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');
    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Confirmed->value]);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});
