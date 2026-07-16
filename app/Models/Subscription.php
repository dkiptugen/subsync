<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Subscription extends Model
{
    use HasFactory;
    use LogsActivity;

    // protected $casts = ['subscription_date'=>\DateTime::class]
    protected $fillable = [
        'identifier', 'product_id', 'subscription_group_id', 'subscription_date', 'reccurent_cycle',
        'rate_id', 'reccuring', 'expiry_date', 'status', 'user_id', 'cart_id', 'subscription_token',
        'unsubscription_date', 'reason_id', 'activator_id', 'activator_reason', 'finance_approver_id',
        'finance_approved_at', 'finance_approval_status', 'reconcile_date', 'days_added', 'category',
        'article_id', 'type',
    ];

    protected $casts = [
        'subscription_date' => 'datetime',
        'expiry_date' => 'datetime',
        'unsubscription_date' => 'date',
        'finance_approved_at' => 'datetime',
        'reconcile_date' => 'datetime',
        'reccuring' => 'boolean',
        'status' => 'integer',
        'finance_approval_status' => 'integer',
        'days_added' => 'integer',
    ];

    // protected $fillable = ['identifier', 'product_id', 'subscription_group_id', 'subscription_date', 'reccurent_cycle', 'cart_id','rate_id', 'reccuring', 'expiry_date', 'status', 'user_id','activator_reason', 'created_at', 'updated_at'];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }

    public function payment_method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transaction(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function metadata(): MorphMany
    {
        return $this->morphMany(Autorenewal::class, 'subscribable');
    }

    /* public function transaction() : Attribute
         {
             return Attribute::make(
                 get: fn ($value, $attributes) =>  Transaction::whereJsonContains('subscription_ids',$this->id)->get()
             )->shouldCache();

         }*/
    public function activator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activator_id')->withDefault(['id' => 0, 'name' => 'None']);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'subscription_products', 'subscription_id', 'product_id');
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);

    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
