<?php

namespace App\Services\Shipping;

use App\Models\Area;
use App\Models\DeliveryZone;
use App\Models\District;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * FR-SHP-002/003/005: area assignment overrides district assignment; a
 * district with no zone (and no area override) is undeliverable.
 */
class DeliveryZoneService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function resolve(District $district, ?Area $area): ?DeliveryZone
    {
        if ($area !== null && $area->delivery_zone_id !== null) {
            return $area->deliveryZone;
        }

        return $district->deliveryZone;
    }

    /** @param  array<string, mixed>  $data */
    public function create(array $data): DeliveryZone
    {
        $zone = DeliveryZone::create([
            'name' => $data['name'],
            'base_charge' => $data['base_charge'],
            'free_delivery_threshold' => $data['free_delivery_threshold'] ?? null,
            'estimate_text' => $data['estimate_text'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        $this->audit->log('admin.delivery-zone.created', $zone, ['name' => $zone->name]);

        return $zone;
    }

    /** @param  array<string, mixed>  $data */
    public function update(DeliveryZone $zone, array $data): DeliveryZone
    {
        $before = $zone->only(['name', 'base_charge', 'is_active']);

        $zone->forceFill([
            'name' => $data['name'],
            'base_charge' => $data['base_charge'],
            'free_delivery_threshold' => $data['free_delivery_threshold'] ?? null,
            'estimate_text' => $data['estimate_text'] ?? null,
            'sort_order' => $data['sort_order'] ?? $zone->sort_order,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $zone->is_active,
        ])->save();

        $this->audit->log('admin.delivery-zone.updated', $zone, [
            'before' => $before,
            'after' => $zone->only(['name', 'base_charge', 'is_active']),
        ]);

        return $zone;
    }

    /** @param  array<int, int|null>  $districtZoneIds  districtId => zoneId|null */
    public function assignDistricts(array $districtZoneIds): void
    {
        DB::transaction(function () use ($districtZoneIds) {
            foreach ($districtZoneIds as $districtId => $zoneId) {
                District::whereKey($districtId)->update(['delivery_zone_id' => $zoneId ?: null]);
            }
        });

        $this->audit->log('admin.delivery-zone.districts-assigned', null, ['count' => count($districtZoneIds)]);
    }

    /** @param  array<string, mixed>  $data */
    public function createArea(array $data): Area
    {
        $area = Area::create([
            'district_id' => $data['district_id'],
            'name' => $data['name'],
            'name_bn' => $data['name_bn'] ?? null,
            'delivery_zone_id' => $data['delivery_zone_id'] ?? null,
            'is_active' => true,
        ]);

        $this->audit->log('admin.delivery-zone.area-created', $area, ['name' => $area->name]);

        return $area;
    }
}
