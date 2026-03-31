<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels
        ;

    public $transaction;
    /**
     * Create a new job instance.
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
            $trans = $this->transaction->load(['subscription.product.site']);
            $product = $trans->subscription->product;
            $site = @$trans->subscription->product->site->callback_url;
            $payload = [
                'identifier' => $trans->identifier,
                'product_identifier' => $product->identifier,
                'product_name' => $product->product_name,
                'receipt' => $trans->receipt,
                'amount' => $trans->amount_paid,
                'phone' => $trans->phone,
                'transaction_date' => $trans->updated_at->format('Y-m-d H:i:s'),
            ];
            if($site){
                $response = Http::withHeaders([
                    'Signature' => hash_hmac('sha256', "h22", 'wehbook'),
                ])->post($site, $payload);
            }
        }catch (\Exception $exception){
            report($exception);
        }
    }
}
