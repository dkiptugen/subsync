<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class MediaEvent extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['name', 'identifier', 'status'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
