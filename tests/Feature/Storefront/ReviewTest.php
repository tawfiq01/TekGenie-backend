<?php

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\User;

it('refuses a review from a customer without a delivered order for the product (TC-REV-001, AC-REV-01)', function () {
    $user = User::factory()->create();
    $product = makeVisibleProduct();

    $this->actingAs($user)
        ->post(route('reviews.store', $product), ['rating' => 5])
        ->assertForbidden();

    expect(Review::count())->toBe(0);
});

it('lets a customer review a product from a delivered order line (FR-REV-001)', function () {
    $order = placeTestOrder();
    $user = User::factory()->create();
    $order->update(['user_id' => $user->id, 'status' => OrderStatus::Delivered]);
    $item = $order->items->first();
    $product = $item->product;

    $this->actingAs($user)
        ->post(route('reviews.store', $product), ['rating' => 4, 'title' => 'Good', 'body' => 'Works well.'])
        ->assertRedirect();

    $review = Review::where('order_item_id', $item->id)->firstOrFail();
    expect($review->status)->toBe(ReviewStatus::Pending)
        ->and($review->rating)->toBe(4)
        ->and($review->isVerifiedPurchase())->toBeTrue();
});

it('refuses a second review for the same already-reviewed order line (TC-REV-002)', function () {
    $order = placeTestOrder();
    $user = User::factory()->create();
    $order->update(['user_id' => $user->id, 'status' => OrderStatus::Delivered]);
    $product = $order->items->first()->product;

    $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 5]);
    expect(Review::count())->toBe(1);

    $this->actingAs($user)
        ->post(route('reviews.store', $product), ['rating' => 3])
        ->assertForbidden();

    expect(Review::count())->toBe(1);
});

it('requires a verified email before submitting a review (FR-ACC-004)', function () {
    $order = placeTestOrder();
    $user = User::factory()->unverified()->create();
    $order->update(['user_id' => $user->id, 'status' => OrderStatus::Delivered]);
    $product = $order->items->first()->product;

    $this->actingAs($user)
        ->post(route('reviews.store', $product), ['rating' => 5])
        ->assertForbidden();

    expect(Review::count())->toBe(0);
});

it('hides a pending review from the public product page until approved (TC-REV-003)', function () {
    $order = placeTestOrder();
    $user = User::factory()->create();
    $order->update(['user_id' => $user->id, 'status' => OrderStatus::Delivered]);
    $product = $order->items->first()->product;

    $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 5, 'body' => 'Pending review body text']);

    $this->get(route('product.show', $product))->assertOk()->assertDontSee('Pending review body text');

    expect($product->fresh()->rating_count)->toBe(0);
});
