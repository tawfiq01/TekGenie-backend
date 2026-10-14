<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the admin-guard permissions and default roles defined in SRS §4 / SAD §7.
 */
class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'products.view', 'products.edit', 'products.price',
            'inventory.adjust',
            'orders.view', 'orders.update-status', 'orders.cancel', 'orders.returns', 'orders.payments',
            'customers.view', 'customers.manage',
            'coupons.manage',
            'content.manage',
            'reviews.moderate',
            'settings.manage', 'settings.seo',
            'shipping.manage',
            'payments.manage',
            'staff.manage',
            'reports.view',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }

        $roles = [
            'Super Admin' => $permissions,
            'Store Manager' => [
                'products.view', 'products.edit', 'products.price',
                'inventory.adjust',
                'orders.view', 'orders.update-status', 'orders.cancel', 'orders.returns', 'orders.payments',
                'customers.view', 'customers.manage',
                'coupons.manage',
                'content.manage',
                'reviews.moderate',
                'settings.manage', 'settings.seo',
                'shipping.manage',
                'payments.manage',
                'reports.view',
                'audit.view',
            ],
            'Order Manager' => [
                'products.view',
                'orders.view', 'orders.update-status', 'orders.cancel', 'orders.returns', 'orders.payments',
                'customers.view',
                'reports.view',
            ],
            'Catalog Manager' => [
                'products.view', 'products.edit', 'products.price',
                'inventory.adjust',
                'orders.view',
                'reports.view',
            ],
            'Content Editor' => [
                'products.view',
                'coupons.manage',
                'content.manage',
                'reviews.moderate',
                'settings.seo',
            ],
            'Support' => [
                'products.view',
                'orders.view',
                'customers.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'admin');
            $role->syncPermissions(
                Permission::query()->whereIn('name', $rolePermissions)->where('guard_name', 'admin')->get()
            );
        }
    }
}
