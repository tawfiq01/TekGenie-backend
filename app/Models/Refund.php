<?php

namespace App\Models;

use App\Enums\RefundMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property RefundMethod $method */
class Refund extends Model
{
    protected $fillable = [
        'payment_id', 'order_id', 'amount', 'method', 'reference', 'note', 'refunded_at', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'method' => RefundMethod::class,
            'amount' => 'decimal:2',
            'refunded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Payment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Admin, $this> */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'recorded_by');
    }
}
