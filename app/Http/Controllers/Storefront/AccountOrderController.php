<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * FR-ACC-007: order history list. Detail, timeline and cancel are already
 * served by OrderConfirmationController (checkout.confirmation/orders.cancel),
 * which already enforces session-owner-or-signed-link access (BR-19).
 */
class AccountOrderController extends Controller
{
    public function index(): View
    {
        return view('account.orders.index', [
            'orders' => Auth::guard('web')->user()->orders()->latest('placed_at')->paginate(10),
        ]);
    }
}
