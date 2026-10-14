<?php

namespace App\Services\Reporting;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturnItem;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * FR-RPT-001..006. Every figure here is computed live from orders, order
 * items, stock and coupon usage — nothing is pre-aggregated into its own
 * reporting table, and nothing is ever seeded (FR-RPT-001/FR-REV-005: an
 * empty period shows an empty state, not zeros dressed as data).
 */
class ReportService
{
    /**
     * FR-RPT-001: dashboard figures for a period — cancelled orders never count as sales.
     *
     * @return array{order_count: int, gross_sales: string, average_order_value: string, orders_by_status: Collection<string, int>, low_stock_count: int, pending_reviews_count: int, latest_orders: Collection<int, Order>}
     */
    public function dashboard(Carbon $from, Carbon $to): array
    {
        $sales = Order::whereBetween('placed_at', [$from, $to])->where('status', '!=', OrderStatus::Cancelled);
        $orderCount = (clone $sales)->count();
        $grossSales = (float) (clone $sales)->sum('grand_total');

        $byStatus = Order::whereBetween('placed_at', [$from, $to])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $threshold = (int) Setting::get('store', 'low_stock_default_threshold', 5);

        return [
            'order_count' => $orderCount,
            'gross_sales' => number_format($grossSales, 2, '.', ''),
            'average_order_value' => number_format($orderCount > 0 ? $grossSales / $orderCount : 0, 2, '.', ''),
            'orders_by_status' => $byStatus,
            'low_stock_count' => ProductVariant::where('is_active', true)->where('stock', '>', 0)
                ->whereRaw('stock <= COALESCE(low_stock_threshold, ?)', [$threshold])->count(),
            'pending_reviews_count' => Review::where('status', ReviewStatus::Pending)->count(),
            'latest_orders' => Order::latest('placed_at')->take(5)->get(),
        ];
    }

    /**
     * FR-RPT-002/006: sales by day, orders not Cancelled; cancelled/returned value shown separately.
     *
     * @return array{rows: Collection<int, array{period: mixed, orders: int, gross: numeric-string, discount: numeric-string, delivery: numeric-string, net: numeric-string}>, totals: array{orders: int, gross: numeric-string, discount: numeric-string, delivery: numeric-string, net: numeric-string, cancelled_value: numeric-string, returned_value: numeric-string}}
     */
    public function sales(Carbon $from, Carbon $to, ?int $zoneId = null): array
    {
        $base = Order::whereBetween('placed_at', [$from, $to])
            ->where('status', '!=', OrderStatus::Cancelled)
            ->when($zoneId !== null, fn ($q) => $q->where('delivery_zone_id', $zoneId));

        $rows = (clone $base)
            ->selectRaw('DATE(placed_at) as period, count(*) as orders, sum(grand_total) as gross, sum(discount_total) as discount, sum(delivery_charge) as delivery, sum(subtotal - discount_total) as net')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $cancelledValue = (float) Order::whereBetween('cancelled_at', [$from, $to])->where('status', OrderStatus::Cancelled)->sum('grand_total');

        $returnedValue = (float) OrderReturnItem::query()
            ->join('order_items', 'order_items.id', '=', 'order_return_items.order_item_id')
            ->join('order_returns', 'order_returns.id', '=', 'order_return_items.order_return_id')
            ->whereBetween('order_returns.processed_at', [$from, $to])
            ->sum(DB::raw('order_items.unit_price * order_return_items.quantity'));

        $formattedRows = $rows->map(fn ($row) => [
            'period' => $row->getAttribute('period'),
            'orders' => (int) $row->getAttribute('orders'),
            'gross' => number_format((float) $row->getAttribute('gross'), 2, '.', ''),
            'discount' => number_format((float) $row->getAttribute('discount'), 2, '.', ''),
            'delivery' => number_format((float) $row->getAttribute('delivery'), 2, '.', ''),
            'net' => number_format((float) $row->getAttribute('net'), 2, '.', ''),
        ]);

        return [
            'rows' => $formattedRows,
            'totals' => [
                'orders' => (int) $rows->sum('orders'),
                'gross' => number_format((float) $rows->sum('gross'), 2, '.', ''),
                'discount' => number_format((float) $rows->sum('discount'), 2, '.', ''),
                'delivery' => number_format((float) $rows->sum('delivery'), 2, '.', ''),
                'net' => number_format((float) $rows->sum('net'), 2, '.', ''),
                'cancelled_value' => number_format($cancelledValue, 2, '.', ''),
                'returned_value' => number_format($returnedValue, 2, '.', ''),
            ],
        ];
    }

    /**
     * FR-RPT-003: units sold, revenue and returns per product for a period (grouped by the order's own snapshot, not live catalogue rows).
     *
     * @return Collection<int, array{product_name: mixed, sku: mixed, units_sold: int, revenue: numeric-string, returned_units: int}>
     */
    public function products(Carbon $from, Carbon $to): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.placed_at', [$from, $to])
            ->where('orders.status', '!=', OrderStatus::Cancelled)
            ->selectRaw('order_items.product_name, order_items.sku, sum(order_items.quantity) as units_sold, sum(order_items.line_total) as revenue, sum(order_items.returned_quantity) as returned_units')
            ->groupBy('order_items.product_name', 'order_items.sku')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'product_name' => $row->getAttribute('product_name'),
                'sku' => $row->getAttribute('sku'),
                'units_sold' => (int) $row->getAttribute('units_sold'),
                'revenue' => number_format((float) $row->getAttribute('revenue'), 2, '.', ''),
                'returned_units' => (int) $row->getAttribute('returned_units'),
            ]);
    }

    /**
     * FR-RPT-004: stock on hand, low/out of stock, and stock value at current price.
     *
     * @return array{rows: Collection<int, array{sku: string, product_name: string, stock: int<0, max>, state: string, stock_value: float}>, totals: array{stock_value: numeric-string, low_stock_count: int<0, max>, out_of_stock_count: int<0, max>}}
     */
    public function inventory(): array
    {
        $threshold = (int) Setting::get('store', 'low_stock_default_threshold', 5);

        $rows = ProductVariant::with('product')->where('is_active', true)->get()->map(fn (ProductVariant $variant) => [
            'sku' => $variant->sku,
            'product_name' => $variant->product->name ?? '—',
            'stock' => $variant->stock,
            'state' => $variant->stockState($threshold),
            'stock_value' => $variant->stock * (float) $variant->price,
        ]);

        return [
            'rows' => $rows,
            'totals' => [
                'stock_value' => number_format((float) $rows->sum('stock_value'), 2, '.', ''),
                'low_stock_count' => $rows->where('state', 'low')->count(),
                'out_of_stock_count' => $rows->where('state', 'out_of_stock')->count(),
            ],
        ];
    }

    /**
     * FR-RPT-005: usage count and discount given per coupon for a period (released/cancelled usages excluded).
     *
     * @return Collection<int, array{code: string, used_count: int, usage_limit: int<0, max>|null, total_discount: numeric-string}>
     */
    public function coupons(Carbon $from, Carbon $to): Collection
    {
        return Coupon::query()
            ->withCount(['usages as period_usage_count' => fn ($q) => $q->whereBetween('created_at', [$from, $to])->whereNull('released_at')])
            ->withSum(['usages as period_discount_total' => fn ($q) => $q->whereBetween('created_at', [$from, $to])->whereNull('released_at')], 'discount_amount')
            ->having('period_usage_count', '>', 0)
            ->get()
            ->map(fn (Coupon $coupon) => [
                'code' => $coupon->code,
                'used_count' => (int) $coupon->getAttribute('period_usage_count'),
                'usage_limit' => $coupon->usage_limit,
                'total_discount' => number_format((float) ($coupon->getAttribute('period_discount_total') ?? 0), 2, '.', ''),
            ]);
    }
}
