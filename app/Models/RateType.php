<?php

namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RateType extends Model
    {
        use HasFactory;

        protected $fillable = ['name','period','dow'];
        protected $casts = ['dow' =>JsonCast::class];
        use LogsActivity;
        public function getActivitylogOptions(): LogOptions
            {
                return LogOptions::defaults()->logOnly($this->fillable);
            }

        public function rate()
            {
               return $this->hasMany(Rate::class) ;
        }
    }
