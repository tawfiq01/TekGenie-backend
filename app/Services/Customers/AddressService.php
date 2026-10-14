<?php

namespace App\Services\Customers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** FR-ACC-006: exactly one default address per customer, enforced here (not a DB constraint). */
class AddressService
{
    /** @param  array<string, mixed>  $data */
    public function create(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data): Address {
            $makeDefault = (bool) ($data['is_default'] ?? false) || ! $user->addresses()->exists();

            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                ...$this->onlyAddressFields($data),
                'is_default' => $makeDefault,
            ]);
        });
    }

    /** @param  array<string, mixed>  $data */
    public function update(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data): Address {
            $makeDefault = (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                $address->user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                ...$this->onlyAddressFields($data),
                'is_default' => $makeDefault || $address->is_default,
            ]);

            return $address;
        });
    }

    public function setDefault(Address $address): void
    {
        DB::transaction(function () use ($address): void {
            $address->user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });
    }

    public function delete(Address $address): void
    {
        DB::transaction(function () use ($address): void {
            $wasDefault = $address->is_default;
            $user = $address->user;
            $address->delete();

            if ($wasDefault) {
                $next = $user->addresses()->latest('id')->first();
                $next?->update(['is_default' => true]);
            }
        });
    }

    /** @param  array<string, mixed>  $data
     * @return array<string, mixed> */
    private function onlyAddressFields(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'label', 'recipient_name', 'phone', 'division_id', 'district_id',
            'area_id', 'area_text', 'address_line', 'postcode',
        ]));
    }
}
