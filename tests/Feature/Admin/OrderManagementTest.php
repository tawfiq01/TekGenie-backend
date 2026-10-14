<?php

use App\Enums\OrderStatus;
use App\Enums\ReturnType;
use App\Models\Admin;
use App\Models\OrderNote;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('renders the order detail page at every stage with full relationships loaded', function () {
    $order = placeTestOrder(['stock' => 10], 2);
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $this->actingAs($admin, 'admin')->get(route('admin.orders.show', $order))->assertOk()->assertSee($order->order_number);

    foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
        $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => $status->value]);
        $this->actingAs($admin, 'admin')->get(route('admin.orders.show', $order))->assertOk()->assertSee($status->label());
    }

    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.collect', $order), ['collected_amount' => $order->grand_total]);
    $this->actingAs($admin, 'admin')->post(route('admin.orders.payments.refund', $order), ['amount' => $order->grand_total, 'method' => 'cash']);

    $item = $order->items->first();
    $this->actingAs($admin, 'admin')->post(route('admin.orders.returns.store', $order), [
        'type' => ReturnType::CustomerReturn->value,
        'reason' => 'Changed mind',
        'lines' => [$item->id => ['quantity' => 1, 'condition' => 'damaged']],
    ]);

    $this->actingAs($admin, 'admin')->get(route('admin.orders.show', $order))->assertOk();
});

it('searches orders by order number, phone and customer name (TC-ORD-006)', function () {
    $target = placeTestOrder([], 1, ['name' => 'Rahim Uddin', 'phone' => '01799999999']);
    placeTestOrder([], 1, ['name' => 'Someone Else', 'phone' => '01711111111']);

    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.index', ['search' => $target->order_number]))
        ->assertOk()
        ->assertSee($target->order_number)
        ->assertDontSee('01711111111');
});

it('filters orders by status and payment status', function () {
    $pending = placeTestOrder();
    $confirmed = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');
    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $confirmed), ['status' => OrderStatus::Confirmed->value]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.orders.index', ['status' => 'confirmed']));
    $response->assertOk()->assertSee($confirmed->order_number)->assertDontSee($pending->order_number);
});

it('adds a staff-only note that the customer never sees (TC-ORD-007/FR-ORD-011)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.orders.notes.store', $order), ['body' => 'Called twice, no answer'])
        ->assertRedirect(route('admin.orders.show', $order));

    expect(OrderNote::where('order_id', $order->id)->where('body', 'Called twice, no answer')->exists())->toBeTrue();

    $this->actingAs($admin, 'admin')->get(route('admin.orders.show', $order))->assertSee('Called twice, no answer');

    // The customer-facing confirmation page never queries order_notes at all.
    $customerView = $this->get(URL::temporarySignedRoute('checkout.confirmation', now()->addHour(), ['order' => $order->id]));
    $customerView->assertOk()->assertDontSee('Called twice, no answer');
});

it('updates courier name and tracking reference', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')
        ->put(route('admin.orders.courier.update', $order), ['courier_name' => 'Pathao', 'tracking_reference' => 'PTH-123'])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->courier_name)->toBe('Pathao')
        ->and($order->fresh()->tracking_reference)->toBe('PTH-123');
});

it('allows editing the address while Pending but not after Processing (TC-ORD-008)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')->put(route('admin.orders.address.update', $order), [
        'customer_name' => 'Corrected Name',
        'phone' => '01712345678',
        'address_line' => 'Corrected address',
    ])->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->address_line)->toBe('Corrected address');

    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Confirmed->value]);
    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Processing->value]);

    $this->actingAs($admin, 'admin')->put(route('admin.orders.address.update', $order), [
        'customer_name' => 'Too Late',
        'phone' => '01712345678',
        'address_line' => 'Should not apply',
    ])->assertStatus(422);

    expect($order->fresh()->address_line)->toBe('Corrected address');
});

it('prints a packing slip (TC-ORD-009)', function () {
    $order = placeTestOrder();
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.packing-slip', $order))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee($order->customer_name);
});

it('requires orders.view to see the order list or detail', function () {
    $order = placeTestOrder();
    $contentEditor = Admin::factory()->create();
    $contentEditor->assignRole('Content Editor');

    $this->actingAs($contentEditor, 'admin')->get(route('admin.orders.index'))->assertForbidden();
    $this->actingAs($contentEditor, 'admin')->get(route('admin.orders.show', $order))->assertForbidden();
});
