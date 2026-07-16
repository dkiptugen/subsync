<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\DashboardRealtimeObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $observers = [
        Transaction::class => [DashboardRealtimeObserver::class],
        Subscription::class => [DashboardRealtimeObserver::class],
        Product::class => [DashboardRealtimeObserver::class],
        User::class => [DashboardRealtimeObserver::class],
        Organization::class => [DashboardRealtimeObserver::class],
        Lead::class => [DashboardRealtimeObserver::class],
    ];

    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        'Illuminate\Auth\Events\Registered' => [
            'App\Listeners\LogRegisteredUser',
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
