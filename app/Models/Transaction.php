<?php

	namespace App\Models;

	use App\Casts\JsonCast;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Spatie\Activitylog\Models\Concerns\LogsActivity;
    use Spatie\Activitylog\Support\LogOptions;

    class Transaction extends Model
		{
			use HasFactory;
			use LogsActivity;

			protected $fillable
				= [
					'identifier', 'subscription_id', 'payment_method_id', 'cart_id', 'channel', 'receipt',
					'initiator', 'coupon_code', 'amount', 'currency', 'status', 'user_id', 'transaction_date',
					'amount_paid', 'type', 'response', 'reserved_currency', 'reserved_currency_amount',
					'transaction_code','transaction_token','result'
				];

			protected $casts = [
                'subscription_ids' => JsonCast::class,
                'response' => JsonCast::class,
                'transaction_date' => 'datetime',
                'amount' => 'decimal:2',
                'amount_paid' => 'decimal:2',
                'reserved_currency_amount' => 'decimal:2',
                'status' => 'integer',
            ];

			public function getActivitylogOptions ()
			: LogOptions
				{
                    return  LogOptions::defaults()->logAll();
					//return LogOptions::defaults ()->logOnly ($this->fillable);
				}

			public function user (): BelongsTo
				{
					return $this->belongsTo (User::class);
				}

			public function product (): BelongsTo
				{
					return $this->belongsTo (Product::class);
				}

			public function rate (): BelongsTo
				{
					return $this->belongsTo (Rate::class);
				}

			public function payment_method (): BelongsTo
				{
					return $this->belongsTo (PaymentMethod::class);
				}

			public function subscription (): BelongsTo
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
