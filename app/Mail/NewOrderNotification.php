<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** FR-NTF-002: new-order alert sent to the configured staff address. */
class NewOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function build(): self
    {
        return $this->subject("New order — {$this->order->order_number}")
            ->view('emails.new-order');
    }
}
