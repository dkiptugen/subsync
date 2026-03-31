<?php

namespace App\Providers;

use App\Macros\BuilderMacros;
use App\Macros\CacheMacros;
use App\Macros\CollectionMacros;
use App\Macros\ResponseMacros;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
    {
    /**
     * Register services.
     */
        public function register(): void
            {
                //
            }

    /**
     * Bootstrap services.
     */
        public function boot(): void
            {
                BuilderMacros::register();
                CacheMacros::register();
                CollectionMacros::register();
                ResponseMacros::register();
            }
    }
