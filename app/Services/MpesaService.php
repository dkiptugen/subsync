<?php

namespace App\Services;

use App\Events\PaymentMade;
use App\Models\Transaction;
use App\Traits\Meta;
use App\Libs\Mpesa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MpesaService
    {
        use Meta;
        public function start_notification($data)
            {
                $request                    =   new \stdClass();
                $request->shortcode         =   $data->identifier;
                $request->consumerkey       =   $data->configuration['consumer_key'];
                $request->consumersecret    =   $data->configuration['consumer_secret'];
                $pay                        =   new Mpesa();
                $result                     =   $pay->RegisterURL($request);
                if($result)
                    {
                        $data->notifying = 1;
                        $data->save();
                        return self::success('Mpesa Notification','Successful', route('payment_methods.index'));
                    }
                return self::failed('Mpesa Notification','failed', route('payment_methods.index'));
            }
		public function update_payment ($transcode, $amount, $receipt, $name, $number, $transtime, $response)
			{
				try
					{
						$transaction = Transaction::with (['subscription'])->where ('identifier', $transcode)->first ();
						if ($transaction->amount <= $amount)
							{
								$transaction->increment ('amount_paid', $amount);
								$transaction->status           = 1;
								$transaction->receipt          = $receipt;
								$transaction->initiator        = $name.' - '.$number;
								$transaction->response         = $response;
								$transaction->transaction_date = Carbon::parse ($transtime)->toDateTimeString ();
								$transaction->save ();
								$transaction->subscription ()->update (['status' => 1]);

							}
						else
							{
								$transaction->decrement ('amount', $amount);
								$transaction->increment ('amount_paid', $amount);
								$transaction->receipt          = $receipt;
								$transaction->initiator        = $name.' - '.$number;
								$transaction->response         = $response;
								$transaction->transaction_date = Carbon::parse ($transtime)->toDateTimeString ();
								$transaction->save ();
							}
						event (new PaymentMade($transaction));
						Log::info ('event fire');
					}
				catch(\Exception $e)
					{
						Log::info ($e->getMessage ());
					}
			}




}
