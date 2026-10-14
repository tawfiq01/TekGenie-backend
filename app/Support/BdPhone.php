<?php

namespace App\Support;

/**
 * Bangladesh mobile number validation and normalisation (SRS §6.2, VR-02).
 *
 * Accepts 01XXXXXXXXX, +8801XXXXXXXXX or 8801XXXXXXXXX, with optional
 * separators, and always normalises to the 11-digit 01XXXXXXXXX form.
 */
final class BdPhone
{
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $raw) ?? '';

        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            $digits = '0'.substr($digits, 3);
        }

        if (strlen($digits) !== 11) {
            return null;
        }

        return preg_match('/^01[3-9]\d{8}$/', $digits) === 1 ? $digits : null;
    }

    public static function isValid(?string $raw): bool
    {
        return self::normalize($raw) !== null;
    }
}
