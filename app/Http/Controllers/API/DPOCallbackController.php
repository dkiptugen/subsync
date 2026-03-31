<?php


    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use App\Http\Resources\SubscriptionResource;
    use App\Jobs\ExtendBundleChildSubscriptions;
    use App\Jobs\Kafka\FailedPaymentEventJob;
    use App\Jobs\Kafka\SuccessPaymentEventJob;
    use App\Libs\DPO;
    use App\Models\Cart;
    use App\Models\Subscription;
    use App\Models\Transaction;
    use Exception;
    use GuzzleHttp\Client;
    use Illuminate\Http\Request;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Mtownsend\XmlToArray\XmlToArray;
    use Spatie\ArrayToXml\ArrayToXml;


    class DPOCallbackController extends Controller
        {
            public $dpo;

            protected $transrenewals;

            public function __construct()
                {

                    parent::__construct();
                    $this->dpo = new DPO();

                }

            public function verifyToken(Request $request)
                {
                    try
                        {
                            $trans = Transaction::with(['subscription', 'payment_method','user'])
                                                ->where('transaction_token', $request->TransactionToken)
                                                ->where('identifier', $request->CompanyRef)
                                                ->first();
                            $cart = null;
                            if(is_null($trans))
                            {
                                $cart = Cart::where('identifier', $request->CompanyRef)->first();
                                if($cart)
                                {
                                    $trans = Transaction::with(['subscription', 'payment_method','user'])
                                        ->where('transaction_token', $request->TransactionToken)
                                        ->whereHas('subscription', function ($query) use ($cart) {
                                            $query->where('cart_id', $cart->id);
                                        })
                                        ->first();
                                }
                            }

                            if (!is_null($trans))
                                {
                                    $firstcount = 0;
                                    $this->dpo->transaction_token = $trans->transaction_token;
                                    $this->dpo->company_token     = $trans->payment_method->configuration['company_token'];
                                    $this->dpo->accountref        = !is_null($cart) ?$cart->identifier : $trans->identifier;
                                    firstrepeat:
                                    $statusResult                 = $this->dpo->verifyToken();
                                    try{
                                        $statusCode = simplexml_load_string($statusResult);
                                    }catch (Exception $e)
                                    {
                                        report($e);
                                        if($firstcount <=2)
                                        {
                                            $firstcount += 1;
                                            goto firstrepeat;
                                        }
                                    }


                                    if (@$statusCode->Result == '000')
                                        {
                                            try
                                                {
                                                    $trans->amount_paid      = $trans->amount;
                                                    $trans->status           = 1;
                                                    $trans->receipt          = (string)$statusCode->TransactionApproval ?? '';
                                                    $trans->initiator        = $statusCode->CustomerName ?? '';
                                                    $trans->transaction_date = Carbon::parse($statusCode->TransactionSettlementDate)->toDateTimeString();
                                                    //$trans->response         = json_encode($statusCode);
                                                    $res = $trans->save();
                                                    if ($res)
                                                        {

                                                            //Log::error ($trans->subscription->rate->name);
                                                            if (strtolower($trans->subscription->rate->name) == 'archive issue')
                                                                {
                                                                    if ($trans->subscription->status == 0)
                                                                        {
                                                                            $subscription         = Subscription::find($trans->subscription_id);
                                                                            $subscription->status = 1;
                                                                            $subscription->save();
                                                                            //Log::debug ('Archive hit');
                                                                        }
                                                                }

                                                            else
                                                                {
                                                                    if ($trans->subscription->recurring == 1)
                                                                        {
                                                                            $secondcount = 0;
                                                                            $subtoken                = new DPO();
                                                                            $subtoken->company_token = $trans->payment_method->configuration['company_token'];
                                                                            $subtoken->email         = $trans->user->email;
                                                                            secondrepeat:
                                                                            $result                  = $subtoken->retrieveTokenSub();
                                                                            try{
                                                                                $resultCode              = simplexml_load_string($result);
                                                                            }catch (Exception $e)
                                                                            {
                                                                                report($e);
                                                                                if($secondcount <=2)
                                                                                {
                                                                                    $secondcount += 1;
                                                                                    goto secondrepeat;
                                                                                }
                                                                            }

                                                                            if ($resultCode->Result == '000')
                                                                                {
                                                                                    $subscription                     = Subscription::find($trans->subscription_id);
                                                                                    $subscription->subscription_token = (string)$resultCode->subscriptionToken;
                                                                                    $subscription->status             = 1;
                                                                                    $subscription->save();
                                                                                    //Log::debug ('Reccurrent subscription hit');

                                                                                }

                                                                            if ($trans->subscription->reccurent_cycle == 0 && $trans->subscription->status == 0)
                                                                                {

                                                                                    if ($trans->subscription->product->type == 'epaper')
                                                                                        {
                                                                                            $subscription                    = Subscription::find($trans->subscription_id);
                                                                                            //$subscription->subscription_date = Carbon::now()->startOfDay();
                                                                                            //$subscription->expiry_date       = Carbon::now()->addDays($trans->subscription->rate->period - 1)->endOfDay()->toDateTimeString();
                                                                                            $subscription->increment('reccurent_cycle');
                                                                                            $subscription->status = 1;
                                                                                            $subscription->save();

                                                                                        }
                                                                                    else
                                                                                        {
                                                                                            $subscription                    = Subscription::find($trans->subscription_id);
                                                                                            //$subscription->subscription_date = Carbon::now();
                                                                                            //$subscription->expiry_date       = Carbon::now()->addDays($trans->subscription->rate->period)->toDateTimeString();
                                                                                            $subscription->increment('reccurent_cycle');
                                                                                            $subscription->status = 1;
                                                                                            $subscription->save();

                                                                                        }
                                                                                    //Log::debug ('Reccurrent subscription hit - 0');
                                                                                }
                                                                            else
                                                                                {
                                                                                    if ($trans->subscription->product->type == 'epaper')
                                                                                        {
                                                                                            $subscription = Subscription::find($trans->subscription_id);
                                                                                            if ($trans->subscription->status == 1 && Carbon::parse($trans->subscription->expiry_date)->gt(Carbon::now()))
                                                                                                {
//                                                                                                    $subscription->expiry_date = Carbon::parse($trans->subscription->expiry_date)
//                                                                                                                                       ->addDays($trans->subscription->rate->period)
//                                                                                                                                       ->endOfDay()
//                                                                                                                                       ->toDateTimeString();
                                                                                                }
                                                                                            else
                                                                                                {
//                                                                                                    $subscription->expiry_date = Carbon::now()
//                                                                                                                                       ->addDays($trans->subscription->rate->period - 1)
//                                                                                                                                       ->endOfDay()
//                                                                                                                                       ->toDateTimeString();
                                                                                                }

                                                                                            $subscription->increment('reccurent_cycle');
                                                                                            $subscription->status = 1;
                                                                                            $subscription->save();
                                                                                        }
                                                                                    else
                                                                                        {
                                                                                            $subscription         = Subscription::find($trans->subscription_id);
                                                                                            $subscription->status = 1;
                                                                                            if ($trans->subscription->status == 1 && Carbon::parse($trans->subscription->expiry_date)->gt(Carbon::now()))
                                                                                                {
                                                                                                    //$subscription->expiry_date = Carbon::parse($trans->subscription->expiry_date)->addDays($trans->subscription->rate->period)->toDateTimeString();
                                                                                                }
                                                                                            else
                                                                                                {
//                                                                                                    $subscription->expiry_date = Carbon::now()
//                                                                                                                                       ->addDays($trans->subscription->rate->period)
//                                                                                                                                       ->toDateTimeString();
                                                                                                }

                                                                                            $subscription->increment('reccurent_cycle');
                                                                                            $subscription->save();

                                                                                        }
                                                                                    if ($trans->subscription->reccuring == 1)
                                                                                        {
                                                                                            $trans->subscription->metadata()->insert([
                                                                                                                                         'start_date'        => Carbon::now()->startOfDay(),
                                                                                                                                         'next_renewal_date' => Carbon::now()->addDays($trans->subscription->rate->period)->startOfDay(),
                                                                                                                                         'expiry_date'       => Carbon::now()->addDays($trans->rate->period)->endOfDay()
                                                                                                                                     ]);
                                                                                        }

                                                                                    Log::debug('Reccurrent subscription hit - 1');
                                                                                    return redirect('https://www.nation.africa');
                                                                                }


                                                                        }
                                                                    else
                                                                        {
                                                                            if ($trans->subscription->status == 0)
                                                                                {
                                                                                    if ($trans->subscription->product->type == 'epaper')
                                                                                        {
                                                                                            $subscription = Subscription::find($trans->subscription->id);
                                                                                            $subscription->status            = 1;
                                                                                            //$subscription->subscription_date = Carbon::now()->startOfDay();
                                                                                            //if($trans->subscription->status == 1)
                                                                                            //$subscription->expiry_date = Carbon::now()->addDays($trans->subscription->rate->period)->endOfDay()->toDateTimeString();
                                                                                            $subscription->save();
                                                                                            Log::debug('Epaper: Non Reccurrent subscription hit' . json_encode($subscription));

                                                                                        }
                                                                                    else
                                                                                        {
                                                                                            $subscription                    = Subscription::find($trans->subscription->id);
                                                                                            $subscription->status            = 1;
                                                                                            //$subscription->subscription_date = Carbon::now();
                                                                                            //$subscription->expiry_date       = Carbon::now()->addDays($trans->subscription->rate->period)->toDateTimeString();
                                                                                            $subscription->save();
                                                                                            Log::debug('Paywall : Non Reccurrent subscription hit' . json_encode($subscription));

                                                                                        }
                                                                                    //Log::debug ('Non Reccurrent
                                                                                    // subscription hit');
                                                                                    $dtd = new SubscriptionResource($subscription);
                                                                                    Log::info(json_encode($dtd));
                                                                                    //dd($trans);
                                                                                    return response()->json([
                                                                                                                'status' => true, 'data' => $dtd
                                                                                                            ]);

                                                                                }
                                                                            $dtd = new SubscriptionResource(Subscription::find($trans->subscription->id));
                                                                            Log::info(json_encode($dtd));
                                                                            //dd($trans);
                                                                            return response()->json([
                                                                                                        'status' => true, 'data' => $dtd
                                                                                                    ]);

                                                                        }
                                                                }

                                                            $dtd = new SubscriptionResource(Subscription::find($trans->subscription->id));
                                                            //Log::info (json_encode ($dtd));
                                                            //dd($trans);
                                                            return response()->json([
                                                                                        'status' => true, 'data' => $dtd
                                                                                    ]);
                                                        }

                                                    //https://epaper.nation
                                                    //.africa/account/subscription/dpo-callback?TransID=2DE51A56-3834-43C2-99AC-97096156A214&CCDapproval=SEH8ILC1F6&PnrID=DVBNDVNZ&TransactionToken=2DE51A56-3834-43C2-99AC-97096156A214&CompanyRef=DVBNDVNZ
                                                    $sub = Subscription::find($trans->subscription->id);

                                                    try
                                                        {
                                                            $kafka_data =   [
                                                                'transaction'    => $trans,
                                                                'user'           => $trans->user,
                                                                'payment_method' => 'DPO',
                                                                'subscription'   => $sub
                                                            ];
                                                            SuccessPaymentEventJob::dispatch($kafka_data);
                                                        }
                                                    catch (\Exception $e)
                                                        {
                                                            Log::error("Kafka successful payment", [$e->getMessage()]);
                                                        }

                                                    return response()->json([
                                                                                'status' => true, 'data' => new SubscriptionResource($sub)
                                                                            ]);
                                                }
                                            catch (Exception $e)
                                                {
                                                    echo $e->getMessage();
                                                }


                                        }
                                    else
                                        {
                                            try
                                                {
                                                    $kafka_data = ['transaction'    => $trans,
                                                                   'user'           => @$trans->user,
                                                                   'subscription'   => @$trans->subscription,
                                                                   'amount'         =>  @$trans->currency.@$trans->amount.'/=',
                                                                   'error_message'  => isset($statusCode->ResultExplanation) ? (string) $statusCode->ResultExplanation : null,
                                                                   'payment_method' => 'DPO'
                                                    ];
                                                    FailedPaymentEventJob::dispatch($kafka_data);
                                                }
                                            catch (\Exception $e)
                                                {
                                                    Log::error("Kafka Mpesa Failed: ", [$e->getMessage()]);
                                                }
                                            return response()->json([
                                                                        'status' => false, 'data' => 'payment not successful'
                                                                    ]);
                                        }

                                }
                            else
                                {
                                    return response()->json([
                                                                'status' => false, 'data' => 'transaction not found'
                                                            ]);
                                }
                        }
                    catch (Exception $e)
                        {
                            return response()->json([
                                                        'status' => false, 'data' => $e->getMessage()
                                                    ]);
                        }

                }

            public function notification(Request $request)
                {

                    $xml = $request->getContent();
                    $request_data = XmlToArray::convert( $xml);

                    $request->merge($request_data);

                    $trans = Transaction::with([
                                                   'subscription', 'payment_method'
                                               ])->where('transaction_token', $request->TransactionToken)->where('identifier',
                                                                                                                 $request->TransactionRef)->first();

                    //Log::info ($trans);
                    if (!is_null($trans))
                        {
                            $this->dpo->transaction_token = $trans->transaction_token;
                            $this->dpo->company_token     = $trans->payment_method->configuration['company_token'];
                            $this->dpo->accountref        = $trans->identifier;
                            $statusResult                 = $this->dpo->verifyToken();
                            $statusCode                   = simplexml_load_string($statusResult);
                            if ($statusCode->Result == '000')
                                {
                                    try
                                        {
                                            $trans                   = Transaction::with([
                                                                                             'subscription', 'subscription.rate', 'payment_method'
                                                                                         ])->where('identifier', $statusCode->AccRef)->first();
                                            $trans->amount_paid      = $trans->amount;
                                            $trans->status           = 1;
                                            $trans->receipt          = (string)$statusCode->TransactionApproval ?? '';
                                            $trans->initiator        = $statusCode->CustomerName ?? '';
                                            $trans->transaction_date = Carbon::parse($statusCode->TransactionSettlementDate)->toDateTimeString();
                                            //$trans->response         = json_encode($statusCode);
                                            $res = $trans->save();
                                            if ($res)
                                                {
                                                    $trans->subscription()
                                                          ->where('id', $trans->subscription_id)
                                                          ->update(['status' => 1]);

                                                    deactivate_after_upgrade($trans->identifier);

                                                    if ($trans->subscription->recurring == 1)
                                                        {
                                                            $subtoken                = new DPO();
                                                            $subtoken->company_token = $trans->payment_method->configuration['company_token'];
                                                            $subtoken->email         = $statusCode->CustomerEmail;
                                                            $result                  = $subtoken->retrieveTokenSub();
                                                            $resultCode              = simplexml_load_string($result);
                                                            $ata                     = [];
                                                            $ata['status']           = 1;

                                                            //Log::error($result);

                                                            if ($resultCode->Result == '000')
                                                                {
                                                                    $ata['subscription_token'] = $resultCode->subscriptionToken;
                                                                }

                                                            if (strtolower($trans->subscription->rate->name) != 'archive issue')
                                                                {
                                                                    $ata['reccurent_cycle'] = ++$trans->subscription->reccurent_cycle;

                                                                }
                                                            $trans->subscription()->where('id',
                                                                                          $trans->subscription_id)->update($ata);
                                                            //Log::error($trans->subscription->refresh());
                                                            if ($trans->subscription->recurring == 1)
                                                                {
                                                                    $trans->subscription->metadata()->insert([
                                                                                                                 'start_date'        => Carbon::now()->startOfDay(),
                                                                                                                 'next_renewal_date' => Carbon::now()->addDays($trans->subscription->rate->period + 1)->startOfDay(),
                                                                                                                 'expiry_date'       => Carbon::now()->addDays($trans->rate->period)->endOfDay()
                                                                                                             ]);
                                                                }


                                                        }


                                                }

                                        }
                                    catch (Exception $e)
                                        {
                                            Log::error($e->getMessage());
                                        }
                                    try
                                        {
                                            $kafka_data =   [
                                                'transaction'    => $trans,
                                                'user'           => $trans->user,
                                                'payment_method' => 'DPO',
                                                'subscription'   => $trans->subscription
                                            ];
                                            SuccessPaymentEventJob::dispatch($kafka_data);
                                        }
                                    catch (\Exception $e)
                                        {
                                            Log::error("Kafka successful payment", [$e->getMessage()]);
                                        }

                                    $data = '<?xml version="1.0" encoding="utf-8"?>
										<API3G>
                                            <Response>OK</Response>
                                        </API3G>';
                                    return response($data, 200)->header('Content-Type', 'application/xml');
                                }
                            else
                                {
                                    try
                                        {
                                            $kafka_data = ['transaction'    => $trans,
                                                           'user'           => $trans->user,
                                                           'subscription'   => $trans->subscription,
                                                           'amount'         =>  @$statusCode->TransactionCurrency.@$statusCode->TransactionAmount.'/=',
                                                           'error_message'  => @$statusCode->ResultExplanation,
                                                           'payment_method' => 'DPO'
                                            ];
                                            FailedPaymentEventJob::dispatch($kafka_data);
                                        }
                                    catch (\Exception $e)
                                        {
                                            Log::error("Kafka DPO Failed: ", [$e->getMessage()]);
                                        }
                                }
                        }

                }

            public function dpo_callback(Request $request)
            {
                    $token     = $request->TransactionToken;
                    $reference = $request->CompanyRef;
                    $query =  $request->query();
                    $querystring = request()->getQueryString();
                    $count = 0;

                    $transactions = Transaction::with([
                                                   'payment_method','user','subscription.product'
                                               ])->where('transaction_token', $token)->where('identifier', $reference)->get();

                    $cart = null;
                    if($transactions->isEmpty())
                    {
                        $cart = Cart::with(['subscriptions'])->where('identifier', $reference)->first();
                        if($cart)
                        {
                            $subids = $cart->subscriptions->pluck('id')->toArray();
                            $transactions = Transaction::with(['payment_method','user','subscription.product'])
                                ->whereIn('subscription_id', $subids)->orderBy('id','desc')->get();
                        }
                    }

                    $endpoint = 'https://secure.3gdirectpay.com/API/v6/';

                    if(!$transactions->isEmpty())
                    {
                        $first = $transactions->first();

                        if($first->status == 1)
                        {
                            if($cart)
                            {
                                $cart->status = 1;
                                $cart->save();
                            }

                            deactivate_after_upgrade($first->identifier);

                            $url = $first->redirect_url;
                            $separator = '?';
                            if(str_contains($url, '?'))
                                $separator = '&';

                            $url = $url . $separator . $querystring;

                            return redirect($url);
                        }

                        $data = [
                            'CompanyToken' => @$first->payment_method->configuration['company_token'] ,
                            'Request' => 'verifyToken',
                            'TransactionToken' => $first->transaction_token ,
                            'ACCref' => @$cart->identifier ?? $first->identifier
                        ];

                        $payload = ArrayToXml::convert($data,'API3G',false,'UTF-8');

                        $client = new Client(['headers' => [ 'Content-Type' => 'application/xml'],
                            'verify'=> base_path('/cacert.pem'),'http_errors'=>true]);

                        repeat:
                        try{
                            $response = $client->request('POST', $endpoint, ['body' => $payload]);
                            $responseBody = @$response->getBody()->getContents();
                            $result = (object)XmlToArray::convert($responseBody);
                        }catch (\Exception $e)
                        {
                            report($e);
                            Log::error($e->getMessage());
                            if($count <=2)
                            {
                                $count += 1;
                                goto repeat;
                            }
                        }

                        $url = $first->redirect_url;

                        if(@$result->Result == '000') {

                            if($cart)
                            {
                                $cart->status = 1;
                                $cart->save();
                            }

                            foreach ($transactions as $transaction) {
                                $transaction->status = 1;
                                $transaction->receipt = (string)$result->TransactionApproval ?? '';
                                $transaction->initiator = $result->CustomerName ?? '';
                                $transaction->transaction_date = Carbon::parse($result->TransactionSettlementDate)->toDateTimeString();
                                $transaction->result = trim(@$result->ResultExplanation);
                                $transaction->amount_paid = $transaction->amount;
                                $transaction->save();
                                $transaction->subscription()->where('id', $transaction->subscription_id)->update(['status' => 1]);
                                deactivate_after_upgrade($transaction->identifier);

                                $kafka_data =   [
                                    'transaction'    => $transaction,
                                    'user'           => $transaction->user,
                                    'payment_method' => 'DPO',
                                    'subscription'   => $transaction->subscription,
                                ];
                                SuccessPaymentEventJob::dispatch($kafka_data);
                                //ExtendBundleChildSubscriptions::dispatch(@$transaction->subscription->id);
                            }
                        }
                        else{
                            $url = $first->back_url;
                            foreach ($transactions as $transaction) {
                                $transaction->result = trim(@$result->ResultExplanation);
                                $transaction->save();
                                $kafka_data = ['transaction'    => $transaction,
                                    'user'           => $transaction->user,
                                    'subscription'   => $transaction->subscription,
                                    'amount'         =>  @$result->TransactionCurrency.@$result->TransactionAmount.'/=',
                                    'error_message'  => @$result->ResultExplanation,
                                    'payment_method' => 'DPO'
                                ];
                                FailedPaymentEventJob::dispatch($kafka_data);
                            }
                        }
                    }
                    else{
                        $url = 'https://www.nation.africa';
                    }

                    $separator = '?';
                    if(str_contains($url, '?'))
                        $separator = '&';

                    $url = $url . $separator . $querystring;

                    return redirect($url);
            }

        }
