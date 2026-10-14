<?php

it('shows a masked status for the correct order number and phone pair (TC-ACC-010, BR-15)', function () {
    $order = placeTestOrder([], 1, ['address_line' => 'House 12, Road 5, Dhanmondi']);

    $this->post(route('orders.track.lookup'), [
        'order_number' => $order->order_number,
        'phone' => $order->phone,
    ])->assertOk()->assertSee('House ••, Road ••, Dhanmondi')->assertSee($order->order_number);
});

it('refuses the right order number with the wrong phone, with a generic message (BR-15)', function () {
    $order = placeTestOrder();

    $this->post(route('orders.track.lookup'), [
        'order_number' => $order->order_number,
        'phone' => '01799999999',
    ])->assertSessionHasErrors('order_number');
});

it('throttles repeated tracking lookups', function () {
    $order = placeTestOrder();

    for ($i = 0; $i < 7; $i++) {
        $this->post(route('orders.track.lookup'), ['order_number' => 'bad', 'phone' => '01799999999']);
    }

    $this->post(route('orders.track.lookup'), ['order_number' => $order->order_number, 'phone' => $order->phone])
        ->assertStatus(429);
});
