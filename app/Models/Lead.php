<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;


class Lead extends Model
    {
        use HasFactory;


        public function product()
            {
               return $this->belongsTo(Product::class) ;
            }
    }
