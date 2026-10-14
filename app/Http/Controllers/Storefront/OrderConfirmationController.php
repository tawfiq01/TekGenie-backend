<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FR-CHK-010/FR-ORD-008/011: viewable and cancellable only by the order's
 * owner — an authenticated customer who placed it, or (for guests) a
 * validly signed, time-limited link. BR-19: never reveal that an order
 * exists to anyone else (404, not 403).
 */
class OrderConfirmationController extends Controller
{
    public function __construct(private readonly OrderStatusService $statuses) {}

    public function show(Request $request, Order $order): View
    {
        $this->assertOwned($request, $order);

        return view('storefront.confirmation', [
            'order' => $order->load([
                'items',
                'statusHistories' => fn ($q) => $q->where('is_customer_visible', true)->orderBy('created_at'),
            ]),
            'cancelUrl' => URL::temporarySignedRoute('orders.cancel', now()->addHours(24), ['order' => $order->id]),
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->assertOwned($request, $order);

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $this->statuses->cancelByCustomer($order, Auth::id(), $data['reason'] ?? 'Cancelled by customer');

        $redirectUrl = Auth::check() && $order->user_id === Auth::id()
            ? route('checkout.confirmation', $order)
            : URL::temporarySignedRoute('checkout.confirmation', now()->addHours(24), ['order' => $order->id]);

        return redirect()->to($redirectUrl)->with('status', 'Your order has been cancelled.');
    }

    private function assertOwned(Request $request, Order $order): void
    {
        $ownedBySession = Auth::check() && $order->user_id === Auth::id();

        if (! $ownedBySession && ! $request->hasValidSignature()) {
            throw new NotFoundHttpException;
        }
    }
}
