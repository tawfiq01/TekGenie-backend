<?php

namespace App\Services\Inventory;

use App\Enums\StockMovementType;
use App\Models\Admin;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\Audit\AuditLogger;
use App\Services\Catalog\VariantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The stock ledger (FR-INV-001/002). This is the single place stock is ever
 * changed — manual adjustments now (FR-INV-005); order placement and
 * cancellation (FR-INV-003/004) call the same recordMovement() once the
 * Checkout/Orders modules land, so every change always gets a movement row.
 */
class InventoryService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly VariantService $variants,
    ) {}

    /**
     * Manual stock adjustment (FR-INV-005). $delta is signed: positive to
     * increase, negative to decrease. Decreases below zero are refused.
     */
    public function adjust(ProductVariant $variant, int $delta, string $reason, Admin $actor): StockMovement
    {
        if ($delta === 0) {
            throw ValidationException::withMessages(['quantity' => 'Enter a non-zero quantity.']);
        }

        $movement = $this->recordMovement($variant, StockMovementType::Adjustment, $delta, $reason, $actor);

        $this->audit->log('admin.inventory.adjusted', $variant, [
            'delta' => $delta,
            'reason' => $reason,
            'balance_after' => $movement->balance_after,
        ]);

        return $movement;
    }

    /**
     * Records a stock movement under a row lock (BR-04/BR-08). $delta is
     * signed; the resulting balance can never go below zero.
     */
    public function recordMovement(
        ProductVariant $variant,
        StockMovementType $type,
        int $delta,
        string $reason,
        Admin $actor,
        ?int $orderId = null,
        ?int $orderReturnId = null,
    ): StockMovement {
        return DB::transaction(function () use ($variant, $type, $delta, $reason, $actor, $orderId, $orderReturnId): StockMovement {
            $locked = ProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail();
            $newBalance = $locked->stock + $delta;

            if ($newBalance < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'This would take stock below zero — current stock is '.$locked->stock.'.',
                ]);
            }

            $locked->forceFill(['stock' => $newBalance])->save();

            $movement = StockMovement::create([
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => $delta,
                'affects_stock' => $type->affectsStock(),
                'balance_after' => $newBalance,
                'reason' => $reason,
                'order_id' => $orderId,
                'order_return_id' => $orderReturnId,
                'admin_id' => $actor->id,
            ]);

            $this->variants->recalcProductAggregates($locked->product);

            return $movement;
        });
    }
}
