<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** FR-NTF-001: order confirmation sent to the customer, if an email was given. */
class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function build(): self
    {
        return $this->subject("Order confirmed — {$this->order->order_number}")
            ->view('emails.order-placed');
    }
}
