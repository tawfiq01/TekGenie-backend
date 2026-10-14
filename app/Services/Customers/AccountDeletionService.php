<?php

namespace App\Services\Customers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * FR-ACC-011 (S): anonymises personal data and soft-deletes the account;
 * order records are untouched since every order already carries its own
 * snapshot of the customer's name/phone/email (FR-ORD-001) independent of
 * the User row.
 */
class AccountDeletionService
{
    public function delete(User $user): void
    {
        $user->forceFill([
            'name' => 'Deleted user',
            'email' => 'deleted-'.$user->id.'-'.Str::random(8).'@deleted.invalid',
            // users.phone is NOT NULL and unique; this can never collide
            // with a real BD mobile number (those are always 01[3-9]…).
            'phone' => '0000'.str_pad((string) $user->id, 7, '0', STR_PAD_LEFT),
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => null,
        ])->save();

        $user->addresses()->delete();
        $user->wishlistItems()->delete();

        $user->delete();
    }
}
