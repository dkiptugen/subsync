<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'nickname', 'points', 'media_event_id', 'identifier'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pointHistory()
    {
        return $this->hasMany(PointHistory::class);
    }

    public function mediaEvent()
    {
        return $this->belongsTo(MediaEvent::class);
    }
}
