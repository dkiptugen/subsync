<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Rate extends Model
    {
        use HasFactory;

        protected $fillable = ['id', 'product_id', 'name', 'period', 'rate_type_id', 'cost', 'currency', 'reserve_currency', 'reserve_currency_cost', 'region_id', 'status', 'description', 'user_id', 'type', 'organization_id', 'start_date', 'enddate','swahili_name','category','listorder','editions'];
        use LogsActivity;

        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults()->logOnly($this->fillable);
            }

        public function product()
            {
                return $this->belongsTo(Product::class);
            }

        public function user()
            {
                return $this->belongsTo(User::class);
            }

        public function organization()
            {
                return $this->belongsTo(Organization::class)->withDefault(['name' => 'Default']);
            }

        public function rate_type()
            {
                return $this->belongsTo(RateType::class);
            }
    }
