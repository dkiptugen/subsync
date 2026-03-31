<?php

namespace App\Jobs;

use App\Models\Cart;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdatedPhoneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable
        //, SerializesModels
        ;

    public $reference;
    public $phone;
    /**
     * Create a new job instance.
     */
    public function __construct($reference,$msisdn)
    {
        $this->reference = $reference;
        $this->phone = $msisdn;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cart = Cart::where('identifier', $this->reference)->whereNotNull('identifier')->first();

        if($cart){

            $transactions = Transaction::whereHas('subscription', function ($query) use ($cart)
            {
                $query->where('cart_id', $cart->id);
            })->where('channel', 'like', '%mpesa%')->get();

            foreach ($transactions as $transaction) {
                if(is_null($transaction->phone)){
                    $transaction->phone = $this->phone;
                    $transaction->source = 'validation';
                    $transaction->save();
                }
            }
        }
        else{
            $transaction = Transaction::where('identifier', $this->reference)->first();
            if($transaction && is_null($transaction->phone))
            {
                $transaction->phone = $this->phone;
                $transaction->source = 'validation';
                $transaction->save();
            }
        }
    }
}
