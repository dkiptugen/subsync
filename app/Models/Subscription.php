<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Subscription extends Model
{
    use HasFactory;
    use LogsActivity;

    // protected $casts = ['subscription_date'=>\DateTime::class]
    protected $guarded = [];

    // protected $fillable = ['identifier', 'product_id', 'subscription_group_id', 'subscription_date', 'reccurent_cycle', 'cart_id','rate_id', 'reccuring', 'expiry_date', 'status', 'user_id','activator_reason', 'created_at', 'updated_at'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    public function metadata()
    {
        return $this->morphMany(Autorenewal::class, 'subscribable');
    }

    /* public function transaction() : Attribute
         {
             return Attribute::make(
                 get: fn ($value, $attributes) =>  Transaction::whereJsonContains('subscription_ids',$this->id)->get()
             )->shouldCache();

         }*/
    public function activator()
    {
        return $this->belongsTo(User::class, 'activator_id')->default(['id' => 0, 'name' => 'None']);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'subscription_products', 'subscription_id', 'product_id');
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);

    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
