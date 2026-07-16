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

    class DPOEventJob implements ShouldQueue
    {
        use Dispatchable, HasReliableHttpDelivery, InteractsWithQueue, Queueable, SerializesModels
            ;

        public $user;
        public $subscription;
        public $transaction;
        public $amount;
        public $transaction_token;
        public $company_token;
        public $iframe;

        /**
         * Create a new job instance.
         */
        public function __construct(array $data)
            {
                $this->user              = $data['user'];
                $this->subscription      = $data['subscription'];
                $this->transaction       = $data['transaction'];
                $this->amount            = $data['amount'];
                $this->transaction_token = $data['transaction_token'];
                $this->company_token     = $data['company_token'];
                $this->iframe            = $data['iframe'];
                //$this->transaction->withoutRelations();
                //$this->subscription->withoutRelations();
            }

        /**
         * Execute the job.
         */
        public function handle(): void
            {
                try
                    {
                        Log::info('Kafka Job: DPO Event Job');

                        $kafka_data = [
                            "kafka_topic"   => "subscription_system_events_json",
                            "eventId"       => Str::uuid()->toString(),
                            "eventName"     => "dpo_initialize",
                            "eventTime"     => now()->toIso8601String(),
                            "eventMetadata" => [
                                "page"        => "dpo Initialize",
                                "user"        => [
                                    "userId"    => optional($this->user)->id,
                                    "userName"  => optional($this->user)->name,
                                    "userEmail" => optional($this->user)->email,
                                ],
                                "product"     => [
                                    "productName" => optional(optional($this->subscription)->product)->product_name
                                ],
                                "transaction" => [
                                    "transcode" => optional($this->transaction)->identifier,
                                    "amount"    => $this->amount
                                ],
                                "response"    => [
                                    "TransactionToken" => $this->transaction_token,
                                    "CompanyToken"     => $this->company_token,
                                    'iframe_link'      => $this->iframe
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
                        Log::error("Kafka DPO Notification Job", [$e->getMessage()]);
                        throw $e;
                    }
            }
    }
