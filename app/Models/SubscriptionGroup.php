<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SubscriptionGroup extends Model
{
    use HasFactory;

    protected $fillable = ['identifier', 'subdate'];

    public $timestamps = false;

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
