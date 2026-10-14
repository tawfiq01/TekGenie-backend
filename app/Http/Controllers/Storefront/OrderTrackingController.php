<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\BdPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** FR-ACC-008; BR-15: a guest must supply both the order number and the phone used on it. */
class OrderTrackingController extends Controller
{
    public function show(): View
    {
        return view('storefront.track-order');
    }

    public function lookup(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'order_number' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $phone = BdPhone::normalize($data['phone']);

        $order = $phone
            ? Order::where('order_number', $data['order_number'])
                ->where('phone', $phone)
                ->with(['items', 'statusHistories' => fn ($q) => $q->where('is_customer_visible', true)->orderBy('created_at')])
                ->first()
            : null;

        if ($order === null) {
            return back()->withErrors([
                'order_number' => 'We could not find an order matching that order number and phone number.',
            ])->withInput();
        }

        return view('storefront.track-order', [
            'order' => $order,
            'maskedAddress' => $this->maskAddress($order->address_line),
        ]);
    }

    private function maskAddress(string $addressLine): string
    {
        return preg_replace('/\d+/', '••', $addressLine) ?? $addressLine;
    }
}
