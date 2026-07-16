<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class B2bPurchaseDetail extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $guarded = ['*'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
