<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserMeta extends Model
    {
        use HasFactory;
        public $timestamps = false;
        protected $fillable = ['user_id','action','result','date_created'];
        use LogsActivity;
        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults()
                                 ->logOnly($this->fillable);
            }
        public function user()
            {
                return $this->belongsToMany(User::class);
            }
	
	/**
	 * @return \Illuminate\Database\Eloquent\Casts\Attribute
	 */
		public function dateCreated () :Attribute
			{
				return Attribute::make( set: fn(string $value) => Carbon::now()->format('Y-m-d'));
			}
    }
