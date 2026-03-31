<?php

namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentMethod extends Model
    {
        use HasFactory;
        protected $casts = ['configuration' => JsonCast::class,
            'notification_endpoints' => JsonCast::class];
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
