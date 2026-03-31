<?php

    namespace App\Events;

    use Illuminate\Broadcasting\Channel;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Broadcasting\PresenceChannel;
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class PaymentFailed implements ShouldBroadcastNow {
        use Dispatchable, InteractsWithSockets, SerializesModels;

        public $identifier;
        public $message;

        public function __construct( $identifier, $message ) {
            $this->identifier = $identifier;
            $this->message    = $message;
        }


        public function broadcastOn() {
            return new Channel( 'payment.' . $this->identifier );
        }

        public function broadcastAs() {
            return 'failed_payment';
        }

        public function broadcastWith() {
            return [
                'error_message' => $this->message
            ];
        }


    }
