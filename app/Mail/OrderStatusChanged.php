<?php

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** FR-NTF-003: status-change email for Confirmed/Shipped/Delivered/Cancelled. */
class OrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly OrderStatus $status,
    ) {}

    public function build(): self
    {
        return $this->subject("Order {$this->order->order_number} is now {$this->status->label()}")
            ->view('emails.order-status-changed');
    }
}
