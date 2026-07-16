<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\TrimWhiteSpaceCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasPushSubscriptions;
    use HasRoles;
    use Notifiable;

    /**
     * @var string[]
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable
        = [
            'name',
            'surname',
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
            'allow_marketing',
            'can_notify',
            'phone',
            'daily_notifications',
            'email_verified_at',
            'remember_token',
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
            'last_login' => 'datetime',
            'password_changed_at' => 'datetime',
            'status' => 'integer',
            'is_verified' => 'integer',
            'verification_count' => 'integer',
            'can_notify' => 'boolean',
            'allow_marketing' => 'boolean',
            'daily_notifications' => 'boolean',
        ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class)->withDefault(['id' => 0, 'name' => 'None']);
    }

    public function meta(): HasMany
    {
        return $this->hasMany(UserMeta::class);
    }

    public function subscription(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function whitelist(): MorphMany
    {
        return $this->morphMany(UserWhitelist::class, 'whitelistable');
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class, 'user_id', 'id');
    }
}
