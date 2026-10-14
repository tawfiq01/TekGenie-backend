<?php

namespace App\Services\Reviews;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Admin;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** FR-REV-001..004; BR-16: only a Delivered, not-yet-reviewed order line makes a customer eligible. */
class ReviewService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @return Collection<int, OrderItem> */
    public function reviewableOrderItems(User $user, Product $product): Collection
    {
        return OrderItem::query()
            ->where('product_id', $product->id)
            ->whereDoesntHave('review')
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', OrderStatus::Delivered))
            ->get();
    }

    public function canReview(User $user, Product $product): bool
    {
        return $this->reviewableOrderItems($user, $product)->isNotEmpty();
    }

    /** @throws ValidationException */
    public function submit(User $user, Product $product, int $rating, ?string $title, ?string $body): Review
    {
        $orderItem = $this->reviewableOrderItems($user, $product)->first();

        if ($orderItem === null) {
            throw ValidationException::withMessages([
                'rating' => 'You can only review products from your delivered orders.',
            ]);
        }

        return Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_item_id' => $orderItem->id,
            'rating' => $rating,
            'title' => $title,
            'body' => $body,
            'status' => ReviewStatus::Pending,
        ]);
    }

    public function moderate(Admin $admin, Review $review, ReviewStatus $decision, ?string $note = null): Review
    {
        return DB::transaction(function () use ($admin, $review, $decision, $note): Review {
            $review->forceFill([
                'status' => $decision,
                'moderated_by' => $admin->id,
                'moderated_at' => now(),
            ])->save();

            $this->recalculateRating($review->product_id);

            $this->audit->log('admin.review.moderated', $review, ['status' => $decision->value, 'note' => $note]);

            return $review;
        });
    }

    public function reply(Admin $admin, Review $review, string $reply): Review
    {
        $review->forceFill(['reply' => $reply, 'replied_at' => now()])->save();

        $this->audit->log('admin.review.replied', $review, ['reply' => $reply]);

        return $review;
    }

    private function recalculateRating(int $productId): void
    {
        $approved = fn () => Review::where('product_id', $productId)->where('status', ReviewStatus::Approved);

        Product::whereKey($productId)->update([
            'rating_avg' => $approved()->avg('rating'),
            'rating_count' => $approved()->count(),
        ]);
    }
}
