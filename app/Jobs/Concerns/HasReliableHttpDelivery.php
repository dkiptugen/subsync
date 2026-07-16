<?php

namespace App\Jobs\Concerns;

use Illuminate\Support\Facades\Log;
use Throwable;

trait HasReliableHttpDelivery
{
    public int $tries = 3;

    public int $timeout = 30;

    public array $backoff = [1, 5, 10];

    public function failed(?Throwable $exception): void
    {
        Log::error('Queued HTTP delivery failed.', [
            'job' => static::class,
            'error' => $exception?->getMessage(),
        ]);
    }
}
