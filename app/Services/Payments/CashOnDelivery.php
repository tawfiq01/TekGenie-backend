<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;

/** FR-PAY-002/003: the only payment method in the MVP. */
class CashOnDelivery implements PaymentMethodDriver
{
    public function method(): PaymentMethod
    {
        return PaymentMethod::Cod;
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('payments', 'cod_enabled', true);
    }

    public function displayName(): string
    {
        return (string) Setting::get('payments', 'cod_display_name', $this->method()->label());
    }

    public function instructions(): ?string
    {
        return Setting::get('payments', 'cod_instructions') ?: null;
    }

    public function maxOrderValue(): ?float
    {
        $max = (float) Setting::get('checkout', 'cod_max_order_value', 0);

        return $max > 0 ? $max : null;
    }

    public function supportsRefund(): bool
    {
        return true;
    }

    public function initiate(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method' => $this->method(),
            'status' => PaymentStatus::Unpaid,
            'amount' => $order->grand_total,
        ]);
    }
}
