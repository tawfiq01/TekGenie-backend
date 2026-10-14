<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Services\Audit\AuditLogger;
use App\Services\Reporting\CsvExporter;
use App\Services\Reporting\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** FR-RPT-002..006, gated by reports.view. */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly AuditLogger $audit,
    ) {}

    public function sales(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);
        $zoneId = $request->integer('zone_id') ?: null;

        return view('admin.reports.sales', [
            'report' => $this->reports->sales($from, $to, $zoneId),
            'from' => $from,
            'to' => $to,
            'zoneId' => $zoneId,
            'zones' => DeliveryZone::query()->orderBy('name')->get(),
        ]);
    }

    public function exportSales(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);
        $zoneId = $request->integer('zone_id') ?: null;
        $report = $this->reports->sales($from, $to, $zoneId);

        $this->logExport('sales', ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'zone_id' => $zoneId]);

        return CsvExporter::stream('sales-report.csv', ['Date', 'Orders', 'Gross', 'Discount', 'Delivery', 'Net'], $report['rows']->map(fn ($row) => array_values($row)));
    }

    public function products(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        return view('admin.reports.products', [
            'rows' => $this->reports->products($from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function exportProducts(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);
        $rows = $this->reports->products($from, $to);

        $this->logExport('products', ['from' => $from->toDateString(), 'to' => $to->toDateString()]);

        return CsvExporter::stream('product-report.csv', ['Product', 'SKU', 'Units sold', 'Revenue', 'Returned units'], $rows->map(fn ($row) => array_values($row)));
    }

    public function inventory(): View
    {
        return view('admin.reports.inventory', ['report' => $this->reports->inventory()]);
    }

    public function exportInventory(): StreamedResponse
    {
        $report = $this->reports->inventory();

        $this->logExport('inventory', []);

        return CsvExporter::stream('inventory-report.csv', ['SKU', 'Product', 'Stock', 'Status', 'Stock value'], $report['rows']->map(fn ($row) => [
            $row['sku'], $row['product_name'], $row['stock'], $row['state'], number_format($row['stock_value'], 2, '.', ''),
        ]));
    }

    public function coupons(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        return view('admin.reports.coupons', [
            'rows' => $this->reports->coupons($from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function exportCoupons(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);
        $rows = $this->reports->coupons($from, $to);

        $this->logExport('coupons', ['from' => $from->toDateString(), 'to' => $to->toDateString()]);

        return CsvExporter::stream('coupon-report.csv', ['Code', 'Used', 'Usage limit', 'Total discount'], $rows->map(fn ($row) => array_values($row)));
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function resolveRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : now()->subDays(29)->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }

    /** @param  array<string, mixed>  $filters */
    private function logExport(string $report, array $filters): void
    {
        $this->audit->log('admin.report.exported', null, ['report' => $report, 'filters' => $filters]);
    }
}
