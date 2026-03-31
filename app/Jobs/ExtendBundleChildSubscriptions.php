<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Rate;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtendBundleChildSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable
        //, SerializesModels
        ;

    public $subscription_id;
    /**
     * Create a new job instance.
     */
    public function __construct(int $subscription_id)
    {
        $this->subscription_id = $subscription_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $subscription = Subscription::with([
            'rate',
            'products'
        ])->whereHas('products', function ($query) {

        })->find($this->subscription_id);

        if(!$subscription) {
            return;
        }

        if(!property_exists($subscription, 'products')) {
            return;
        }

        $products = @$subscription->products;
        $rate = @$subscription->rate;

        if ($products->isNotEmpty()) {
            $ids = $products->pluck('id')->toArray();
            $subscriptions = Subscription::whereIn('product_id', $ids)
                ->where('user_id', $subscription->user_id)
                ->whereDate('expiry_date', '>', Carbon::now()->addDays(1)->toDateString())
                ->get();

            foreach ($subscriptions as $sub) {
                $sub->expiry_date = Carbon::parse($sub->expiry_date)->addDays($rate->period)->toDateString();
                $sub->save();
            }
        }
    }
}
