<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class UserWhitelist extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['whitelistable_type', 'whitelistable_id', 'product_id', 'user_id', 'reason', 'status', 'startdate', 'enddate', 'tag'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }

    public function whitelistable()
    {
        return $this->morphTo(__FUNCTION__, 'whitelistable_type', 'whitelistable_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'whitelistable_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'user_whitelist_products', 'user_whitelist_id', 'product_id');
    }
}
