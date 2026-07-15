<?php

namespace App\Models;

use App\Casts\TrimWhiteSpaceCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Organization extends Model
{
    use HasFactory;

    protected $casts = [
        'name' => TrimWhiteSpaceCast::class,
        'registration_no' => TrimWhiteSpaceCast::class,
    ];

    protected $fillable = [
        'kra_pin',
        'registration_no',
        'name',
        'address',
        'phone_number',
        'status',
        'additional_items',
        'user_id',
    ];

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function whitelist()
    {
        return $this->morphMany(UserWhitelist::class, 'whitelistable');
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class)
            ->using(AgentOrganization::class)
            ->withTimestamps();
    }
}
