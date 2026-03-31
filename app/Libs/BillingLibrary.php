<?php


namespace App\Libs;

use App\Enums\PaymentStageEnum;
use App\Jobs\ConfirmPayment;
use App\Jobs\Kafka\DPOEventJob;
use App\Models\Cart;
use App\Models\PaymentMeta;
use App\Models\Subscription;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class BillingLibrary
{

    static function flattenStdClassToString($object)
    {
        // Convert stdClass to an array
        $array = (array)$object;

        // Flatten array values into a string
        return implode(' ', array_map('strval', $array));
    }

    public static function payment($subscription, $trans, $user, $payment_method, $recurrency, $region, $amount, $currency, $back, $redirect,$transactioncode= null)
    {
        $corrected_amount = (int)filter_var(str_replace(',', '', $amount), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $subscriptions = [];
        if($transactioncode)
        {
            $cart = Cart::where('identifier', $transactioncode)->first();
            if($cart)
            {
                $subs = Subscription::with(['transaction'])->where('cart_id',$cart->id)->get();
                foreach ($subs as $sub)
                {
                    $subscriptions[] = $sub;
                }
            }
        }
        else
        {
            array_push($subscriptions,$subscription);
            $subscriptions[0]->transaction = $trans;
        }

        if ($payment_method->provider == 'mpesa')
        {
            $mpesa                 = new Mpesa(PaymentStageEnum::from($payment_method->configuration['environment'])->name);
            $mpesa->consumerkey    = $payment_method->configuration['consumer_key'];
            $mpesa->consumersecret = $payment_method->configuration["consumer_secret"];
            $mpesa->shortcode      = $payment_method->configuration["shortcode"];
            $mpesa->trxcode        = 'PB';
            $mpesa->merchantname   = $payment_method->name;
            $mpesa->amount         = ceil($corrected_amount);
            $mpesa->ref            = $transactioncode ?? $trans->identifier;
            $mpesa->qrformat       = 1;
            $mpesa->qrtype         = 'D';
            $mpesa->size           = 300;
            $qr = null;
            try {
                $qr = $mpesa->qr();
            }catch (Exception $e)
            {
                report($e);
            }

            return ['status'           => true,
                'subscription' => false,
                'data' => route('mpesa.view', $transactioncode ?? $trans->identifier),
                'type' => 'mpesa',
                'transaction_code' => $transactioncode ?? $trans->identifier,
                'SubscriptionActivated' => false,
                'amount' => $corrected_amount,
                'account'=> $transactioncode ?? $trans->identifier,
                'paybill' => @$payment_method->configuration["shortcode"],
                'qr' => $qr,
                ];
        }
        if ($payment_method->provider == 'dpo')
        {
            $name               = explode(' ', $user->name);
            $dpo                = new DPO();
            $dpo->company_token = $payment_method->configuration['company_token'];
            $dpo->amount        = $corrected_amount;
            $dpo->currency      = $currency;
            $dpo->companyref    = $payment_method->id;
            $dpo->firstname     = @$name[0] ?? '';
            $dpo->lastname      = @$name[1] ?? $user->surname;
            $dpo->accountref    = $transactioncode ?? $trans->identifier;
            $dpo->email         = $user->email;
            //$dpo->back_url      = trim($back);
            //$dpo->redirect_url  = trim($redirect);
            $dpo->back_url                  = url('dpo_callback');
            $dpo->redirect_url              = url('dpo_callback');
//                            $dpo->back_url                  = 'https://dev-subscribe.nation.africa/dpo_callback';
//                            $dpo->redirect_url              = 'https://dev-subscribe.nation.africa/dpo_callback';
            $dpo->service[0]['type']        = $payment_method->configuration['shortcode'];
            $dpo->service[0]['description'] = $subscription->product->product_name ?? '-' . $subscription->rate->name ?? '';
            $dpo->service[0]['date']        = Carbon::now()->format('Y-m-d');
            $dpo->service[0]['ref']         = $subscription->identifier;
            $dpo->renewal                   = $recurrency;
            $dpo->country_code              = $region->code;
            $dpo->country                   = $region->name;
            $dpo->phone                     = $user->phone;
            Log::info(json_encode($dpo));
            $result = $dpo->Checkout();

            if(is_null($result))
            {
                $result = $dpo->Checkout();
            }

            if(is_string($result) || is_null($result))
            {
                Log::error("DPO FAILURE = ".$result);
            }

            foreach ($subscriptions as $ksubscription)
            {
                try
                {
                    if(is_array($result))
                    {
                        $kafka_data = [
                            'user'              => $user,
                            'subscription'      => $ksubscription,
                            'transaction'       => $ksubscription->transaction,
                            'amount'            => count($subscriptions) > 1 ? $ksubscription->amount  : $amount,
                            'transaction_token' => is_object(@$result['token']) ? self::flattenStdClassToString($result['token']) : $result['token'],
                            'company_token'     => $payment_method->configuration['company_token'],
                            'iframe'            => $result['iframe']
                        ];

                        DPOEventJob::dispatch($kafka_data);
                    }

                }
                catch (\Exception $e)
                {
                    report($e);
                    Log::error("Kafka DPO Notification Billing ", [$e->getMessage()]);
                }
            }

            try
            {
                if ($result)
                {
                    $trans->transaction_code  = (string)$result['TransRef'];
                    $trans->transaction_token = (string)$result['token'];
                    //$trans->redirect_url      = trim($redirect);
                    //$trans->back_url          = trim($back);
                    $trans->save();

                    //ConfirmPayment::dispatch($trans->identifier, $trans->transaction_token)->delay(now()->addSeconds(30))->onQueue('low');
                    return [
                        'status' => true,
                        'subscription' => false,
                        'data' => $result['iframe'],
                        'type' => 'dpo',
                        'transaction_code' => $transactioncode ?? $trans->identifier,
                        'SubscriptionActivated' => false,
                        'dpo_transaction_code' =>$trans->transaction_code,
                        'dpo_transaction_token' => $trans->transaction_token,
                    ];

                }
                else
                {
                    return [
                        'status' => false, 'subscription' => false,
                        'data' => trim($redirect),
                        'SubscriptionActivated' => false,
                        'type' => 'dpo',
                    ];
                }

            }
            catch (Exception $e)
            {
                report($e);
                Log::error($e->getMessage() . $e->getTraceAsString());
            }

        }
        if ($payment_method->povider == 'pesapal')
        {
            return response()->view('modules.front.pesapal', ['pay' => $payment_method, 'recurrency' => $recurrency]);
        }
    }
}
