<?php

namespace App\Models;

use App\Casts\JsonCast;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


class PaymentMethod extends Model
    {
        protected $casts = [
            'configuration' => JsonCast::class,
            'notification_endpoints' => JsonCast::class,
            'status' => StatusEnum::class,
        ];
        use LogsActivity;
        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults();
            }
        public function user()
            {
                return $this->belongsTo(User::class);
            }

    }
