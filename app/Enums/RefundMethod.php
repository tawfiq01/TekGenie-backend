<?php

namespace App\Enums;

/** How a refund was physically paid back to the customer (UC-25) — distinct from PaymentMethod, which is how the order was collected. */
enum RefundMethod: string
{
    case Cash = 'cash';
    case BkashPersonal = 'bkash_personal';
    case BankTransfer = 'bank_transfer';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BkashPersonal => 'bKash (personal)',
            self::BankTransfer => 'Bank transfer',
            self::Other => 'Other',
        };
    }
}
