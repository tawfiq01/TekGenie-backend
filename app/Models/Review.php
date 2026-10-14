<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_item_id', 'rating', 'title', 'body',
        'status', 'moderated_by', 'moderated_at', 'reply', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'rating' => 'integer',
            'moderated_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<OrderItem, $this> */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /** @return BelongsTo<Admin, $this> */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'moderated_by');
    }

    public function isVerifiedPurchase(): bool
    {
        return $this->order_item_id !== null;
    }
}
