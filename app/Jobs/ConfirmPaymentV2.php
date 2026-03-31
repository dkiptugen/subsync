<?php

    namespace App\Jobs;

    use App\Libs\DPO;
    use App\Models\Transaction;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Log;

    class ConfirmPaymentV2 implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        public $identifier;
        public $transaction_token;

        public function __construct($identifier, $transaction_token)
        {
            $this->transaction_token = $transaction_token;
            $this->identifier        = $identifier;
        }

        public function handle()
        {
            $maxAttempts    = 120;
            $currentAttempt = 1;

            while ($currentAttempt <= $maxAttempts) {
                $transaction = $this->getTransaction();

                if ($transaction) {
                    $statusCode = $this->verifyTransaction($transaction);

                    Log::error($statusCode);

                    if ($statusCode->Result == '000') {
                        $this->updateTransaction($transaction, $statusCode);
                        break;
                    }
                }

                // Implement a retry strategy here

                $currentAttempt++;
            }
        }

        protected function getTransaction()
        {
            return Transaction::with(['subscription', 'subscription.rate'])->where('transaction_token',
                    $this->transaction_token)->where('identifier', $this->identifier)->first();
        }

        protected function verifyTransaction($transaction)
        {
            $dpo                    = new DPO();
            $dpo->transaction_token = $transaction->transaction_token;
            $dpo->company_token     = $transaction->payment_method->configuration['company_token'];
            $dpo->accountref        = $transaction->identifier;

            return simplexml_load_string($dpo->verifyToken());
        }

        protected function updateTransaction($transaction, $statusCode)
        {

            $trans                   = $transaction;
            $trans->amount_paid      = $trans->amount;
            $trans->status           = 1;
            $trans->receipt          = $statusCode->TransactionApproval ?? '';
            $trans->initiator        = $statusCode->CustomerName ?? '';
            $trans->transaction_date = Carbon::parse($statusCode->TransactionSettlementDate)->toDateTimeString();
            //$trans->response         = $statusResult;
            $res = $trans->save();
            if ($res) {
	            if(Carbon::parse($trans->subscription->subscription_date)->gte(Carbon::now()))
		            {
			            $trans->subscription ()
			                  ->where ('id', $trans->subscription_id)
			                  ->update ([ 'status' => 1]);
		            }
	            else
		            {
			            $trans->subscription ()
			                  ->where ('id', $trans->subscription_id)
			                  ->update ([
				                  'subscription_date' => Carbon::now()->startOfDay(),
				                  'expiry_date'       => Carbon::now()->addDays($trans->subscription->rate->period)->endOfDay(),
				                  'status' => 1
			                  ]);
		            }

                if ($trans->subscription->recurring == 1) {

                    $trans->subscription->metadata()->insert([
                            'start_date'        => Carbon::now()->startOfDay(),
                            'next_renewal_date' => Carbon::now()->addDays($trans->subscription->rate->period + 1)->startOfDay(),
                            'expiry_date'       => Carbon::now()->addDays($trans->rate->period)->endOfDay()
                        ]);
                }
            }
        }
    }
