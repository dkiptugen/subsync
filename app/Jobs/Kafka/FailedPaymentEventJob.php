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

    class FailedPaymentEventJob implements ShouldQueue
    {
        use Dispatchable, HasReliableHttpDelivery, InteractsWithQueue, Queueable
            //, SerializesModels
            ;
        public $transaction;
        public $user;
        public $subscription;
        public $amount;
        public $error_message;
        public $payment_method;
        /**
         * Create a new job instance.
         */
        public function __construct($data)
            {
                $this->transaction    = $data['transaction'];
                $this->user           = $data['user'];
                $this->subscription   = $data['subscription'];
                $this->amount         = $data['amount'];
                $this->error_message  = $data['error_message'];
                $this->payment_method = $data['payment_method'];
                $this->transaction->withoutRelations();
                $this->subscription->withoutRelations();
            }

        /**
         * Execute the job.
         */
        public function handle(): void
            {
                try
                    {

                        Log::info('Kafka Job: FailedPaymentEventJob');
                        $kafka_data = [
                            "kafka_topic"   => "subscription_system_events_json",
                            "eventId"       => Str::uuid()->toString(),
                            "eventName"     => "failed_payment",
                            "eventTime"     => now()->toIso8601String(),
                            "eventMetadata" => [
                                "page"        => "Failed Payment",
                                "user"        => [
                                    "userId"    => optional($this->user)->id??'',
                                    "userName"        => optional($this->user)->name??'',
                                    "userEmail" => optional($this->user)->email??''

                                ],
                                "product"     => [
                                    "productName" => $this->subscription->product->product_name
                                ],
                                "transaction" => [
                                    "transcode"      => $this->transaction->identifier,
                                    "amount"         => $this->amount,
                                    "reason"         => $this->error_message,
                                    "payment_method" => $this->payment_method
                                ]
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
                        Log::error('Kafka Failed Payment', [$e->getMessage()]);
                        throw $e;
                    }
            }
    }
