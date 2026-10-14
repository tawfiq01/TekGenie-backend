<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Auth\Events\Login;

/** FR-CRT-002: merge the guest cart into the customer's cart on login. */
class MergeGuestCartOnLogin
{
    public function __construct(private readonly CartService $cart) {}

    public function handle(Login $event): void
    {
        if ($event->guard !== 'web' || ! $event->user instanceof User) {
            return;
        }

        $this->cart->mergeGuestCartIntoUser($event->user);
    }
}
