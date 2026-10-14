<?php

use App\Enums\OrderStatus;
use App\Mail\OrderStatusChanged;
use App\Models\Admin;
use App\Models\Setting;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('Store Manager');
});

it('saves store settings and shows the new value immediately (TC-SET-001)', function () {
    $payload = [
        'name' => 'Dhaka Gadgets',
        'contact_phone' => '01712345678',
        'contact_email' => 'hello@example.test',
        'contact_address' => 'House 1, Road 2, Dhaka',
        'business_hours' => '9am - 9pm',
        'order_number_prefix' => 'DG',
        'currency_symbol' => '৳',
        'low_stock_default_threshold' => '8',
        'announcement_text' => 'Eid sale is live',
        'announcement_link' => 'https://example.test/sale',
        'facebook_url' => 'https://facebook.com/example',
        'instagram_url' => 'https://instagram.com/example',
    ];

    $this->actingAs($this->admin, 'admin')
        ->put(route('admin.settings.store.update'), $payload)
        ->assertRedirect(route('admin.settings.store'));

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.settings.store'))
        ->assertOk()
        ->assertSee('Dhaka Gadgets')
        ->assertSee('Eid sale is live');

    expect(Setting::get('store', 'name'))->toBe('Dhaka Gadgets');
    expect(Setting::get('store', 'low_stock_default_threshold'))->toBe('8');
});

it('saves payment settings including the cross-group COD max order value (FR-PAY-002)', function () {
    $payload = [
        'cod_enabled' => '1',
        'cod_display_name' => 'Cash on Delivery',
        'cod_instructions' => 'Pay the courier in cash on arrival.',
        'cod_max_order_value' => '15000',
    ];

    $this->actingAs($this->admin, 'admin')
        ->put(route('admin.settings.payments.update'), $payload)
        ->assertRedirect(route('admin.settings.payments'));

    expect(Setting::get('payments', 'cod_display_name'))->toBe('Cash on Delivery');
    expect(Setting::get('checkout', 'cod_max_order_value'))->toBe('15000');

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.settings.payments'))
        ->assertOk()
        ->assertSee('15000');
});

it('turns COD off and persists the unchecked state (FR-PAY-002)', function () {
    $this->actingAs($this->admin, 'admin')->put(route('admin.settings.payments.update'), [
        'cod_display_name' => 'Cash on Delivery',
        'cod_max_order_value' => '0',
    ]);

    expect(Setting::get('payments', 'cod_enabled'))->toBe('0');
});

it('saves notification settings and applies per-status toggles independently (TC-NTF-003)', function () {
    $this->actingAs($this->admin, 'admin')->put(route('admin.settings.notifications.update'), [
        'staff_new_order_email' => 'staff@example.test',
        'status_change_emails_enabled' => '1',
        'notify_shipped' => '1',
        // notify_confirmed/delivered/cancelled omitted = unchecked.
    ])->assertRedirect(route('admin.settings.notifications'));

    expect(Setting::get('notifications', 'notify_shipped'))->toBe('1');
    expect(Setting::get('notifications', 'notify_confirmed'))->toBe('0');
    expect(Setting::get('notifications', 'notify_delivered'))->toBe('0');
    expect(Setting::get('notifications', 'notify_cancelled'))->toBe('0');

    Mail::fake();
    $order = placeTestOrder([], 1, ['email' => 'customer@example.test']);

    $this->actingAs($this->admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Confirmed->value]);
    Mail::assertNotQueued(OrderStatusChanged::class, fn ($mail) => $mail->status === OrderStatus::Confirmed);

    $this->actingAs($this->admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Processing->value]);
    $this->actingAs($this->admin, 'admin')->post(route('admin.orders.status.update', $order), ['status' => OrderStatus::Shipped->value]);
    Mail::assertQueued(OrderStatusChanged::class, fn ($mail) => $mail->status === OrderStatus::Shipped);
});

it('saves SEO settings (FR-SET-002, global defaults only)', function () {
    $this->actingAs($this->admin, 'admin')->put(route('admin.settings.seo.update'), [
        'default_meta_title_pattern' => '{title} — Dhaka Gadgets',
        'default_meta_description' => 'Buy gadgets online in Bangladesh.',
    ])->assertRedirect(route('admin.settings.seo'));

    expect(Setting::get('seo', 'default_meta_title_pattern'))->toBe('{title} — Dhaka Gadgets');

    $this->actingAs($this->admin, 'admin')
        ->get(route('admin.settings.seo'))
        ->assertOk()
        ->assertSee('Buy gadgets online in Bangladesh.');
});

it('allows a settings.seo-only role to reach SEO settings but not store/payments/notifications', function () {
    $contentEditor = Admin::factory()->create();
    $contentEditor->assignRole('Content Editor');

    $this->actingAs($contentEditor, 'admin')->get(route('admin.settings.seo'))->assertOk();
    $this->actingAs($contentEditor, 'admin')->put(route('admin.settings.seo.update'), [
        'default_meta_title_pattern' => '{title}',
    ])->assertRedirect(route('admin.settings.seo'));

    $this->actingAs($contentEditor, 'admin')->get(route('admin.settings.store'))->assertForbidden();
    $this->actingAs($contentEditor, 'admin')->get(route('admin.settings.payments'))->assertForbidden();
    $this->actingAs($contentEditor, 'admin')->get(route('admin.settings.notifications'))->assertForbidden();
});
