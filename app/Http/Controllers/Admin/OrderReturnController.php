<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReturnCondition;
use App\Enums\ReturnType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderReturnRequest;
use App\Models\Admin;
use App\Models\Order;
use App\Services\Orders\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/** FR-ORD-009, gated by orders.returns. */
class OrderReturnController extends Controller
{
    public function __construct(private readonly ReturnService $returns) {}

    public function store(StoreOrderReturnRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $lines = [];
        foreach ($data['lines'] as $orderItemId => $line) {
            if ((int) ($line['quantity'] ?? 0) < 1) {
                continue;
            }

            $lines[] = [
                'order_item_id' => (int) $orderItemId,
                'quantity' => (int) $line['quantity'],
                'condition' => ReturnCondition::from($line['condition']),
            ];
        }

        $this->returns->process($order, ReturnType::from($data['type']), $data['reason'], $lines, $actor);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Return processed.');
    }
}
