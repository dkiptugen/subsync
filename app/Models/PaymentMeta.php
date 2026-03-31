<?php

namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMeta extends Model
    {
        use HasFactory;
        protected $fillable = ["id","uuid","data"];
        protected $casts = ['data'=>JsonCast::class];
    }
