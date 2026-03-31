<?php

    namespace App\Jobs;

    use App\Events\PaymentFailed;
    use App\Events\PaymentMade;
    use App\Jobs\Kafka\FailedPaymentEventJob;
    use App\Libs\Mpesa;

    use App\Models\Subscription;
    use App\Models\Transaction;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Log;
    use App\Jobs\ExtendBundleChildSubscriptions;

    class StkQueryJob implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable
            , SerializesModels
            ;

        public mixed $response;
        public mixed $payment;
        public       $transaction;
        public       $tries      = 5;
        public       $retryAfter = 5;

        /**
         * Create a new job instance.
         */
        public function __construct($response,$transaction)
            {
                $this->response    = $response;
                $this->payment     = $transaction->payment_method;
                $this->transaction = $transaction;
            }

        /**
         * Execute the job.
         */
        public function handle(): void
            {
                //Log::info($this->payment);
                if(is_null(@$this->response->CheckoutRequestID))
                {
                            $transaction = Transaction::find($this->transaction->id);
                            $transaction->response = $this->response;
                            $transaction->save();

                            return;
                }
                try {
                    $mpesa = new Mpesa();
                    $mpesa->CheckoutRequestID = $this->response->CheckoutRequestID;
                    $mpesa->consumerkey = $this->payment->configuration['consumer_key'];
                    $mpesa->consumersecret = $this->payment->configuration['consumer_secret'];
                    $mpesa->passkey = $this->payment->configuration['pass_key'];
                    $mpesa->shortcode = $this->payment->configuration['shortcode'];
                    $result = $mpesa->checkout_query();


                    $cart_id = null;
                    if (property_exists($this->transaction, 'subscription')) {
                        $cart_id = $this->transaction->Subscription->cart_id;


                        //Log::error("\nCheckout Query" . json_encode($result));
                        if (@$result->ResultCode == '0') {

                            if (!is_null($cart_id)) {
                                $transactions = Transaction::with(['subscription', 'user'])
                                    ->whereHas('subscription', function ($query) use ($cart_id) {
                                        $query->where('cart_id', $cart_id);
                                    })->get();
                            } else {
                                $transaction = Transaction::find($this->transaction->id);
                                $transactions = collect([$transaction]);
                            }

                            foreach ($transactions as $transaction) {
                                if ($transaction->status == 0) {
                                    $transaction->status = 1;
                                    $transaction->amount_paid = $transaction->amount;
                                    $transaction->response = $result;
                                    $transaction->transaction_date = Carbon::now();
                                    $res = $transaction->save();
                                    if ($res) {
                                        $subscription = Subscription::find($transaction->subscription_id);
                                        if ($transaction->subscription->status == 0) {
                                            $subscription->status = 1;
                                            $subscription->save();
                                        }
                                    }
                                    event(new PaymentMade($transaction));
                                    deactivate_after_upgrade($transaction->identifier);
                                    //ExtendBundleChildSubscriptions::dispatch($transaction->subscription->id);
                                }
                            }

                            $this->delete();
                        } else {
                            event(new PaymentFailed($this->transaction->identifier, $result->ResultDesc));
                            try {
                                $kafka_data = ['transaction' => $this->transaction,
                                    'user' => $this->transaction->user,
                                    'subscription' => $this->transaction->subscription,
                                    'amount' => $this->transaction->total_amount,
                                    'error_message' => $result->ResultDesc,
                                    'payment_method' => 'mpesa'

                                ];
                                FailedPaymentEventJob::dispatch($kafka_data);
                            } catch (\Exception $e) {
                                Log::error("Kafka Mpesa Failed: ", [$e->getMessage()]);
                            }
                        }
                    }
                }
                catch
                    (\Exception $e)
                    {
                        event(new PaymentFailed($this->transaction->identifier, "Missing subscription record object for transaction"));
                        try {
                            $kafka_data = ['transaction' => $this->transaction,
                                'user' => $this->transaction->user,
                                'subscription' => $this->transaction->subscription,
                                'amount' => $this->transaction->total_amount,
                                'error_message' => 'Missing subscription record object for transaction',
                                'payment_method' => 'mpesa'
                            ];
                            FailedPaymentEventJob::dispatch($kafka_data);
                        } catch (\Exception $e) {
                            Log::error("Kafka Mpesa Failed: ", [$e->getMessage()]);
                        }
                        //Log::error("Mpesa Query: ", [$e->getMessage()]);
                    }
                    if ($this->attempts() < $this->tries) {
                        $this->release($this->retryAfter);
                    } else {
                        //Log::error("Job failed after maximum retries.");
                        $this->delete();
                    }
            }
    }


