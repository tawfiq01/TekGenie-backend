<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Staff & role management (FR-ADM-002). Enforces SRS §4's business rules:
 * the last active Super Admin can't be deactivated or demoted, and staff
 * can't edit their own roles.
 */
class StaffManagementService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array{name: string, email: string, password: string}  $data
     * @param  array<int, string>  $roleNames
     */
    public function create(array $data, array $roleNames): Admin
    {
        $admin = DB::transaction(function () use ($data, $roleNames) {
            $admin = Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $admin->syncRoles($roleNames);

            return $admin;
        });

        $this->audit->log('admin.staff.created', $admin, [
            'name' => $admin->name,
            'email' => $admin->email,
            'roles' => $roleNames,
        ]);

        return $admin;
    }

    /**
     * @param  array{name: string, email: string, roles: array<int, string>, is_active?: bool}  $data
     */
    public function update(Admin $actor, Admin $admin, array $data): Admin
    {
        $desiredActive = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $admin->is_active;
        $desiredRoles = $data['roles'];

        if ($actor->is($admin) && $this->rolesChanged($admin, $desiredRoles)) {
            throw ValidationException::withMessages([
                'roles' => 'You cannot edit your own roles.',
            ]);
        }

        if ($this->isLastActiveSuperAdmin($admin)) {
            if (! $desiredActive) {
                throw ValidationException::withMessages([
                    'is_active' => 'The last active Super Admin cannot be deactivated.',
                ]);
            }

            if (! in_array('Super Admin', $desiredRoles, true)) {
                throw ValidationException::withMessages([
                    'roles' => 'The last active Super Admin cannot be demoted.',
                ]);
            }
        }

        return DB::transaction(function () use ($admin, $data, $desiredActive, $desiredRoles) {
            $before = $admin->only(['name', 'email', 'is_active']) + [
                'roles' => $admin->roles()->pluck('name')->all(),
            ];

            $admin->forceFill([
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => $desiredActive,
            ])->save();

            $admin->syncRoles($desiredRoles);

            $this->audit->log('admin.staff.updated', $admin, [
                'before' => $before,
                'after' => $admin->only(['name', 'email', 'is_active']) + ['roles' => $desiredRoles],
            ]);

            return $admin;
        });
    }

    /** @param  array<int, string>  $desiredRoles */
    private function rolesChanged(Admin $admin, array $desiredRoles): bool
    {
        $current = $admin->roles()->pluck('name')->sort()->values()->all();
        $desired = collect($desiredRoles)->sort()->values()->all();

        return $current !== $desired;
    }

    private function isLastActiveSuperAdmin(Admin $admin): bool
    {
        if (! $admin->is_active || ! $admin->hasRole('Super Admin')) {
            return false;
        }

        return Admin::query()
            ->role('Super Admin')
            ->where('is_active', true)
            ->where('id', '!=', $admin->id)
            ->doesntExist();
    }
}
