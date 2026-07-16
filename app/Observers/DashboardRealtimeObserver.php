<?php

namespace App\Observers;

use App\Services\DashboardRealtimeService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class DashboardRealtimeObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly DashboardRealtimeService $realtime) {}

    public function created(Model $model): void
    {
        $this->realtime->publish($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->realtime->publish($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->realtime->publish($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->realtime->publish($model, 'restored');
    }
}
