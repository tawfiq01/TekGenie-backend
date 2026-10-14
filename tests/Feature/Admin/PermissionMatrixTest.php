<?php

use App\Models\Admin;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

/**
 * TC-ADM-004, scoped to the admin routes built so far (staff & roles, audit
 * log). Grows as later phases add more admin routes.
 */
it('enforces staff.manage and audit.view per role on every protected admin route', function (string $role, bool $canManageStaff, bool $canViewAudit) {
    $admin = Admin::factory()->create();
    $admin->assignRole($role);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.index'));
    $canManageStaff ? $response->assertOk() : $response->assertForbidden();

    $response = $this->actingAs($admin, 'admin')->get(route('admin.roles.index'));
    $canManageStaff ? $response->assertOk() : $response->assertForbidden();

    $response = $this->actingAs($admin, 'admin')->get(route('admin.audit-log.index'));
    $canViewAudit ? $response->assertOk() : $response->assertForbidden();

    $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))->assertOk();
})->with([
    'Super Admin' => ['Super Admin', true, true],
    'Store Manager' => ['Store Manager', false, true],
    'Order Manager' => ['Order Manager', false, false],
    'Catalog Manager' => ['Catalog Manager', false, false],
    'Content Editor' => ['Content Editor', false, false],
    'Support' => ['Support', false, false],
]);
