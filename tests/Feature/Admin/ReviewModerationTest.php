<?php

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Admin;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

function submitPendingReview(int $rating = 5): Review
{
    $order = placeTestOrder();
    $user = User::factory()->create();
    $order->update(['user_id' => $user->id, 'status' => OrderStatus::Delivered]);
    $product = $order->items->first()->product;

    test()->actingAs($user)->post(route('reviews.store', $product), ['rating' => $rating, 'body' => 'Review body']);

    return Review::where('product_id', $product->id)->firstOrFail();
}

it('approves a pending review and recalculates the product rating (TC-REV-003)', function () {
    $review = submitPendingReview(4);
    $admin = Admin::factory()->create();
    $admin->assignRole('Content Editor');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.reviews.approve', $review))
        ->assertRedirect();

    $review->refresh();
    expect($review->status)->toBe(ReviewStatus::Approved)
        ->and($review->moderated_by)->toBe($admin->id);

    $product = $review->product->fresh();
    expect((float) $product->rating_avg)->toBe(4.0)
        ->and($product->rating_count)->toBe(1);
});

it('rejects a review, which never appears on the product page or counts toward the rating (TC-REV-003)', function () {
    $review = submitPendingReview(1);
    $admin = Admin::factory()->create();
    $admin->assignRole('Content Editor');

    $this->actingAs($admin, 'admin')->post(route('admin.reviews.reject', $review), ['note' => 'Spam']);

    $review->refresh();
    expect($review->status)->toBe(ReviewStatus::Rejected);

    $this->get(route('product.show', $review->product))->assertOk()->assertDontSee('Review body');
    expect($review->product->fresh()->rating_count)->toBe(0);
});

it('publishes a staff reply visible to everyone (TC-REV-004)', function () {
    $review = submitPendingReview();
    $admin = Admin::factory()->create();
    $admin->assignRole('Content Editor');

    $this->actingAs($admin, 'admin')->post(route('admin.reviews.approve', $review));
    $this->actingAs($admin, 'admin')->post(route('admin.reviews.reply', $review), ['reply' => 'Thanks for the feedback!']);

    expect($review->fresh()->reply)->toBe('Thanks for the feedback!');

    $this->get(route('product.show', $review->product))->assertOk()->assertSee('Thanks for the feedback!');
});

it('requires reviews.moderate permission', function () {
    $review = submitPendingReview();
    $viewer = Admin::factory()->create();
    $viewer->assignRole('Order Manager');

    $this->actingAs($viewer, 'admin')->get(route('admin.reviews.index'))->assertForbidden();
    $this->actingAs($viewer, 'admin')->post(route('admin.reviews.approve', $review))->assertForbidden();
});

it('renders the reviews index page with the status filter', function () {
    submitPendingReview();
    $admin = Admin::factory()->create();
    $admin->assignRole('Content Editor');

    $this->actingAs($admin, 'admin')->get(route('admin.reviews.index'))->assertOk();
    $this->actingAs($admin, 'admin')->get(route('admin.reviews.index', ['status' => 'approved']))->assertOk();
});
