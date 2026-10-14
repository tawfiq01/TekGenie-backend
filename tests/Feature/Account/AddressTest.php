<?php

use App\Models\Address;
use App\Models\District;
use App\Models\User;
use Database\Seeders\BangladeshGeoSeeder;

beforeEach(function () {
    $this->seed(BangladeshGeoSeeder::class);
});

it('adds the first address as default automatically and a second one only if requested (TC-ACC-007)', function () {
    $user = User::factory()->create();
    $dhaka = District::where('name', 'Dhaka')->firstOrFail();
    $chittagong = District::where('name', '!=', 'Dhaka')->firstOrFail();

    $this->actingAs($user)->post(route('account.addresses.store'), [
        'recipient_name' => 'Karim', 'phone' => '01712345678',
        'division_id' => $dhaka->division_id, 'district_id' => $dhaka->id,
        'address_line' => 'House 1, Road 1',
    ])->assertRedirect(route('account.addresses.index'));

    $first = $user->addresses()->first();
    expect($first->is_default)->toBeTrue();

    $this->actingAs($user)->post(route('account.addresses.store'), [
        'recipient_name' => 'Karim Office', 'phone' => '01712345678',
        'division_id' => $chittagong->division_id, 'district_id' => $chittagong->id,
        'address_line' => 'Office address', 'is_default' => '1',
    ])->assertRedirect(route('account.addresses.index'));

    expect($first->fresh()->is_default)->toBeFalse()
        ->and($user->addresses()->where('is_default', true)->count())->toBe(1);
});

it('promotes another address to default when the default is deleted', function () {
    $user = User::factory()->create();
    $district = District::firstOrFail();

    $this->actingAs($user)->post(route('account.addresses.store'), [
        'recipient_name' => 'A', 'phone' => '01712345678',
        'division_id' => $district->division_id, 'district_id' => $district->id, 'address_line' => 'Address A',
    ]);
    $default = $user->addresses()->first();

    $this->actingAs($user)->post(route('account.addresses.store'), [
        'recipient_name' => 'B', 'phone' => '01712345678',
        'division_id' => $district->division_id, 'district_id' => $district->id, 'address_line' => 'Address B',
    ]);

    $this->actingAs($user)->delete(route('account.addresses.destroy', $default));

    expect($user->addresses()->count())->toBe(1)
        ->and($user->addresses()->first()->is_default)->toBeTrue();
});

it('never lets a customer edit or delete another customer\'s address (TC-ACC-008, BR-19)', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $district = District::firstOrFail();

    $address = Address::create([
        'user_id' => $owner->id, 'recipient_name' => 'Owner', 'phone' => '01712345678',
        'division_id' => $district->division_id, 'district_id' => $district->id,
        'address_line' => 'Owner address', 'is_default' => true,
    ]);

    $this->actingAs($other)->get(route('account.addresses.edit', $address))->assertNotFound();
    $this->actingAs($other)->put(route('account.addresses.update', $address), [
        'recipient_name' => 'Hacked', 'phone' => '01712345678',
        'division_id' => $district->division_id, 'district_id' => $district->id, 'address_line' => 'Hacked address',
    ])->assertNotFound();
    $this->actingAs($other)->delete(route('account.addresses.destroy', $address))->assertNotFound();

    expect($address->fresh()->recipient_name)->toBe('Owner')
        ->and(Address::count())->toBe(1);
});
