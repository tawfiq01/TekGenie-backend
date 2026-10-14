<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database (DOC-06 §9). DemoCatalogSeeder only
     * runs outside production, and is never run against a live store.
     */
    public function run(): void
    {
        $this->call([
            BangladeshGeoSeeder::class,
            DeliveryZoneSeeder::class,
            RoleAndPermissionSeeder::class,
            SuperAdminSeeder::class,
            SettingsSeeder::class,
            PageSeeder::class,
        ]);

        if (! app()->isProduction()) {
            $this->call(DemoCatalogSeeder::class);
        }
    }
}
