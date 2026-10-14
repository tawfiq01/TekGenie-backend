<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;

/**
 * FR-PAY-001: a match with no default arm, so adding a PaymentMethod case
 * without also registering its driver here is a static analysis error
 * (non-exhaustive match), not a silent runtime failure.
 */
class PaymentManager
{
    public function __construct(private readonly CashOnDelivery $cod) {}

    public function driver(PaymentMethod $method): PaymentMethodDriver
    {
        return match ($method) {
            PaymentMethod::Cod => $this->cod,
        };
    }

    /** @return array<int, PaymentMethodDriver> */
    public function enabled(): array
    {
        return array_filter(
            [$this->cod],
            fn (PaymentMethodDriver $driver) => $driver->isEnabled(),
        );
    }
}
