<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Spatie\Activitylog\LogOptions;
    use Spatie\Activitylog\Traits\LogsActivity;

    class B2bTransaction extends Model
        {
            use HasFactory;
            use LogsActivity;
            protected $fillable = ['b2b_subscription_id','b2b_purchase_id','identifier','organization_id','product_id','rate_id','status','activator_id','activator_reason','region_id','currency','reserved_currency','amount','amount_paid','receipt','pay_channel','date_paid','user_id','created_at','updated_at'];
            public function getActivitylogOptions()
            : LogOptions
                {

                    return LogOptions::defaults();
                }


            public function subscription()
                {

                    return $this->belongsTo(B2bSubscription::class,'b2b_subscription_id','id');
                }
            public function activator()
                {
                    return $this->belongsTo(User::class,'activator_id','id');
                }
        }
