<?php

namespace App\Enums;

enum ReturnCondition: string
{
    case Resaleable = 'resaleable';
    case Damaged = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::Resaleable => 'Resaleable',
            self::Damaged => 'Damaged',
        };
    }
}
