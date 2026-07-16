<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
    {
        use HasFactory;
        protected $guarded = ['*'];
        public $table           =   'activity_log';
        public function user()
            {
                return $this->belongsTo(User::class,'causer_id')->withDefault(['name'=>'System']);
            }
    }
