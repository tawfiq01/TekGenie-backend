<?php

use App\Models\User;

it('registers with a unique phone and email, and refuses a duplicate (TC-ACC-001)', function () {
    $this->post('/register', [
        'name' => 'Rahim Uddin',
        'phone' => '01712345678',
        'email' => 'rahim@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertRedirect();

    $this->assertAuthenticated();
    expect(User::where('phone', '01712345678')->count())->toBe(1);

    auth()->logout();

    $this->from('/register')->post('/register', [
        'name' => 'Rahim Uddin',
        'phone' => '01712345678',
        'email' => 'rahim@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertRedirect('/register')->assertSessionHasErrors('email');

    expect(User::where('phone', '01712345678')->count())->toBe(1);
    $this->assertGuest();
});

it('refuses a duplicate phone even with a different email (VR-02, FR-ACC-001)', function () {
    User::factory()->create(['phone' => '01712345678']);

    $this->from('/register')->post('/register', [
        'name' => 'Someone Else',
        'phone' => '01712345678',
        'email' => 'someone-else@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertRedirect('/register')->assertSessionHasErrors('phone');
});
