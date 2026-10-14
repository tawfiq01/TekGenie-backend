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
    private const STATUS_CHANGE_NOTIFIED = [
        OrderStatus::Confirmed,
        OrderStatus::Shipped,
        OrderStatus::Delivered,
        OrderStatus::Cancelled,
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
        if (! in_array($status, self::STATUS_CHANGE_NOTIFIED, true)) {
            return;
        }

        if (! (bool) Setting::get('notifications', 'status_change_emails_enabled', true)) {
            return;
        }

        if ($order->email) {
            Mail::to($order->email)->queue(new OrderStatusChanged($order, $status));
        }
    }
}
