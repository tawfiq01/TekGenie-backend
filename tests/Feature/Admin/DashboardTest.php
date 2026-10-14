<?php

use App\Enums\OrderStatus;
use App\Models\Admin;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('shows an empty state when there are no orders in the period (TC-RPT-001)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk()->assertSee('No orders in this period');
});

it('computes dashboard figures from real data, excluding cancelled orders (TC-RPT-001)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Store Manager');

    $kept = placeTestOrder(['price' => 1000], 1);
    $cancelled = placeTestOrder(['price' => 2000], 1);
    $this->actingAs($admin, 'admin')->post(route('admin.orders.cancel', $cancelled), ['reason' => 'Out of stock']);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('1') // order count
        ->assertSee(number_format((float) $kept->grand_total, 2, '.', ''));

    expect($cancelled->fresh()->status)->toBe(OrderStatus::Cancelled);
});
