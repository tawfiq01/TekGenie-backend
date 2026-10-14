<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\ReturnCondition;
use App\Enums\ReturnType;
use App\Enums\StockMovementType;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FR-ORD-009; BR-07/BR-08. A delivery-failed return is always full and
 * moves the order to Returned; a customer return can be partial and leaves
 * the order Delivered. Resaleable lines restock; damaged lines are written
 * off (logged, stock unaffected).
 */
class ReturnService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly OrderStatusService $statuses,
    ) {}

    /** @param  array<int, array{order_item_id: int, quantity: int, condition: ReturnCondition}>  $lines */
    public function process(Order $order, ReturnType $type, string $reason, array $lines, Admin $actor): OrderReturn
    {
        if ($order->status !== $type->requiredOrderStatus()) {
            throw ValidationException::withMessages([
                'type' => "A {$type->label()} return requires the order to be {$type->requiredOrderStatus()->label()}.",
            ]);
        }

        if (empty($lines)) {
            throw ValidationException::withMessages(['lines' => 'Select at least one item to return.']);
        }

        $orderReturn = DB::transaction(function () use ($order, $type, $reason, $lines, $actor): OrderReturn {
            $orderReturn = OrderReturn::create([
                'order_id' => $order->id,
                'type' => $type,
                'reason' => $reason,
                'processed_by' => $actor->id,
                'processed_at' => now(),
            ]);

            foreach ($lines as $line) {
                $item = $order->items()->whereKey($line['order_item_id'])->firstOrFail();
                $remaining = $item->quantity - $item->returned_quantity;

                if ($line['quantity'] < 1 || $line['quantity'] > $remaining) {
                    throw ValidationException::withMessages([
                        'lines' => "Only {$remaining} of {$item->product_name} can still be returned.",
                    ]);
                }

                OrderReturnItem::create([
                    'order_return_id' => $orderReturn->id,
                    'order_item_id' => $item->id,
                    'quantity' => $line['quantity'],
                    'condition' => $line['condition'],
                ]);

                $item->increment('returned_quantity', $line['quantity']);

                $variant = $item->product_variant_id ? $item->variant()->withTrashed()->first() : null;

                if ($variant !== null) {
                    $resaleable = $line['condition'] === ReturnCondition::Resaleable;

                    $this->inventory->recordMovement(
                        $variant,
                        $resaleable ? StockMovementType::ReturnRestock : StockMovementType::ReturnWriteoff,
                        $resaleable ? $line['quantity'] : -$line['quantity'],
                        "Return #{$orderReturn->id}: {$line['condition']->label()}",
                        $actor,
                        $order->id,
                        $orderReturn->id,
                    );
                }
            }

            return $orderReturn;
        });

        if ($type === ReturnType::DeliveryFailed) {
            $this->statuses->transition($order->fresh(), OrderStatus::Returned, $actor, 'Delivery refused/failed — returned.');
        }

        return $orderReturn;
    }
}
