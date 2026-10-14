<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one Super Admin from .env values (DOC-06 §9). Change the password
 * immediately after first login in a real environment.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');

        $admin = Admin::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $admin->syncRoles(['Super Admin']);
    }
}
