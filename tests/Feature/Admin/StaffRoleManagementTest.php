<?php

use App\Models\Admin;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('only lets staff with staff.manage view or change staff/roles (TC-ADM-002)', function () {
    $storeManager = Admin::factory()->create();
    $storeManager->assignRole('Store Manager');

    $this->actingAs($storeManager, 'admin')
        ->get(route('admin.staff.index'))
        ->assertForbidden();

    $other = Admin::factory()->create();
    $other->assignRole('Support');

    $this->actingAs($storeManager, 'admin')
        ->put(route('admin.staff.update', $other), [
            'name' => $other->name,
            'email' => $other->email,
            'roles' => ['Order Manager'],
        ])
        ->assertForbidden();
});

it('protects the last active Super Admin from self-deactivation and self role edits (TC-ADM-003)', function () {
    $superAdmin = Admin::factory()->create();
    $superAdmin->assignRole('Super Admin');

    $this->actingAs($superAdmin, 'admin')
        ->put(route('admin.staff.update', $superAdmin), [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'roles' => ['Super Admin'],
            'is_active' => '0',
        ])
        ->assertSessionHasErrors('is_active');

    $this->actingAs($superAdmin, 'admin')
        ->put(route('admin.staff.update', $superAdmin), [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'roles' => ['Order Manager'],
        ])
        ->assertSessionHasErrors('roles');

    expect($superAdmin->refresh()->is_active)->toBeTrue()
        ->and($superAdmin->hasRole('Super Admin'))->toBeTrue();
});

it('logs a deactivated staff member out and refuses their next login (TC-ADM-007)', function () {
    $superAdmin = Admin::factory()->create();
    $superAdmin->assignRole('Super Admin');

    $staff = Admin::factory()->create(['password' => Hash::make('Password123!')]);
    $staff->assignRole('Support');

    $this->actingAs($superAdmin, 'admin')
        ->put(route('admin.staff.update', $staff), [
            'name' => $staff->name,
            'email' => $staff->email,
            'roles' => ['Support'],
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.staff.index'));

    expect($staff->refresh()->is_active)->toBeFalse();

    auth('admin')->logout();

    $this->post('/admin/login', [
        'email' => $staff->email,
        'password' => 'Password123!',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('admin');
});
