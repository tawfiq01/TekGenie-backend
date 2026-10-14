<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('logs in with email or mobile number and regenerates the session (TC-ACC-002)', function () {
    $user = User::factory()->create([
        'phone' => '01712345678',
        'email' => 'rahim@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->post('/login', [
        'email' => 'rahim@example.com',
        'password' => 'Password123!',
    ])->assertRedirect();
    $this->assertAuthenticatedAs($user);

    $this->post('/logout')->assertRedirect();
    $this->assertGuest();

    $this->post('/login', [
        'email' => '+8801712345678',
        'password' => 'Password123!',
    ])->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

it('throttles repeated failed logins (TC-ACC-003, FR-ACC-002, NFR-SEC-07)', function () {
    User::factory()->create([
        'email' => 'rahim@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => 'rahim@example.com',
            'password' => 'wrong-password',
        ]);
    }

    // The 6th attempt within a minute is rejected by the `throttle:login`
    // route middleware (config/fortify.php's named "login" limiter, 5/min
    // per email+IP) before it ever reaches the login action (EH-12).
    $response = $this->post('/login', [
        'email' => 'rahim@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(429);
    expect($response->headers->has('Retry-After'))->toBeTrue();
    $this->assertGuest();
});

it('refuses login for a deactivated customer account', function () {
    User::factory()->create([
        'email' => 'rahim@example.com',
        'password' => Hash::make('Password123!'),
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email' => 'rahim@example.com',
        'password' => 'Password123!',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
