<?php

use App\Http\Middleware\CheckAppKey;
use App\Http\Middleware\CustomCors;
use App\Http\Middleware\PasswordExpired;
use App\Http\Middleware\TrackUserFlow;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web     : __DIR__.'/../routes/web.php',
        api     : __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health  : '/up',
        then    : function () {
            Route::middleware('b2b')->prefix('b2b')->group(base_path('routes/b2b.php'));
            Route::middleware('web')->group(base_path('routes/front.php'));
            Route::prefix('install')->group(base_path('routes/installer.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (): string => rtrim(config('app.url'), '/').'/login');
        $middleware->redirectUsersTo(fn (): string => rtrim(config('app.url'), '/').'/manage');

        $middleware->alias([
            'password.expired' => PasswordExpired::class,
            'cors' => CustomCors::class,
            'app.key' => CheckAppKey::class,
            'track.flow' => TrackUserFlow::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
