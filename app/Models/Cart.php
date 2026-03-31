<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cart extends Model
    {
        use HasFactory;
        protected $fillable = ['status','user_id','organization_id','amount','currency'];
        use LogsActivity;
        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults();
            }
        public function items()
            {
                return $this->hasMany(CartItem::class, 'cart_id');
            }

        public function subscriptions()
        {
            return $this->hasMany(Subscription::class, 'cart_id');
        }
    }
