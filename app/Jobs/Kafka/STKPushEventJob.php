<?php

    namespace App\Jobs\Kafka;

    use App\Jobs\Concerns\HasReliableHttpDelivery;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    class STKPushEventJob implements ShouldQueue
    {
        use Dispatchable, HasReliableHttpDelivery, InteractsWithQueue, Queueable, SerializesModels
            ;
        public $transaction;
        public $account;
        public $shortcode;
        public $amount;
        public $msisdn;
        public $response;
        /**
         * Create a new job instance.
         */
        public function __construct($data)
            {
                $this->transaction = $data['transaction'];
                $this->account     = $data['account'];
                $this->shortcode   = $data['shortcode'];
                $this->amount      = $data['amount'];
                $this->msisdn      = $data['msisdn'];
                $this->response    = $data['response'];
                $this->transaction->withoutRelations();
            }

        /**
         * Execute the job.
         */
        public function handle(): void
            {
                try
                    {
                        Log::info('Kafka Job: STK Push Event Job');

                        $kafka_data = [
                            "kafka_topic"   => "subscription_system_events_json",
                            "eventId"       => Str::uuid()->toString(), // Use Laravel's Str::uuid() helper to generate a UUID for eventId
                            "eventName"     => "stkpush", // Specify the eventName at runtime, e.g., "successful_payment" or "customer_subscription"
                            "eventTime"     => now()->toIso8601String(),
                            "eventMetadata" => [
                                "page"        => "stkpush",
                                "user"        => [
                                    "userId"    => $this->transaction->user_id,
                                    "userName"        => $this->transaction->user->name,
                                    "userEmail" => $this->transaction->user->email,
                                ],
                                "product"     => [
                                    "productName" => $this->transaction->subscription->product->product_name
                                ],
                                "transaction" => [
                                    "transcode" => $this->account,
                                    "amount"    => $this->amount,
                                    "shortcode" => $this->shortcode,
                                    "phone_no"  => $this->msisdn
                                ],
                                "response"    => $this->response
                            ]
                        ];
                        Http::withHeaders([
                                              'Content-Type' => 'application/json',
                                          ])
                            ->connectTimeout(3)->timeout(10)->retry([100, 500, 1000])
                            ->post(config('kafka.kafka_domain').'/api/v1/subscriptions', $kafka_data)->throw();
                    }
                catch (\Exception $e)
                    {
                        Log::error('KAFKA Mpesa stk', [$e->getMessage()]);
                        throw $e;
                    }
            }
    }
