<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

/**
 * Seeds the three starter delivery zones (FR-SHP-002), left inactive with zero
 * charges until the business sets real values (BRD Q-03).
 */
class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = ['Inside Dhaka', 'Dhaka Suburbs', 'Outside Dhaka'];

        foreach ($zones as $sortOrder => $name) {
            DeliveryZone::query()->firstOrCreate(
                ['name' => $name],
                [
                    'base_charge' => 0,
                    'free_delivery_threshold' => null,
                    'estimate_text' => null,
                    'is_active' => false,
                    'sort_order' => $sortOrder,
                ]
            );
        }
    }
}
