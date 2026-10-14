<?php

namespace App\Services\Orders;

use App\Enums\PaymentStatus;
use App\Enums\RefundMethod;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** FR-PAY-003/FR-ORD-010; UC-22/UC-25. Records what staff did manually — no money is moved by the system. */
class OrderPaymentService
{
    public function recordCollection(Order $order, float $collectedAmount, ?string $varianceNote, Admin $actor): Payment
    {
        return DB::transaction(function () use ($order, $collectedAmount, $varianceNote, $actor): Payment {
            $payment = Payment::query()->where('order_id', $order->id)->lockForUpdate()->first();

            if ($payment === null) {
                throw ValidationException::withMessages(['collected_amount' => 'No payment record exists for this order.']);
            }

            if ($payment->status === PaymentStatus::Paid) {
                throw ValidationException::withMessages(['collected_amount' => 'Collection has already been recorded for this order.']);
            }

            $variesFromTotal = round($collectedAmount, 2) !== round((float) $payment->amount, 2);

            if ($variesFromTotal && ! $varianceNote) {
                throw ValidationException::withMessages(['variance_note' => 'Explain why the collected amount differs from the order total.']);
            }

            $payment->forceFill([
                'status' => PaymentStatus::Paid,
                'collected_amount' => $collectedAmount,
                'collected_at' => now(),
                'recorded_by' => $actor->id,
                'provider_payload' => $variesFromTotal ? ['variance_note' => $varianceNote] : null,
            ])->save();

            $order->forceFill(['payment_status' => PaymentStatus::Paid])->save();

            return $payment;
        });
    }

    public function recordRefund(Order $order, float $amount, RefundMethod $method, ?string $reference, ?string $note, Admin $actor): Refund
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Enter a refund amount greater than zero.']);
        }

        return DB::transaction(function () use ($order, $amount, $method, $reference, $note, $actor): Refund {
            $payment = Payment::query()->where('order_id', $order->id)->where('status', PaymentStatus::Paid)->lockForUpdate()->first();

            if ($payment === null) {
                throw ValidationException::withMessages(['amount' => 'No payment has been recorded for this order yet.']);
            }

            $alreadyRefunded = (float) Refund::query()->where('payment_id', $payment->id)->sum('amount');
            $available = round((float) $payment->amount - $alreadyRefunded, 2);

            if ($amount > $available) {
                throw ValidationException::withMessages(['amount' => "At most ৳{$available} can still be refunded for this order."]);
            }

            $refund = Refund::create([
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'note' => $note,
                'refunded_at' => now(),
                'recorded_by' => $actor->id,
            ]);

            $order->forceFill([
                'payment_status' => $amount >= $available ? PaymentStatus::Refunded : PaymentStatus::PartiallyRefunded,
            ])->save();

            return $refund;
        });
    }
}
