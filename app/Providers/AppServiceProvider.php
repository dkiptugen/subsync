<?php

namespace App\Providers;

use App\Models\User;
use App\Support\PermissionHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
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
