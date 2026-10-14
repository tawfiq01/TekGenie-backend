<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out an idle staff session (FR-ADM-001, NFR-SEC-12).
 */
class EnsureAdminSessionIsFresh
{
    public function handle(Request $request, Closure $next): Response
    {
        $lastActivity = $request->session()->get('admin_last_activity');
        $timeoutMinutes = (int) config('auth.admin_idle_timeout_minutes', 120);

        if ($lastActivity !== null && now()->timestamp - $lastActivity > $timeoutMinutes * 60) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('status', 'Your session expired due to inactivity. Please log in again.');
        }

        $request->session()->put('admin_last_activity', now()->timestamp);

        return $next($request);
    }
}
