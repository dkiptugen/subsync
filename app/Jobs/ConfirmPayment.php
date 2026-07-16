<?php

	namespace App\Jobs;

	use App\Libs\DPO;
	use App\Models\Transaction;
	use Exception;
	use Illuminate\Bus\Queueable;
	use Illuminate\Contracts\Queue\ShouldQueue;
	use Illuminate\Foundation\Bus\Dispatchable;
	use Illuminate\Queue\InteractsWithQueue;
	use Illuminate\Queue\SerializesModels;
	use Illuminate\Support\Carbon;

	class ConfirmPayment implements ShouldQueue
		{
			use Dispatchable;
			use InteractsWithQueue;
			use Queueable;
			use SerializesModels;

			public $identifier;
			public $transaction_token;
			public int $tries = 120;
			public int $timeout = 60;
			public int $backoff = 10;
		//public $queue = 'low';


		/**
		 * Create a new job instance.
		 */
			public function __construct (string $identifier, string $transaction_token)
				{
					$this->transaction_token = $transaction_token;
					$this->identifier        = $identifier;
					//Log::error( $this->transaction_token. $this->identifier);

				}


		/**
		 * Execute the job.
		 */
			public function handle ()
			: void
				{
					$trans = Transaction::with ([
						'subscription', 'subscription.rate'
					])->where ('transaction_token', $this->transaction_token)->where ('identifier',
						$this->identifier)->first ();

					if (is_null($trans))
						{
							$this->release($this->backoff);

							return;
						}

					$statusCode = $this->verifyTransaction($trans);

					if (($statusCode->Result ?? null) !== '000')
						{
							$this->release($this->backoff);

							return;
						}

					$trans->amount_paid      = $trans->amount;
					$trans->status           = 1;
					$trans->receipt          = $statusCode->TransactionApproval ?? '';
					$trans->initiator        = $statusCode->CustomerName ?? '';
					$trans->transaction_date = Carbon::now()->toDateTimeString();
					$trans->response         = $statusCode;

					if (! $trans->save())
						{
							return;
						}

					if (Carbon::parse($trans->subscription->subscription_date)->gte(Carbon::now()))
						{
							$trans->subscription()->where('id', $trans->subscription_id)->update(['status' => 1]);
						}
					else
						{
							$trans->subscription()->where('id', $trans->subscription_id)->update([
								'subscription_date' => Carbon::now()->startOfDay(),
								'expiry_date' => Carbon::now()->addDays($trans->subscription->rate->period)->endOfDay(),
								'status' => 1,
							]);
						}

					if ($trans->subscription->recurring == 1)
						{
							$trans->subscription->metadata()->insert([
								'start_date' => $trans->subscription->subscription_date,
								'next_renewal_date' => Carbon::parse($trans->subscription->subscription_date)->addDays($trans->subscription->rate->period + 1)->startOfDay(),
								'expiry_date' => Carbon::parse($trans->subscription->subscription_date)->addDays($trans->subscription->rate->period)->endOfDay(),
							]);
						}
				}

			protected function verifyTransaction ($transaction)
				{
					$dpo                    = new DPO();
					$dpo->transaction_token = $transaction->transaction_token;
					$dpo->company_token     = $transaction->payment_method->configuration['company_token'];
					$dpo->accountref        = $transaction->identifier;
					$xml                    = $dpo->verifyToken ();
					try
						{
							$jsonString  = json_encode (simplexml_load_string ($xml)); // Convert XML string to JSON string
							$plainObject = json_decode ($jsonString);
						}
					catch (Exception $e)
						{
							$plainObject = (object) ["Result" => "001", "message" => $e->getMessage ()];
						}

					return $plainObject;
				}
		}
