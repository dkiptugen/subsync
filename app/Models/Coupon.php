<?php

namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Coupon extends Model
    {
        use HasFactory;

        protected $casts = ['products' => JsonCast::class];
        protected $guarded = [];
        use LogsActivity;

        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults();
            }

        public function Products(): Attribute
            {
                return Attribute::make(
                    get: fn($value, $attributes) => Product::whereIn('id', json_decode($value))->get()
                )->shouldCache();

            }

        public function region()
            {
                return $this->belongsTo(Region::class)->withDefault(['id' => 0, 'name' => 'universal']);
            }

        public function ratetype()
            {
                return $this->belongsTo(RateType::class, 'rate_type')->withDefault(['id' => 0, 'name' => 'universal']);
            }

        public function rateTypes()
        {
            return $this->belongsToMany(RateType::class, 'coupon_rate_types', 'coupon_id', 'rate_type_id');
        }
        public function agent()
        {
            return $this->belongsTo(Agent::class);
        }
    }
