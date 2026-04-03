<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserMeta extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'result', 'source', 'ip_address', 'date_created'];

    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    public function dateCreated(): Attribute
    {
        return Attribute::make(set: fn (string $value) => Carbon::now()->format('Y-m-d'));
    }
}
