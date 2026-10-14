<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('keeps the admin guard separate from the customer guard (TC-ADM-001)', function () {
    $customer = User::factory()->create([
        'email' => 'customer@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->actingAs($customer)->get('/admin')->assertRedirect(route('admin.login'));

    $this->post('/admin/login', [
        'email' => 'customer@example.com',
        'password' => 'Password123!',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('admin');
});

it('logs a staff member in on the admin guard and regenerates the session', function () {
    $admin = Admin::factory()->create([
        'email' => 'staff@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->post('/admin/login', [
        'email' => 'staff@example.com',
        'password' => 'Password123!',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin, 'admin');
});

it('refuses login for a deactivated staff account', function () {
    Admin::factory()->inactive()->create([
        'email' => 'staff@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->post('/admin/login', [
        'email' => 'staff@example.com',
        'password' => 'Password123!',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('admin');
});
