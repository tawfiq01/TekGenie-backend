<?php

use App\Models\User;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('updates name, email and phone (FR-ACC-005)', function () {
    $user = User::factory()->create(['phone' => '01712345678', 'email' => 'old@example.com']);

    $this->actingAs($user)->put('/user/profile-information', [
        'name' => 'New Name',
        'email' => 'old@example.com',
        'phone' => '01799999999',
    ])->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->name)->toBe('New Name')
        ->and($user->phone)->toBe('01799999999');
});

it('refuses a phone number already used by another account', function () {
    User::factory()->create(['phone' => '01711111111']);
    $user = User::factory()->create(['phone' => '01712345678']);

    $this->actingAs($user)->put('/user/profile-information', [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => '01711111111',
    ])->assertSessionHasErrors(['phone'], null, 'updateProfileInformation');

    expect($user->fresh()->phone)->toBe('01712345678');
});

it('rejects the wrong current password and accepts the correct one when changing password (TC-ACC-006)', function () {
    $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);

    $this->actingAs($user)->put('/user/password', [
        'current_password' => 'WrongPassword!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertSessionHasErrors(['current_password'], null, 'updatePassword');

    $this->actingAs($user)->put('/user/password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
});

it('fires OtherDeviceLogout so AuthenticateSession invalidates other sessions on password change (FR-ACC-010)', function () {
    $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);
    Event::fake([OtherDeviceLogout::class]);

    $this->actingAs($user)->put('/user/password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertSessionHasNoErrors();

    Event::assertDispatched(OtherDeviceLogout::class);
    expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
});
