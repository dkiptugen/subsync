<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class B2bSubscription extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['organization_id', 'b2b_purchase_id', 'product_id', 'start_date', 'expiry_date', 'accounts', 'records', 'status', 'receipt', 'title', 'rate_type_id', 'amount', 'amount_paid', 'subscription_type', 'activator_reason'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    public function purchase()
    {
        return $this->belongsTo(B2bPurchase::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function users()
    {
        return $this->hasMany(B2bSubscriptionUser::class);
    }

    public function activator()
    {
        return $this->belongsTo(User::class, 'activator_id');
    }

    public function transaction()
    {
        return $this->hasOne(B2bTransaction::class, 'transaction_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'b2b_subscription_products', 'b2b_subscription_id', 'product_id');
    }
}
