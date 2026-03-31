<?php

    namespace App\Jobs\Kafka;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    class UserRegistrationEventJob implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels
            ;
        public $user;
        public $link;
        public $ip_address;
        /**
         * Create a new job instance.
         */
        public function __construct(array $data)
            {
                $this->user       = $data['user'];
                $this->link       = $data['link'];
                $this->ip_address = $data['ip_address'];
                $this->user->withoutRelations();
            }

        /**
         * Execute the job.
         */
        public function handle(): void
            {
                try
                {
                    Log::info('Kafka Job: User Registration');
                    $kafka_data = [
                        "kafka_topic"   => "subscription_system_events_json",
                        "eventId"       => Str::uuid()->toString(), // Use Laravel's Str::uuid() helper to generate a UUID for eventId
                        "eventName"     => "customer_registration", // Specify the eventName at runtime, e.g., "successful_payment" or "customer_subscription"
                        "eventTime"     => now()->toIso8601String(),
                        "eventMetadata" => [
                            "page" => "register",
                            "user" => [
                                "userName"        => $this->user->name,
                                "userId"          => $this->user->id,
                                "userEmail"       => $this->user->email,
                                "organization"    => (optional($this->user->organization)->name)??NULL,
                                "allow_marketing" => $this->user->allow_marketing,
                                "can_notify"      => $this->user->can_notify,
                                'ip_address'      => $this->ip_address,
                                'source'          => $this->link,
                            ],
                        ]
                    ];
                    Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])
                        ->post(config('kafka.kafka_domain').'/api/v1/subscriptions', $kafka_data);
                }
                catch (\Exception $e)
                {
                    Log::error('Kafka User registration Job', [$e->getMessage()]);
                }
            }
    }
