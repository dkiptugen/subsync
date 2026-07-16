<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


class Rate extends Model
    {
        use HasFactory;

        protected $fillable = ['product_id', 'name', 'period', 'rate_type_id', 'cost', 'currency', 'reserve_currency', 'reserve_currency_cost', 'region_id', 'status', 'description', 'user_id', 'type', 'organization_id', 'start_date', 'enddate','swahili_name','category','listorder','editions'];
        use LogsActivity;

        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults()->logOnly($this->fillable);
            }

        public function product(): BelongsTo
            {
                return $this->belongsTo(Product::class);
            }

        public function user(): BelongsTo
            {
                return $this->belongsTo(User::class);
            }

        public function organization(): BelongsTo
            {
                return $this->belongsTo(Organization::class)->withDefault(['name' => 'Default']);
            }

        public function rate_type(): BelongsTo
            {
                return $this->belongsTo(RateType::class);
            }
    }
