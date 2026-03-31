<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SubscriptionGroup extends Model
    {
        use HasFactory;
        protected $fillable = ['identifier','subdate'];
        public $timestamps = False;
        use LogsActivity;


        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults()->logOnly($this->fillable);
            }

        public function subscriptions()
            {
                return $this->hasMany(Subscription::class);
            }

    }
