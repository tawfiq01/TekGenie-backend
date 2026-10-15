<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FR-SET-007: storefront-only maintenance mode with an allow-list secret.
 * Admin routes never pass through this middleware (see routes/web.php), so
 * staff always retain access to turn it back off. A Setting-backed flag is
 * used instead of Laravel's own down()/up() console state so it stays
 * testable and consistent with every other switch in this app.
 */
class CheckMaintenanceMode
{
    private const BYPASS_COOKIE = 'maintenance_bypass';

    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) Setting::get('seo', 'maintenance_mode_enabled', false)) {
            return $next($request);
        }

        $secret = trim((string) Setting::get('seo', 'maintenance_secret', ''));

        if ($secret !== '' && $request->query('secret') === $secret) {
            return redirect($request->path())->withCookie(
                cookie(self::BYPASS_COOKIE, $secret, 60 * 24 * 30, httpOnly: true, sameSite: 'lax')
            );
        }

        if ($secret !== '' && $request->cookie(self::BYPASS_COOKIE) === $secret) {
            return $next($request);
        }

        abort(503);
    }
}
