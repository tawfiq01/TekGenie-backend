<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FR-CHK-010: viewable only by the order's owner — an authenticated
 * customer who placed it, or (for guests) a validly signed, time-limited
 * link. BR-19: never reveal that an order exists to anyone else (404, not 403).
 */
class OrderConfirmationController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $ownedBySession = Auth::check() && $order->user_id === Auth::id();

        if (! $ownedBySession && ! $request->hasValidSignature()) {
            throw new NotFoundHttpException;
        }

        return view('storefront.confirmation', [
            'order' => $order->load('items'),
        ]);
    }
}
