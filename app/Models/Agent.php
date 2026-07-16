<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Agent extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['email', 'name', 'phone', 'type', 'department', 'country'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->using(AgentOrganization::class)
            ->withTimestamps();
    }
}
