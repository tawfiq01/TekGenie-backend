<?php

namespace App\Services\Admin;

use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role & permission management (FR-ADM-002).
 */
class RoleManagementService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param  array<int, string>  $permissionNames */
    public function create(string $name, array $permissionNames): Role
    {
        $role = DB::transaction(function () use ($name, $permissionNames): Role {
            $role = Role::create(['name' => $name, 'guard_name' => 'admin']);

            if (! $role instanceof Role) {
                throw new RuntimeException('Expected the configured permission.models.role to be an Eloquent model.');
            }

            $role->syncPermissions(
                Permission::query()->whereIn('name', $permissionNames)->where('guard_name', 'admin')->get()
            );

            return $role;
        });

        $this->audit->log('admin.role.created', $role, ['name' => $name, 'permissions' => $permissionNames]);

        return $role;
    }

    /** @param  array<int, string>  $permissionNames */
    public function update(Role $role, string $name, array $permissionNames): Role
    {
        $before = ['name' => $role->name, 'permissions' => $role->permissions()->pluck('name')->all()];

        DB::transaction(function () use ($role, $name, $permissionNames) {
            $role->update(['name' => $name]);
            $role->syncPermissions(
                Permission::query()->whereIn('name', $permissionNames)->where('guard_name', 'admin')->get()
            );
        });

        $this->audit->log('admin.role.updated', $role, [
            'before' => $before,
            'after' => ['name' => $name, 'permissions' => $permissionNames],
        ]);

        return $role;
    }
}
