<?php

use App\Models\Area;
use App\Models\DeliveryZone;
use App\Models\District;
use App\Services\Shipping\DeliveryZoneService;
use Database\Seeders\BangladeshGeoSeeder;

beforeEach(function () {
    $this->seed(BangladeshGeoSeeder::class);
});

it('resolves the zone from the district when there is no area override (TC-SHP-002)', function () {
    $zone = DeliveryZone::create(['name' => 'Dhaka Metro', 'base_charge' => 60, 'is_active' => true]);
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $district->update(['delivery_zone_id' => $zone->id]);

    $resolved = app(DeliveryZoneService::class)->resolve($district->fresh(), null);

    expect($resolved?->id)->toBe($zone->id);
});

it('lets an area-level zone override the district-level zone (FR-SHP-002)', function () {
    $districtZone = DeliveryZone::create(['name' => 'Dhaka Metro', 'base_charge' => 60, 'is_active' => true]);
    $areaZone = DeliveryZone::create(['name' => 'Dhaka Remote', 'base_charge' => 100, 'is_active' => true]);
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $district->update(['delivery_zone_id' => $districtZone->id]);
    $area = Area::create(['district_id' => $district->id, 'name' => 'Savar', 'delivery_zone_id' => $areaZone->id, 'is_active' => true]);

    $resolved = app(DeliveryZoneService::class)->resolve($district->fresh(), $area);

    expect($resolved?->id)->toBe($areaZone->id);
});

it('falls back to the district zone when the area has no zone of its own', function () {
    $districtZone = DeliveryZone::create(['name' => 'Dhaka Metro', 'base_charge' => 60, 'is_active' => true]);
    $district = District::where('name', 'Dhaka')->firstOrFail();
    $district->update(['delivery_zone_id' => $districtZone->id]);
    $area = Area::create(['district_id' => $district->id, 'name' => 'Savar', 'delivery_zone_id' => null, 'is_active' => true]);

    $resolved = app(DeliveryZoneService::class)->resolve($district->fresh(), $area);

    expect($resolved?->id)->toBe($districtZone->id);
});

it('returns null when neither the district nor the area has a zone (undeliverable)', function () {
    $district = District::whereNull('delivery_zone_id')->firstOrFail();

    $resolved = app(DeliveryZoneService::class)->resolve($district, null);

    expect($resolved)->toBeNull();
});
