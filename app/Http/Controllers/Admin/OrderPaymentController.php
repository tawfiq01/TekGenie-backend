<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RefundMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderCollectionRequest;
use App\Http\Requests\Admin\StoreOrderRefundRequest;
use App\Models\Admin;
use App\Models\Order;
use App\Services\Orders\OrderPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/** FR-PAY-003/FR-ORD-010, gated by orders.payments. */
class OrderPaymentController extends Controller
{
    public function __construct(private readonly OrderPaymentService $payments) {}

    public function storeCollection(StoreOrderCollectionRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->payments->recordCollection($order, (float) $data['collected_amount'], $data['variance_note'] ?? null, $actor);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Collection recorded.');
    }

    public function storeRefund(StoreOrderRefundRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->payments->recordRefund(
            $order,
            (float) $data['amount'],
            RefundMethod::from($data['method']),
            $data['reference'] ?? null,
            $data['note'] ?? null,
            $actor,
        );

        return redirect()->route('admin.orders.show', $order)->with('status', 'Refund recorded.');
    }
}
