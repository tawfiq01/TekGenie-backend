<?php

use App\Models\Address;
use App\Models\District;
use App\Models\Division;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('anonymises the account, keeps order records, and makes login impossible afterwards (TC-ACC-011)', function () {
    $user = User::factory()->create(['name' => 'Rahim Uddin', 'password' => Hash::make('Password123!')]);
    $order = placeTestOrder();
    $order->update(['user_id' => $user->id]);
    Address::create([
        'user_id' => $user->id, 'recipient_name' => 'Rahim', 'phone' => '01712345678',
        'division_id' => Division::first()->id, 'district_id' => District::first()->id,
        'address_line' => 'Somewhere', 'is_default' => true,
    ]);

    $this->actingAs($user)
        ->delete(route('account.destroy'), ['password' => 'Password123!'])
        ->assertRedirect(route('home'));

    $this->assertGuest();

    $user->refresh();
    expect($user->name)->toBe('Deleted user')
        ->and($user->trashed())->toBeTrue()
        ->and($user->addresses()->count())->toBe(0);

    // The order itself is untouched — it already carries its own snapshot.
    expect(Order::find($order->id))->not->toBeNull()
        ->and($order->fresh()->customer_name)->not->toBe('Deleted user');

    $this->post('/login', ['email' => $user->email, 'password' => 'Password123!'])->assertSessionHasErrors();
    $this->assertGuest();
});

it('requires the correct password to delete the account', function () {
    $user = User::factory()->create(['password' => Hash::make('Password123!')]);

    $this->actingAs($user)
        ->delete(route('account.destroy'), ['password' => 'WrongPassword!'])
        ->assertSessionHasErrors('password');

    $this->assertAuthenticatedAs($user);
});
