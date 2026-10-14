<?php

use App\Models\Admin;
use App\Models\AuditLog;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('records staff login, failed login and staff-management actions, without secrets (TC-AUD-001)', function () {
    $superAdmin = Admin::factory()->create([
        'email' => 'super@example.com',
        'password' => Hash::make('Password123!'),
    ]);
    $superAdmin->assignRole('Super Admin');

    $this->post('/admin/login', [
        'email' => 'super@example.com',
        'password' => 'wrong-password',
    ]);

    expect(AuditLog::where('action', 'admin.login.failed')->exists())->toBeTrue();

    $this->post('/admin/login', [
        'email' => 'super@example.com',
        'password' => 'Password123!',
    ]);

    $loginEntry = AuditLog::where('action', 'admin.login')->first();
    expect($loginEntry)->not->toBeNull()
        ->and($loginEntry->actor_id)->toBe($superAdmin->id)
        ->and($loginEntry->ip_address)->not->toBeNull();

    $staff = Admin::factory()->create();

    $this->actingAs($superAdmin, 'admin')->put(route('admin.staff.update', $staff), [
        'name' => $staff->name,
        'email' => $staff->email,
        'roles' => ['Support'],
    ]);

    $updateEntry = AuditLog::where('action', 'admin.staff.updated')->first();
    expect($updateEntry)->not->toBeNull()
        ->and($updateEntry->changes)->toHaveKey('after')
        ->and(json_encode($updateEntry->changes))->not->toContain('password');
});

it('has no edit or delete route for the audit log and supports filtering (TC-AUD-002)', function () {
    $superAdmin = Admin::factory()->create();
    $superAdmin->assignRole('Super Admin');

    $entry = AuditLog::create([
        'actor_type' => 'admin',
        'actor_id' => $superAdmin->id,
        'action' => 'admin.role.created',
        'subject_type' => Role::class,
        'subject_id' => 1,
        'changes' => ['name' => 'Test Role'],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PestTest',
    ]);

    $this->actingAs($superAdmin, 'admin')->patch("/admin/audit-log/{$entry->id}")->assertStatus(404);
    $this->actingAs($superAdmin, 'admin')->delete("/admin/audit-log/{$entry->id}")->assertStatus(404);

    $this->actingAs($superAdmin, 'admin')
        ->get(route('admin.audit-log.index', ['action' => 'admin.role.created']))
        ->assertOk()
        ->assertSee('admin.role.created');

    $this->actingAs($superAdmin, 'admin')
        ->get(route('admin.audit-log.index', ['action' => 'no-such-action']))
        ->assertOk()
        ->assertDontSee('admin.role.created');
});
