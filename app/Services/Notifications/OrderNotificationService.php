<?php

namespace App\Services\Notifications;

use App\Enums\OrderStatus;
use App\Mail\NewOrderNotification;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

/**
 * FR-NTF-001/002/003/005: every email is queued, never sent synchronously,
 * and callers only reach these methods after the triggering DB transaction
 * has already committed — a failed send can never roll back an order.
 */
class OrderNotificationService
{
    /** FR-NTF-003: the per-status setting key each notified status is individually switchable through. */
    private const STATUS_SETTING_KEYS = [
        'confirmed' => 'notify_confirmed',
        'shipped' => 'notify_shipped',
        'delivered' => 'notify_delivered',
        'cancelled' => 'notify_cancelled',
    ];

    public function orderPlaced(Order $order): void
    {
        if ($order->email) {
            Mail::to($order->email)->queue(new OrderPlaced($order));
        }

        $staffEmail = Setting::get('notifications', 'staff_new_order_email');
        if ($staffEmail) {
            Mail::to($staffEmail)->queue(new NewOrderNotification($order));
        }
    }

    public function statusChanged(Order $order, OrderStatus $status): void
    {
        if (! $this->statusEmailEnabled($status)) {
            return;
        }

        if ($order->email) {
            Mail::to($order->email)->queue(new OrderStatusChanged($order, $status));
        }
    }

    /**
     * A master switch gates every status email at once; the per-status
     * switch then lets staff turn individual statuses off without
     * affecting the others (FR-NTF-003).
     */
    private function statusEmailEnabled(OrderStatus $status): bool
    {
        if (! (bool) Setting::get('notifications', 'status_change_emails_enabled', true)) {
            return false;
        }

        $key = self::STATUS_SETTING_KEYS[$status->value] ?? null;

        return $key !== null && (bool) Setting::get('notifications', $key, true);
    }
}
