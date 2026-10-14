<?php

use App\Models\User;

it('lists the customer\'s own orders, newest first (TC-ACC-009)', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $orderA = placeTestOrder();
    $orderA->update(['user_id' => $user->id, 'placed_at' => now()->subDays(2)]);
    $orderB = placeTestOrder();
    $orderB->update(['user_id' => $user->id, 'placed_at' => now()->subDay()]);
    $othersOrder = placeTestOrder();
    $othersOrder->update(['user_id' => $other->id]);

    $response = $this->actingAs($user)->get(route('account.orders.index'));

    $response->assertOk()->assertSee($orderA->order_number)->assertSee($orderB->order_number)->assertDontSee($othersOrder->order_number);

    $content = $response->getContent();
    expect(strpos($content, $orderB->order_number))->toBeLessThan(strpos($content, $orderA->order_number));
});
