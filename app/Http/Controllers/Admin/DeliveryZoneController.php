<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignDistrictZonesRequest;
use App\Http\Requests\Admin\StoreAreaRequest;
use App\Http\Requests\Admin\StoreDeliveryZoneRequest;
use App\Http\Requests\Admin\UpdateDeliveryZoneRequest;
use App\Models\Area;
use App\Models\DeliveryZone;
use App\Models\District;
use App\Services\Shipping\DeliveryZoneService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** FR-SHP-002/003/005, gated by shipping.manage. */
class DeliveryZoneController extends Controller
{
    public function __construct(private readonly DeliveryZoneService $zones) {}

    public function index(): View
    {
        return view('admin.delivery-zones.index', [
            'zones' => DeliveryZone::query()->withCount('districts')->orderBy('sort_order')->get(),
            'unassignedDistricts' => District::query()->whereNull('delivery_zone_id')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.delivery-zones.create');
    }

    public function store(StoreDeliveryZoneRequest $request): RedirectResponse
    {
        $this->zones->create($request->validated());

        return redirect()->route('admin.delivery-zones.index')->with('status', 'Delivery zone created.');
    }

    public function edit(DeliveryZone $deliveryZone): View
    {
        return view('admin.delivery-zones.edit', [
            'zone' => $deliveryZone,
            'areas' => Area::query()->where('delivery_zone_id', $deliveryZone->id)->with('district')->get(),
        ]);
    }

    public function update(UpdateDeliveryZoneRequest $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        $this->zones->update($deliveryZone, $request->validated());

        return redirect()->route('admin.delivery-zones.index')->with('status', 'Delivery zone updated.');
    }

    public function districts(): View
    {
        return view('admin.delivery-zones.districts', [
            'districts' => District::query()->with('division')->orderBy('division_id')->orderBy('name')->get(),
            'zones' => DeliveryZone::query()->orderBy('name')->get(),
        ]);
    }

    public function assignDistricts(AssignDistrictZonesRequest $request): RedirectResponse
    {
        $this->zones->assignDistricts($request->validated()['zones']);

        return redirect()->route('admin.delivery-zones.districts')->with('status', 'District assignments updated.');
    }

    public function storeArea(StoreAreaRequest $request): RedirectResponse
    {
        $this->zones->createArea($request->validated());

        return redirect()->route('admin.delivery-zones.districts')->with('status', 'Area added.');
    }
}
