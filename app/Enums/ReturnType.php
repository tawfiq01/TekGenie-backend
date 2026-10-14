<?php

namespace App\Enums;

enum ReturnType: string
{
    case DeliveryFailed = 'delivery_failed';
    case CustomerReturn = 'customer_return';

    public function label(): string
    {
        return match ($this) {
            self::DeliveryFailed => 'Delivery failed / refused',
            self::CustomerReturn => 'Customer return',
        };
    }

    /** The order status this return type is valid from (FR-ORD-009). */
    public function requiredOrderStatus(): OrderStatus
    {
        return match ($this) {
            self::DeliveryFailed => OrderStatus::Shipped,
            self::CustomerReturn => OrderStatus::Delivered,
        };
    }
}
