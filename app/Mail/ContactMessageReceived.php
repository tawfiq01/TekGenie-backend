<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** FR-STF-008: queued so a slow mail provider never blocks the request. */
class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ContactMessage $contactMessage) {}

    public function build(): self
    {
        return $this->subject('New contact message: '.$this->contactMessage->subject)
            ->view('emails.contact-message');
    }
}
