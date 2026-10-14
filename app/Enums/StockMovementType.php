<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Opening = 'opening';
    case Restock = 'restock';
    case Sale = 'sale';
    case Cancellation = 'cancellation';
    case ReturnRestock = 'return_restock';
    case ReturnWriteoff = 'return_writeoff';
    case Adjustment = 'adjustment';
    case Correction = 'correction';

    public function affectsStock(): bool
    {
        return $this !== self::ReturnWriteoff;
    }
}
