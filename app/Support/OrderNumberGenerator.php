<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;

/**
 * FR-CHK-009: `[PREFIX]-YYMMDD-XXXXX`, non-sequential-looking, no ambiguous
 * characters (0/O, 1/I excluded from the random suffix).
 */
final class OrderNumberGenerator
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function generate(): string
    {
        $prefix = Setting::get('store', 'order_number_prefix', 'ST');
        $date = now()->format('ymd');

        do {
            $candidate = "{$prefix}-{$date}-".self::randomSuffix(5);
        } while (Order::query()->where('order_number', $candidate)->exists());

        return $candidate;
    }

    private static function randomSuffix(int $length): string
    {
        $lastIndex = strlen(self::ALPHABET) - 1;
        $suffix = '';

        for ($i = 0; $i < $length; $i++) {
            $suffix .= self::ALPHABET[random_int(0, $lastIndex)];
        }

        return $suffix;
    }
}
