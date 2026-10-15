<?php

use App\Exceptions\SlugMovedException;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureAdminSessionIsFresh;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(__DIR__.'/../routes/admin.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'admin.idle' => EnsureAdminSessionIsFresh::class,
            'maintenance' => CheckMaintenanceMode::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('login'));

        // FR-ACC-010: AuthenticateSession only acts on the default ('web')
        // guard's user, so this is inert for admin-guard sessions even
        // though routes/admin.php is wrapped in the 'web' group too.
        $middleware->authenticateSessions();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // FR-SET-005: a slug that moved 301s to its current URL instead of 404ing.
        $exceptions->render(fn (SlugMovedException $e) => redirect()->route($e->routeName, $e->entity, 301));
    })->create();
