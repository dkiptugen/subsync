<?php

	namespace App\Jobs;

	use App\Events\PaymentFailed;
	use App\Events\PaymentMade;
	use App\Models\Subscription;
	use App\Models\Transaction;
	use Illuminate\Bus\Queueable;
	use Illuminate\Contracts\Queue\ShouldQueue;
	use Illuminate\Foundation\Bus\Dispatchable;
	use Illuminate\Queue\InteractsWithQueue;
	use Illuminate\Queue\SerializesModels;
	use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Log;

    class MpesaPaymentJob implements ShouldQueue
		{
			use Dispatchable, InteractsWithQueue, Queueable
                //, SerializesModels
                ;

			public $trans_code;
			public $amount_paid;
			public $receipt_no;
			public $user_name;
			public $user_number;
			public $response;
			public $transtime;

		/**
		 * Create a new job instance.
		 */
			public function __construct($transcode, $amount, $receipt, $name, $number, $transtime, $response)
				{
					$this->trans_code  = $transcode;
					$this->amount_paid = $amount;
					$this->receipt_no  = $receipt;
					$this->user_name   = $name;
					$this->user_number = $number;
					$this->response    = $response;
					$this->transtime   = $transtime;
				}

		/**
		 * Execute the job.
		 */
			public function handle()
			: void
				{
					try
						{
							$transaction = Transaction::with(['subscription'])->where('identifier', $this->trans_code)->first();
							if ($transaction->amount <= $this->amount_paid)
								{
                                    if($transaction->status != 1)
                                        {
                                            $transaction->increment('amount_paid', $this->amount_paid);
                                            $transaction->status           = 1;
                                            $transaction->receipt          = $this->receipt_no;
                                            $transaction->initiator        = $this->user_name.' - '.$this->user_number;
                                            $transaction->response         = $this->response;
                                            $transaction->transaction_date = Carbon::parse($this->transtime)->toDateTimeString();
                                            $res  = $transaction->save();
                                        }
                                    else
                                        {
                                            $res = true;
                                        }

									if($res)
										{
											$subscription         = Subscription::find ($transaction->subscription_id);
                                            if ($transaction->subscription->status == 0)
                                            {
                                                $subscription->status = 1;
                                                $subscription->save ();
                                            }
										}

								}
							else
								{
                                    if($transaction->status != 1)
                                        {
                                            $transaction->decrement('amount', $this->amount_paid);
                                            $transaction->increment('amount_paid', $this->amount_paid);
                                            $transaction->receipt          = $this->receipt_no;
                                            $transaction->initiator        = $this->user_name . ' - ' . $this->user_number;
                                            $transaction->response         = $this->response;
                                            $transaction->transaction_date = Carbon::parse($this->transtime)->toDateTimeString();
                                            $res                           = $transaction->save();
                                        }
								}
							event(new PaymentMade($transaction));
                            //ExtendBundleChildSubscriptions::dispatch($transaction->subscription->id);

							$this->delete();
						}
					catch (\Exception $e)
						{
							event(new PaymentFailed($this->trans_code,$e->getMessage ()));
						}
					//event(new PaymentMade($transaction));


				}
		}
