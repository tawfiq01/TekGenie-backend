<?php

namespace App\Services\Audit;

use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Records staff actions (FR-AUD-001/002). Entries are append-only — nothing
 * here updates or deletes an existing row (FR-AUD-003 is enforced simply by
 * not exposing edit/delete routes for audit_logs).
 */
class AuditLogger
{
    /** @param  array<string, mixed>|null  $changes */
    public function log(string $action, ?Model $subject = null, ?array $changes = null): void
    {
        $this->record(Auth::guard('admin')->user(), $action, $subject, $changes);
    }

    public function logFailedLogin(string $attemptedIdentifier): void
    {
        $this->record(null, 'admin.login.failed', null, ['identifier' => $attemptedIdentifier]);
    }

    /** @param  array<string, mixed>|null  $changes */
    private function record(?Admin $actor, string $action, ?Model $subject, ?array $changes): void
    {
        AuditLog::create([
            'actor_type' => 'admin',
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'changes' => $changes,
            'ip_address' => RequestFacade::ip(),
            'user_agent' => substr((string) RequestFacade::userAgent(), 0, 255),
        ]);
    }
}
