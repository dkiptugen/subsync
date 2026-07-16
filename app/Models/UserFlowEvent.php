<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFlowEvent extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['user_id', 'product_id', 'session_id', 'event', 'path', 'method', 'route_name', 'metadata', 'ip_address', 'user_agent', 'occurred_at'];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
