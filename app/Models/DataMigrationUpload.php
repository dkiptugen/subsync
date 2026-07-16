<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataMigrationUpload extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['type', 'user_id', 'disk', 'path', 'original_name', 'status', 'progress', 'processed_files', 'total_files', 'message', 'error', 'started_at', 'completed_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
