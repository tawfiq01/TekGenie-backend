<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\Checkout\CouponService;
use App\Services\Inventory\InventoryService;
use App\Services\Notifications\OrderNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FR-ORD-002/008/012; BR-06/BR-09/BR-10/BR-11. The only place an order's
 * status ever changes, so every transition gets a history row and the right
 * side effects — stock restore and coupon release on cancellation
 * (exactly once, guarded by orders.stock_restored_at).
 */
class OrderStatusService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly CouponService $coupons,
        private readonly OrderNotificationService $notifications,
    ) {}

    /** Staff-initiated transition (FR-ORD-002). */
    public function transition(Order $order, OrderStatus $to, Admin $actor, ?string $note = null): Order
    {
        return $this->applyTransition($order, $to, 'admin', $actor->id, $note, true, $actor);
    }

    /**
     * Customer-initiated cancellation (FR-ORD-008, BR-09): restricted to
     * Pending regardless of what the state machine itself allows — once
     * staff have touched the order, only staff can cancel it.
     */
    public function cancelByCustomer(Order $order, ?int $userId, ?string $reason): Order
    {
        if ($order->status !== OrderStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'This order can no longer be cancelled — please contact us.',
            ]);
        }

        return $this->applyTransition($order, OrderStatus::Cancelled, 'customer', $userId, $reason, true, null);
    }

    /**
     * FR-ORD-012 (S): bulk status update, skipping orders the transition
     * isn't valid for instead of failing the whole batch.
     *
     * @param  array<int, int>  $orderIds
     * @return array{updated: int, skipped: int}
     */
    public function bulkTransition(array $orderIds, OrderStatus $to, Admin $actor): array
    {
        $updated = 0;
        $skipped = 0;

        foreach (Order::query()->whereIn('id', $orderIds)->get() as $order) {
            if (! $order->status->canTransitionTo($to)) {
                $skipped++;

                continue;
            }

            $this->transition($order, $to, $actor);
            $updated++;
        }

        return ['updated' => $updated, 'skipped' => $skipped];
    }

    private function applyTransition(
        Order $order,
        OrderStatus $to,
        string $actorType,
        ?int $actorId,
        ?string $note,
        bool $customerVisible,
        ?Admin $actor,
    ): Order {
        $order = DB::transaction(function () use ($order, $to, $actorType, $actorId, $note, $customerVisible, $actor): Order {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $locked->status->canTransitionTo($to)) {
                throw ValidationException::withMessages([
                    'status' => "Cannot move this order from {$locked->status->label()} to {$to->label()}.",
                ]);
            }

            $from = $locked->status;

            $attributes = ['status' => $to];
            if (($column = $this->timestampColumnFor($to)) !== null) {
                $attributes[$column] = now();
            }
            if ($to === OrderStatus::Cancelled && $note !== null) {
                $attributes['cancel_reason'] = $note;
            }
            $locked->forceFill($attributes)->save();

            OrderStatusHistory::create([
                'order_id' => $locked->id,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'note' => $note,
                'is_customer_visible' => $customerVisible,
            ]);

            if ($to === OrderStatus::Cancelled) {
                $this->restoreStockAndReleaseCoupon($locked, $actor);
            }

            return $locked->fresh();
        });

        $this->notifications->statusChanged($order, $to);

        return $order;
    }

    private function timestampColumnFor(OrderStatus $status): ?string
    {
        return match ($status) {
            OrderStatus::Confirmed => 'confirmed_at',
            OrderStatus::Shipped => 'shipped_at',
            OrderStatus::Delivered => 'delivered_at',
            OrderStatus::Cancelled => 'cancelled_at',
            OrderStatus::Returned => 'returned_at',
            default => null,
        };
    }

    /** BR-06: cancelling before shipping restores stock and releases the coupon slot, exactly once. */
    private function restoreStockAndReleaseCoupon(Order $order, ?Admin $actor): void
    {
        if ($order->stock_restored_at === null) {
            foreach ($order->items()->get() as $item) {
                $variant = $item->product_variant_id
                    ? $item->variant()->withTrashed()->first()
                    : null;

                if ($variant === null) {
                    continue;
                }

                $this->inventory->recordMovement(
                    $variant,
                    StockMovementType::Cancellation,
                    $item->quantity,
                    "Order {$order->order_number} cancelled",
                    $actor,
                    $order->id,
                );
            }

            $order->forceFill(['stock_restored_at' => now()])->save();
        }

        if ($order->coupon_id !== null) {
            $this->coupons->release($order);

            if ($order->coupon_released_at === null) {
                $order->forceFill(['coupon_released_at' => now()])->save();
            }
        }
    }
}
