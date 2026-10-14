<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockAdjustmentRequest;
use App\Models\Admin;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** FR-INV-005/006, gated by inventory.adjust. */
class AdminInventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $defaultThreshold = (int) Setting::get('store', 'low_stock_default_threshold', 5);

        $variants = ProductVariant::query()
            ->with('product')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            })
            ->when($request->input('filter') === 'out_of_stock', fn ($q) => $q->where('stock', 0))
            ->when($request->input('filter') === 'low_stock', fn ($q) => $q->where('stock', '>', 0)
                ->whereRaw('stock <= COALESCE(low_stock_threshold, ?)', [$defaultThreshold]))
            ->orderBy('sku')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.index', [
            'variants' => $variants,
            'defaultThreshold' => $defaultThreshold,
        ]);
    }

    public function history(ProductVariant $variant): View
    {
        return view('admin.inventory.history', [
            'variant' => $variant->load('product'),
            'movements' => $variant->stockMovements()->with('admin')->latest('created_at')->paginate(25),
        ]);
    }

    public function adjust(StoreStockAdjustmentRequest $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validated();

        /** @var Admin $actor */
        $actor = Auth::guard('admin')->user();

        $this->inventory->adjust($variant, (int) $data['quantity'], $data['reason'], $actor);

        return redirect()->route('admin.inventory.history', $variant)->with('status', 'Stock adjusted.');
    }
}
