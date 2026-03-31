<?php

	namespace App\Models;

	use App\Casts\JsonCast;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Spatie\Activitylog\LogOptions;
	use Spatie\Activitylog\Traits\LogsActivity;

	class Transaction extends Model
		{
			use HasFactory;
			use LogsActivity;

			protected $fillable
				= [
					'id', 'identifier', 'subscription_id', 'payment_method_id', 'cart_id', 'channel', 'receipt',
					'initiator', 'coupon_code', 'amount', 'currency', 'status', 'user_id', 'transaction_date',
					'amount_paid', 'type', 'response', 'reserved_currency', 'reserve_currency_amount', 'created_at',
					'updated_at','transaction_code','transaction_token','result'
				];

			protected $casts = ['subscription_ids' => JsonCast::class,'response'=>JsonCast::class];

			public function getActivitylogOptions ()
			: LogOptions
				{
                    return  LogOptions::defaults()->logAll();
					//return LogOptions::defaults ()->logOnly ($this->fillable);
				}

			public function user ()
				{
					return $this->belongsTo (User::class);
				}

			public function product ()
				{
					return $this->belongsTo (Product::class);
				}

			public function rate ()
				{
					return $this->belongsTo (Rate::class);
				}

			public function payment_method ()
				{
					return $this->belongsTo (PaymentMethod::class);
				}

			public function subscription ()
				{
					return $this->belongsTo (Subscription::class);
				}
		/* public function SubscriptionIds() : Attribute
			 {
				 return Attribute::make(
					 get: fn ($value, $attributes) => Subscription::whereIn('id',json_decode($value))->get()
				 )->shouldCache();

			 }
		 public function getAttributeSubscription() : \Illuminate\Support\Collection
			 {
				 $allSubscription = [];
				 foreach ($this->attributes['subscription_ids'] as $category)
					 {
						 $allSubscription[] = Subscription::find($category);
					 }
				 return collect($allSubscription);
			 }*/
		}
