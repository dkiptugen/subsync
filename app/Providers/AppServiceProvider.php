<?php

namespace App\Providers;

use App\Models\User;
use App\Support\PermissionHelper;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->string('email')->lower().'|'.$request->ip()));

        Gate::before(function (?User $user, string $ability) {
            if ($user?->hasRole('Super Admin')) {
                return true;
            }

            return null;
        });

        Blade::if('canaccess', function (string $permission): bool {
            return PermissionHelper::canAccess($permission);
        });
    }
}
