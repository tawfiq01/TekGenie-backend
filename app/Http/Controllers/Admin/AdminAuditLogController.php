<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only, searchable audit trail (FR-AUD-003). No edit/delete routes
 * exist for this resource — that is how FR-AUD-003's "append-only" rule
 * is enforced.
 */
class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $entries = AuditLog::query()
            ->when($request->filled('actor_id'), fn ($q) => $q->where('actor_id', $request->integer('actor_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%'.$request->string('action').'%'))
            ->when($request->filled('subject_type'), fn ($q) => $q->where('subject_type', 'like', '%'.$request->string('subject_type').'%'))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-log.index', ['entries' => $entries]);
    }
}
