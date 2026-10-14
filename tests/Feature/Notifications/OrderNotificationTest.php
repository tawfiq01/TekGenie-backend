<?php

use App\Enums\OrderStatus;
use App\Mail\NewOrderNotification;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\Admin;
use App\Models\Setting;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Mail;

it('queues a customer confirmation and a staff new-order email after the order commits (TC-NTF-001)', function () {
    Setting::updateOrCreate(['group' => 'notifications', 'key' => 'staff_new_order_email'], ['value' => 'staff@example.test']);
    Mail::fake();

    $order = placeTestOrder([], 1, ['email' => 'customer@example.test']);

    Mail::assertQueued(OrderPlaced::class, fn ($mail) => $mail->order->is($order) && $mail->hasTo('customer@example.test'));
    Mail::assertQueued(NewOrderNotification::class, fn ($mail) => $mail->hasTo('staff@example.test'));
});

it('sends no customer email when the guest gave none (TC-NTF-002)', function () {
    Mail::fake();

    placeTestOrder([], 1, ['email' => null]);

    Mail::assertNotQueued(OrderPlaced::class);
});

it('sends a status-change email only for the notified statuses, when the setting is enabled (TC-NTF-003)', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $admin = Admin::factory()->create();
    $admin->assignRole('Order Manager');

    Mail::fake();
    $order = placeTestOrder([], 1, ['email' => 'customer@example.test']);

    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Confirmed->value]);
    Mail::assertQueued(OrderStatusChanged::class, fn ($mail) => $mail->status === OrderStatus::Confirmed);

    // Processing isn't one of the notified statuses at all (Confirmed/Shipped/Delivered/Cancelled only).
    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Processing->value]);
    Mail::assertNotQueued(OrderStatusChanged::class, fn ($mail) => $mail->status === OrderStatus::Processing);

    Setting::updateOrCreate(['group' => 'notifications', 'key' => 'status_change_emails_enabled'], ['value' => '0']);

    // Shipped IS normally notified, but the blanket toggle is now off.
    $this->actingAs($admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Shipped->value]);
    Mail::assertNotQueued(OrderStatusChanged::class, fn ($mail) => $mail->status === OrderStatus::Shipped);
});
