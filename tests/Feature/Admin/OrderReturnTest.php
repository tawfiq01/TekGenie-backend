<?php

use App\Enums\OrderStatus;
use App\Enums\ReturnType;
use App\Enums\StockMovementType;
use App\Models\Admin;
use App\Models\OrderReturn;
use App\Models\StockMovement;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('restocks resaleable lines and writes off damaged lines without changing stock (TC-ORD-012)', function () {
    $order = placeTestOrder(['stock' => 5], 3);
    $item = $order->items->first();
    $variant = $item->variant;
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Confirmed->value]);
    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Processing->value]);
    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Shipped->value]);

    $stockBeforeReturn = $variant->fresh()->stock; // 2 (5 - 3 sold)

    $this->actingAs($admin, 'admin')->post(route('admin.orders.returns.store', $order), [
        'type' => ReturnType::DeliveryFailed->value,
        'reason' => 'Customer refused delivery',
        'lines' => [
            $item->id => ['quantity' => 2, 'condition' => 'resaleable'],
        ],
    ])->assertRedirect(route('admin.orders.show', $order));

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Returned)
        ->and($variant->fresh()->stock)->toBe($stockBeforeReturn + 2)
        ->and($item->fresh()->returned_quantity)->toBe(2);

    $return = OrderReturn::where('order_id', $order->id)->firstOrFail();
    expect($return->type)->toBe(ReturnType::DeliveryFailed)
        ->and($return->processed_by)->toBe($admin->id);
});

it('writes off a damaged line without changing the sellable stock balance', function () {
    $order = placeTestOrder(['stock' => 5], 1);
    $item = $order->items->first();
    $variant = $item->variant;
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
        $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => $status->value]);
    }

    $stockBeforeReturn = $variant->fresh()->stock;

    $this->actingAs($admin, 'admin')->post(route('admin.orders.returns.store', $order), [
        'type' => ReturnType::CustomerReturn->value,
        'reason' => 'Arrived damaged',
        'lines' => [
            $item->id => ['quantity' => 1, 'condition' => 'damaged'],
        ],
    ])->assertRedirect(route('admin.orders.show', $order));

    expect($variant->fresh()->stock)->toBe($stockBeforeReturn)
        ->and($order->fresh()->status)->toBe(OrderStatus::Delivered);

    $movement = StockMovement::where('order_id', $order->id)->where('type', StockMovementType::ReturnWriteoff)->first();
    expect($movement)->not->toBeNull()
        ->and($movement->quantity)->toBe(-1)
        ->and($movement->affects_stock)->toBeFalse();
});

it('keeps the order Delivered after a partial customer return (TC-ORD-013)', function () {
    $order = placeTestOrder(['stock' => 10], 3);
    $item = $order->items->first();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
        $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => $status->value]);
    }

    $this->actingAs($admin, 'admin')->post(route('admin.orders.returns.store', $order), [
        'type' => ReturnType::CustomerReturn->value,
        'reason' => 'Changed mind about one unit',
        'lines' => [
            $item->id => ['quantity' => 1, 'condition' => 'resaleable'],
        ],
    ])->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered)
        ->and($item->fresh()->returned_quantity)->toBe(1);
});

it('refuses to return more than was ordered (TC-ORD-014)', function () {
    $order = placeTestOrder(['stock' => 10], 2);
    $item = $order->items->first();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Shipped] as $status) {
        $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => $status->value]);
    }

    $this->actingAs($admin, 'admin')->post(route('admin.orders.returns.store', $order), [
        'type' => ReturnType::DeliveryFailed->value,
        'reason' => 'Refused',
        'lines' => [
            $item->id => ['quantity' => 5, 'condition' => 'resaleable'],
        ],
    ])->assertSessionHasErrors('lines');

    expect($order->fresh()->status)->toBe(OrderStatus::Shipped)
        ->and($item->fresh()->returned_quantity)->toBe(0);
});

it('refuses a delivery-failed return unless the order is Shipped', function () {
    $order = placeTestOrder();
    $item = $order->items->first();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')->post(route('admin.orders.returns.store', $order), [
        'type' => ReturnType::DeliveryFailed->value,
        'reason' => 'Refused',
        'lines' => [$item->id => ['quantity' => 1, 'condition' => 'resaleable']],
    ])->assertSessionHasErrors('type');
});

it('requires orders.returns permission to process a return', function () {
    $order = placeTestOrder();
    $item = $order->items->first();
    $viewer = Admin::factory()->create();
    $viewer->assignRole('Catalog Manager');

    $this->actingAs($viewer, 'admin')->post(route('admin.orders.returns.store', $order), [
        'type' => ReturnType::DeliveryFailed->value,
        'reason' => 'x',
        'lines' => [$item->id => ['quantity' => 1, 'condition' => 'resaleable']],
    ])->assertForbidden();
});
