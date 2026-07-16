<?php

use App\Http\Middleware\CheckAppKey;
use App\Http\Middleware\CustomCors;
use App\Http\Middleware\EnsureBusinessUser;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\EnsureOwner;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\PasswordExpired;
use App\Http\Middleware\RedirectIfNotInstalled;
use App\Http\Middleware\TrackUserFlow;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web     : __DIR__.'/../routes/web.php',
        api     : __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health  : '/up',
        then    : function () {
            Route::middleware('web')->group(base_path('routes/b2b.php'));
            Route::middleware('web')->group(base_path('routes/front.php'));
            Route::group([], base_path('routes/installer.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', 'throttle:api');

        $middleware->redirectGuestsTo(fn (): string => rtrim(config('app.url'), '/').'/login');
        $middleware->redirectUsersTo(fn (): string => rtrim(config('app.url'), '/').'/manage');

        $middleware->alias([
            'password.expired' => PasswordExpired::class,
            'cors' => CustomCors::class,
            'app.key' => CheckAppKey::class,
            'passkey' => CheckAppKey::class,
            'force_json' => ForceJsonResponse::class,
            'installed' => RedirectIfNotInstalled::class,
            'not_installed' => EnsureNotInstalled::class,
            'is_owner' => EnsureOwner::class,
            'is_business' => EnsureBusinessUser::class,
            'track.flow' => TrackUserFlow::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $exception): bool => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
