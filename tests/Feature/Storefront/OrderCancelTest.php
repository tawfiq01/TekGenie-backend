<?php

use App\Enums\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\URL;

it('lets a logged-in customer cancel their own Pending order (TC-ORD-010)', function () {
    $user = User::factory()->create();
    $order = placeTestOrder([], 2, []);
    $order->update(['user_id' => $user->id]);
    $variant = $order->items->first()->variant;
    $stockBefore = $variant->fresh()->stock;

    $this->actingAs($user)
        ->post(route('orders.cancel', $order), ['reason' => 'Changed my mind'])
        ->assertRedirect(route('checkout.confirmation', $order));

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Cancelled)
        ->and($variant->fresh()->stock)->toBe($stockBefore + 2);

    $history = OrderStatusHistory::where('order_id', $order->id)->latest('id')->first();
    expect($history->actor_type)->toBe('customer')
        ->and($history->actor_id)->toBe($user->id);
});

it('refuses to let a customer cancel once the order is no longer Pending (TC-ORD-011)', function () {
    $user = User::factory()->create();
    $order = placeTestOrder();
    $order->update(['user_id' => $user->id]);
    $order->update(['status' => OrderStatus::Confirmed]);

    $this->actingAs($user)
        ->post(route('orders.cancel', $order), ['reason' => 'Too late'])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

it('lets a guest cancel via a validly signed link', function () {
    $order = placeTestOrder();
    $signedCancelUrl = URL::temporarySignedRoute('orders.cancel', now()->addHours(24), ['order' => $order->id]);

    $this->post($signedCancelUrl, ['reason' => 'Guest cancel'])->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

it('never lets a customer cancel or view another customer\'s order (TC-ORD-018, BR-19)', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = placeTestOrder();
    $order->update(['user_id' => $owner->id]);

    $this->actingAs($other)->get(route('checkout.confirmation', $order))->assertNotFound();
    $this->actingAs($other)->post(route('orders.cancel', $order), ['reason' => 'x'])->assertNotFound();

    $this->post(route('orders.cancel', $order), ['reason' => 'x'])->assertNotFound();

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

it('shows the customer only customer-visible status history entries (FR-ORD-011)', function () {
    $order = placeTestOrder();
    OrderStatusHistory::create([
        'order_id' => $order->id, 'from_status' => 'pending', 'to_status' => 'confirmed',
        'actor_type' => 'admin', 'is_customer_visible' => true,
    ]);
    OrderStatusHistory::create([
        'order_id' => $order->id, 'from_status' => 'confirmed', 'to_status' => 'processing',
        'actor_type' => 'admin', 'is_customer_visible' => false,
    ]);

    $signed = URL::temporarySignedRoute('checkout.confirmation', now()->addHours(24), ['order' => $order->id]);
    $this->get($signed)->assertOk()->assertSee('Confirmed')->assertDontSee('Processing');
});
