<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\TrimWhiteSpaceCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasPushSubscriptions;
    use HasRoles;
    use LogsActivity;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable
        = [
            'name',
            'username',
            'email',
            'password',
            'type',
            'status',
            'organization_id',
            'last_login',
            'password_changed_at',
            'is_verified',
            'verification_count',
            'verification_token',
        ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden
        = [
            'password',
            'type',
            'status',
            'password_changed_at',
        ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts
        = [
            'email_verified_at' => 'datetime',
            'email' => TrimWhiteSpaceCast::class,
            'name' => TrimWhiteSpaceCast::class,
            'surname' => TrimWhiteSpaceCast::class,
        ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class)->withDefault(['id' => 0, 'name' => 'None']);
    }

    public function meta()
    {
        return $this->hasMany(UserMeta::class);
    }

    public function subscription()
    {
        return $this->hasMany(Subscription::class);
    }

    public function whitelist()
    {
        return $this->morphMany(UserWhitelist::class, 'whitelistable');
    }

    public function providers()
    {
        return $this->hasMany(Provider::class, 'user_id', 'id');
    }
}
