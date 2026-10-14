<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUpdateOrderStatusRequest;
use App\Http\Requests\Admin\StoreOrderNoteRequest;
use App\Http\Requests\Admin\UpdateOrderAddressRequest;
use App\Http\Requests\Admin\UpdateOrderCourierRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Admin;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Services\Orders\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** FR-ORD-001..008/012, gated by orders.view/orders.update-status/orders.cancel. */
class OrderController extends Controller
{
    public function __construct(private readonly OrderStatusService $statuses) {}

    public function index(Request $request): View
    {
        $orders = Order::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(fn ($q) => $q
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->input('payment_status')))
            ->when($request->filled('zone'), fn ($query) => $query->where('delivery_zone_id', $request->input('zone')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('placed_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('placed_at', '<=', $request->input('date_to')))
            ->orderByDesc('placed_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'zones' => DeliveryZone::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'items',
            'statusHistories' => fn ($q) => $q->orderBy('created_at'),
            'notes' => fn ($q) => $q->orderByDesc('created_at'),
            'notes.admin',
            'payments.refunds',
            'returns.items.orderItem',
            'coupon',
        ]);

        return view('admin.orders.show', [
            'order' => $order,
            'allowedTransitions' => $order->status->allowedTransitions(),
        ]);
    }

    public function packingSlip(Order $order): View
    {
        return view('admin.orders.packing-slip', ['order' => $order->load('items')]);
    }

    public function transition(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->statuses->transition($order, OrderStatus::from($data['status']), $actor, $data['note'] ?? null);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order status updated.');
    }

    public function bulkTransition(BulkUpdateOrderStatusRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $result = $this->statuses->bulkTransition($data['order_ids'], OrderStatus::from($data['status']), $actor);

        return redirect()->route('admin.orders.index')
            ->with('status', "{$result['updated']} order(s) updated, {$result['skipped']} skipped (invalid transition).");
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->statuses->transition($order, OrderStatus::Cancelled, $actor, $data['reason']);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order cancelled.');
    }

    public function storeNote(StoreOrderNoteRequest $request, Order $order): RedirectResponse
    {
        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $order->notes()->create(['admin_id' => $actor->id, 'body' => $request->validated()['body']]);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Note added.');
    }

    public function updateCourier(UpdateOrderCourierRequest $request, Order $order): RedirectResponse
    {
        $order->forceFill($request->validated())->save();

        return redirect()->route('admin.orders.show', $order)->with('status', 'Courier details updated.');
    }

    public function updateAddress(UpdateOrderAddressRequest $request, Order $order): RedirectResponse
    {
        if (! in_array($order->status, [OrderStatus::Pending, OrderStatus::Confirmed], true)) {
            abort(422, 'The address can only be edited while the order is Pending or Confirmed.');
        }

        $order->forceFill($request->validated())->save();

        return redirect()->route('admin.orders.show', $order)->with('status', 'Address updated.');
    }
}
