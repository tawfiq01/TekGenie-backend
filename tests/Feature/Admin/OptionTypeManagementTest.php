<?php

use App\Models\Admin;
use App\Models\OptionType;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

it('creates an option type and manages its values (FR-CAT-004)', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    $this->actingAs($admin, 'admin')->post(route('admin.option-types.store'), [
        'name' => 'Colour',
        'display_style' => 'swatch',
    ])->assertRedirect();

    $optionType = OptionType::where('name', 'Colour')->firstOrFail();

    $this->actingAs($admin, 'admin')->post(route('admin.option-values.store', $optionType), [
        'value' => 'Black',
        'swatch_hex' => '#000000',
    ])->assertRedirect(route('admin.option-types.edit', $optionType));

    $value = $optionType->values()->where('value', 'Black')->firstOrFail();
    expect($value->swatch_hex)->toBe('#000000');

    $this->actingAs($admin, 'admin')->delete(route('admin.option-values.destroy', $value))
        ->assertRedirect(route('admin.option-types.edit', $optionType));

    expect($optionType->values()->where('value', 'Black')->exists())->toBeFalse();
});

it('refuses a duplicate option type name', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('Catalog Manager');

    OptionType::create(['name' => 'Colour', 'display_style' => 'swatch']);

    $this->actingAs($admin, 'admin')->post(route('admin.option-types.store'), [
        'name' => 'Colour',
        'display_style' => 'chip',
    ])->assertSessionHasErrors('name');
});
