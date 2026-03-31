<?php

	namespace App\Events;

	use Illuminate\Broadcasting\Channel;
	use Illuminate\Broadcasting\InteractsWithSockets;
	use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
	use Illuminate\Foundation\Events\Dispatchable;
	use Illuminate\Queue\SerializesModels;
	use Illuminate\Support\Facades\Log;


	class PaymentMade implements ShouldBroadcastNow
		{
			use Dispatchable, InteractsWithSockets, SerializesModels;


		/**
		 * Create a new event instance.
		 */
			public $transaction;


			public function __construct($transaction)
				{
					$this->transaction = $transaction;
					//Log::info('Event accessed');
				}


			public function broadcastOn()
				{
					return new Channel('payment.'.$this->transaction->identifier);
				}

			public function broadcastAs()
				{
					return 'new_payment';
				}

		}
