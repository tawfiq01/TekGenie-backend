<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reporting\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** FR-RPT-001. */
class DashboardController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): View
    {
        $period = in_array($request->string('period')->toString(), ['today', '7', '30'], true) ? $request->string('period')->toString() : '7';

        $from = match ($period) {
            'today' => now()->startOfDay(),
            '30' => now()->subDays(29)->startOfDay(),
            default => now()->subDays(6)->startOfDay(),
        };

        return view('admin.dashboard', [
            'period' => $period,
            'summary' => $this->reports->dashboard($from, now()->endOfDay()),
            'failedJobsCount' => DB::table('failed_jobs')->count(),
        ]);
    }
}
