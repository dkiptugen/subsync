<?php

namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


class RateType extends Model
    {


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
