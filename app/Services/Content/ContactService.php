<?php

namespace App\Services\Content;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

/** FR-STF-008. */
class ContactService
{
    /** @param  array<string, mixed>  $data */
    public function submit(array $data, string $ip): ContactMessage
    {
        $message = ContactMessage::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $ip,
        ]);

        $staffEmail = Setting::get('store', 'contact_email');

        if ($staffEmail) {
            Mail::to($staffEmail)->queue(new ContactMessageReceived($message));
        }

        return $message;
    }
}
