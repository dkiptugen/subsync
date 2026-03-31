<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\TrimWhiteSpaceCast;
use Caydeesoft\Permission\Models\Permission;
use Caydeesoft\Permission\Models\PermissionRole;
use Caydeesoft\Permission\Models\Role;
use Caydeesoft\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Admin extends Authenticatable
    {
        use HasApiTokens;
        use HasFactory;
        use HasPushSubscriptions;
        use HasRoles;
        use Notifiable;
        use LogsActivity;

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
                'verification_token'
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
                'role_id'
            ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
        protected $casts
            = [
                'email_verified_at' => 'datetime',
                'email'             => TrimWhiteSpaceCast::class,
                'name'              => TrimWhiteSpaceCast::class,
                'surname'           => TrimWhiteSpaceCast::class
            ];


        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults()
                                 ->logOnly($this->fillable);
            }

        public function role()
            {

                return $this->belongsTo(Role::class)->withDefault(['id' => 0, 'name' => 'None']);

            }

        public function organization()
            {
                return $this->belongsTo(Organization::class)->withDefault(['id' => 0, 'name' => 'None']);
            }


        public function permission()
            {
                return $this->hasManyThrough(Permission::class, PermissionRole::class, 'role_id', 'id', 'role_id', 'permission_id');
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
