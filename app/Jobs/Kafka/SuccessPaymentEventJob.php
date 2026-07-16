<?php

    namespace App\Jobs\Kafka;

    use App\Jobs\Concerns\HasReliableHttpDelivery;
    use App\Models\Coupon;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    class SuccessPaymentEventJob implements ShouldQueue
        {
        use Dispatchable, HasReliableHttpDelivery, InteractsWithQueue, Queueable, SerializesModels
                ;

            public $user;
            public $transaction;
            public $payment_method;
            public $subscription;

        /**
         * Create a new job instance.
         */
            public function __construct(array $data)
                {
                    $this->user           = $data['user'];
                    $this->transaction    = $data['transaction'];
                    $this->payment_method = $data['payment_method'];
                    $this->subscription   = $data['subscription'];
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
                            Log::info('Kafka Job: SuccessPaymentEventJob');
                            $kafka_data = [
                                "kafka_topic"   => "subscription_system_events_json",
                                "eventId"       => Str::uuid()->toString(),
                                "eventName"     => "successful_payment",
                                "eventTime"     => now()->toIso8601String(),
                                "eventMetadata" => [
                                    "page"         => "Successful Payment",
                                    "user"         => [
                                        "userId"    => optional($this->user)->id ?? $this->subscription->user->id,
                                        "userName"  => $this->user->name ?? $this->subscription->user->name,
                                        "userEmail" => optional($this->user)->email ?? $this->subscription->user->email,
                                    ],
                                    "product"      => [
                                        "productName" => $this->subscription->product->product_name
                                    ],
                                    "transaction"  => [
                                        "transcode"      => $this->transaction->identifier,
                                        "amount"         => $this->transaction->amount,
                                        "amount_paid"    => $this->transaction->amount_paid,
                                        "receipt"        => $this->transaction->receipt,
                                        "payment_method" => $this->payment_method,
                                    ],
                                    "subscription" => $this->subscription
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
                            Log::error('Kafka successful payment', [$e->getMessage()]);
                        }

                    try
                        {
                            Log::info('Kafka Job: SuccessPaymentEventJob');
                            $kafka_data = [
                                "kafka_topic"   => "subscription_system_events_json",
                                "eventId"       => Str::uuid()->toString(),
                                "eventName"     => "customer_subscription",
                                "eventTime"     => now()->toIso8601String(),
                                "eventMetadata" => [
                                    "page"         => "customer subscription",
                                    "user"         => [
                                        "userId"    => optional($this->user)->id ?? $this->subscription->user->id,
                                        "userName"  => $this->user->name ?? $this->subscription->user->name,
                                        "userEmail" => optional($this->user)->email ?? $this->subscription->user->email,
                                    ],
                                    "product"      => [
                                        "productName" => $this->subscription->product->product_name
                                    ],
                                    "subscription" => [
                                        "type"              => $this->subscription->rate->name,
                                        "cost"              => $this->subscription->rate->cost,
                                        "currency"          => $this->subscription->rate->currency,
                                        "payment_method"    => $this->transaction->channel,
                                        "transcode"         => $this->transaction->identifier,
                                        "subscription_date" => $this->subscription->subscription_date,
                                        "expiry_date"       => $this->subscription->expiry_date,
                                    ],

                                ]
                            ];

                            if (!is_null($this->transaction->coupon_code))
                                {
                                    $coupon                                  = Coupon::where('code', $this->transaction->coupon_code)->first();
                                    $kafka_data["eventMetadata"]["discount"] = [
                                        "coupon_code" => $coupon->code,
                                        "type"        => ($coupon->type == 0) ? 'Percentage' : "Fixed",
                                        "value"       => $coupon->discount,
                                    ];
                                }

                            Http::withHeaders([
                                                  'Content-Type' => 'application/json',
                                              ])
                                ->connectTimeout(3)->timeout(10)->retry([100, 500, 1000])
                                ->post(config('kafka.kafka_domain').'/api/v1/subscriptions', $kafka_data)->throw();
                        }

                    catch (\Exception $e)
                        {
                            Log::error('Kafka subscription ', [$e->getMessage()]);
                            throw $e;
                        }
                }
        }
