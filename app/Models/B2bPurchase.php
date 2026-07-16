<?php

namespace App\Models;

use App\Casts\JsonCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class B2bPurchase extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $guarded = ['*'];

    protected $casts = ['products' => JsonCast::class];

    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cc_approver()
    {
        return $this->belongsTo(User::class, 'cc_approver_id');
    }

    public function finance_approver()
    {
        return $this->belongsTo(User::class, 'finance_approver_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function details()
    {
        return $this->hasMany(B2bPurchaseDetail::class);
    }
}
