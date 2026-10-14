<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

it('emails a single-use password reset link (TC-ACC-004, FR-ACC-003)', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'rahim@example.com']);

    $this->post('/forgot-password', ['email' => 'rahim@example.com'])->assertSessionHasNoErrors();

    $notification = null;
    Notification::assertSentTo($user, ResetPassword::class, function ($sentNotification) use (&$notification) {
        $notification = $sentNotification;

        return true;
    });

    $this->post('/reset-password', [
        'token' => $notification->token,
        'email' => 'rahim@example.com',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertSessionHasNoErrors();

    $this->post('/login', [
        'email' => 'rahim@example.com',
        'password' => 'NewPassword123!',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($user->fresh());
});

it('does not allow a password reset token to be reused (TC-ACC-004)', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'rahim@example.com']);

    $this->post('/forgot-password', ['email' => 'rahim@example.com']);

    $notification = null;
    Notification::assertSentTo($user, ResetPassword::class, function ($sentNotification) use (&$notification) {
        $notification = $sentNotification;

        return true;
    });

    $this->post('/reset-password', [
        'token' => $notification->token,
        'email' => 'rahim@example.com',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertSessionHasNoErrors();

    $this->post('/reset-password', [
        'token' => $notification->token,
        'email' => 'rahim@example.com',
        'password' => 'AnotherPassword123!',
        'password_confirmation' => 'AnotherPassword123!',
    ])->assertSessionHasErrors('email');
});
