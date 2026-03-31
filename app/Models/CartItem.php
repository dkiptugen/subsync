<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CartItem extends Model
    {
        use HasFactory;

        protected $fillable = ['id', 'cart_id', 'rate_id', 'rate_type', 'product', 'cost', 'currency', 'thumbnail','release_id','release_date', 'created_at', 'updated_at'];
        use LogsActivity;

        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults();
            }

        public function cart()
            {
                return $this->belongsTo(Cart::class);
            }

        public function rate()
            {
                return $this->belongsTo(Rate::class);
            }
    }
