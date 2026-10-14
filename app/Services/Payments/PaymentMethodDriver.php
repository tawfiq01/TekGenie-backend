<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;

/**
 * FR-PAY-001: every payment method sits behind this common interface so a
 * future gateway can be added without touching CheckoutService. MVP ships
 * only CashOnDelivery.
 */
interface PaymentMethodDriver
{
    public function method(): PaymentMethod;

    public function isEnabled(): bool;

    public function displayName(): string;

    public function instructions(): ?string;

    /** Null means no cap. */
    public function maxOrderValue(): ?float;

    public function supportsRefund(): bool;

    /** Creates the order's Payment record at placement time (Unpaid for COD; a redirect/reference for a future gateway). */
    public function initiate(Order $order): Payment;
}
