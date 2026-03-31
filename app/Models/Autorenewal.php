<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autorenewal extends Model
{
    use HasFactory;
    protected $fillable = ['start_date','expiry_date','next_renewal_date'];
        public function subscribable()
            {
                return $this->morphTo(__FUNCTION__, 'subscribable_type', 'subscribable_id');
            }
}
