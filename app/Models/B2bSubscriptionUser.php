<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class B2bSubscriptionUser extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['b2b_subscription_id', 'user_id'];

    public function getActivitylogOptions(): LogOptions
    {

        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    public function subscription()
    {

        return $this->belongsTo(B2bSubscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
