<?php

    namespace App\Http\Controllers\API;

    use App\Enums\PaymentStageEnum;
    use App\Events\PaymentFailed;
    use App\Events\PaymentMade;
    use App\Http\Controllers\Controller;
    use App\Http\Resources\SubscriptionResource;
    use App\Jobs\ExtendBundleChildSubscriptions;
    use App\Jobs\Kafka\SuccessPaymentEventJob;
    use App\Jobs\MpesaPaymentJob;
    use App\Jobs\SendWebhook;
    use App\Jobs\UpdatedPhoneJob;
    use App\Libs\Mpesa;
    use App\Models\Cart;
    use App\Models\Coupon;
    use App\Models\MpesaBlacklist;
    use App\Models\PaymentMethod;
    use App\Models\Product;
    use App\Models\Rate;
    use App\Models\Region;
    use App\Models\Subscription;
    use App\Models\SubscriptionGroup;
    use App\Models\Transaction;
    use App\Models\User;
    use App\Services\MpesaService;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Illuminate\Validation\ValidationException;

    class MpesaCallbackController extends Controller
        {
        /**
         * @param \Illuminate\Http\Request $request
         *
         * @return \App\Http\Resources\SubscriptionResource|\Illuminate\Http\JsonResponse
         */
            public function mpesa_payment(Request $request)
            : SubscriptionResource|JsonResponse
                {

                    try
                        {
                            $request->validate([
                                                   'payment_method_identifier' => 'required|exists:payment_methods,identifier',
                                                   'rate_id'                   => 'required|exists:rates,id',
                                                   'subscription_date'         => 'required',
                                                   'product'                   => 'required|exists:products,identifier',
                                               ]);
                        }
                    catch (ValidationException $e)
                        {
                            return response()->json([
                                                        'status' => false,
                                                        'data'   => $e->validator->errors()->first()
                                                    ]);
                        }

                    $payment_method = PaymentMethod::whereIdentifier($request->payment_method_identifier)->first();
                    $region         = Region::where('code', 'KE')->first();
                    if (!is_null($payment_method))
                        {
                            $product         = Product::where('identifier', $request->product)->first();
                            $subscription_date = Carbon::parse($request->subscription_date);
                            if($product->type == 'paywall')
                            {
                                $subscription_date = Carbon::parse($request->subscription_date . ' ' . now()->format('H:i:s'));
                            }

                            $subscribed = Subscription::with(['product', 'transaction', 'transaction.rate'])
                                ->where('subscription_date', '<=', $subscription_date->toDateTimeString())
                                ->where('expiry_date', '>=', $subscription_date->toDateTimeString())
                                ->where('rate_id', $request->rate_id)
                                ->where('user_id', $request->user()->id)
                                ->when($request->has('article_id') && is_numeric($request->article_id) ,function ($q) use ($request){
                                    $q->where('article_id', $request->article_id);
                                })
                                ->where('status', 1)->first();

                            if (is_null($subscribed))
                                {

                                    $subg = SubscriptionGroup::firstOrCreate([
                                                                                 'subdate' => $subscription_date->format('Y-m-d')
                                                                             ], [
                                                                                 'identifier' => Str::ulid()
                                                                             ]);


                                    $rate = Rate::with(['product'])->where('status', 1)->find($request->rate_id);

                                    if (is_null($rate))
                                        {
                                            return response()->json([
                                                                        'status'  => false,
                                                                        'message' => 'Invalid rate ' . $request->rate_id,
                                                                        'data'    => 'Invalid rate ' . $request->rate_id,
                                                                        'error'   => 'Invalid rate ' . $request->rate_id,
                                                                    ]);
                                        }

                                    if ($rate->cost == 0)
                                        {
                                            return response()->json([
                                                                        'identifier' => 'Free Subscription', 'product' => $rate->product->product_name, 'productIdentifier' => $rate->product->identifier, 'type' => 'whitelist', 'period' => 1, 'subscriptionDate' => Carbon::now()->startOfDay(), 'expiryDate' => Carbon::now()->endOfDay(), 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => 'N/A', 'subscriptionActivated' => true
                                                                    ]);
                                        }


                                    $elapsed_days = 0;
                                    if($rate->category !=='normal'  && $rate->name !=='Archive Issue'  && today()->gt(\Carbon\Carbon::parse($request->subscription_date)))
                                    {
                                        $elapsed_days = today()->diffInDays(Carbon::parse($request->subscription_date));
                                    }

                                    if ($subg)
                                        {
                                            $start_date      = ($rate->product->type == 'paywall') ? $subscription_date->toDateTimeString() : $subscription_date->startOfDay()->toDateTimeString();
                                            $end_date        = ($rate->product->type == 'paywall') ? $subscription_date->addDays(($rate->period + $rate->compensation_days + $elapsed_days))->toDateTimeString() : $subscription_date->addDays(($rate->period + $rate->compensation_days + $elapsed_days) - 1)->endOfDay()->toDateTimeString();
                                            $subs            = Subscription::updateOrCreate([
                                                                                                'user_id' => $request->user()->id, 'product_id' => $rate->product_id, 'rate_id' => $rate->id, 'subscription_date' => $start_date, 'expiry_date' => $end_date
                                                                                            ], [
                                                                                                'identifier' => $this->identifer('Subscription',
                                                                                                                                 'identifier',
                                                                                                                                 8), 'subscription_group_id' => $subg->id, 'reccuring' => 0,'article_id'=> ($rate->name =='article') ? $request->article_id : null,'type' => $rate->category
                                                                                            ]);
                                            //attach products to subs
                                            attach_products($subs);

                                            $bal             = $rate->cost;
                                            $amount          = $bal;
                                            $currency        = $rate->currency;
                                            $reserved_amount = $this->currency_convert($amount, $rate->currency,
                                                                                       config('custom.BILLING.RESERVED_CURRENCY'));
                                            $coupon          = null;
                                            $status          = 0;
                                            if ($request->has('coupon') && !empty(trim($request->coupon)))
                                                {
                                                    $cost         = $this->discount_calc($request->coupon, $amount,
                                                                                         $region, $rate->product_id,
                                                                                         $request->user()->id,
                                                                                         $rate->rate_type_id);
                                                    $discount     = $cost->discount;
                                                    $amount       = $cost->amount;
                                                    $total_amount = $cost->total_amount;

                                                    if ($discount > 0)
                                                        {
                                                            $coupon = $request->get('coupon');
                                                            $cpn    = Coupon::where('code', $coupon)->first();
                                                            $cpn->increment('usage');
                                                            $cpn->save();
                                                            if ($amount == 0)
                                                                {
                                                                    $status = 1;
                                                                }
                                                        }
                                                }
                                            else
                                                {
                                                    $discount     = 0;
                                                    $total_amount = $amount;
                                                }

                                            $trans                           = new Transaction();
                                            $trans->identifier               = $this->identifer('Transaction',
                                                                                                'identifier', 8);
                                            $trans->subscription_id          = $subs->id;
                                            $trans->payment_method_id        = $payment_method->id;
                                            $trans->{'channel'}              = $payment_method->name;
                                            $trans->total_amount             = $total_amount;
                                            $trans->amount                   = $amount;
                                            $trans->discount                 = $discount;
                                            $trans->coupon_code              = $request->coupon;
                                            $trans->currency                 = $currency;
                                            $trans->reserved_currency        = config('custom.BILLING.RESERVED_CURRENCY');
                                            $trans->reserved_currency_amount = $reserved_amount;
                                            $trans->status                   = $status;
                                            $trans->user_id                  = $request->user()->id;
                                            $trans->type                     = 'initial';
                                            $trans->save();
                                            $subs->status = $status;
                                            $subs->save();
                                            if ($status == 1)
                                                {
                                                    return response()->json(new SubscriptionResource($subs));
                                                }

                                            $mpesa                 = new Mpesa(PaymentStageEnum::from($payment_method->configuration['environment'])->name);
                                            $mpesa->consumerkey    = $payment_method->configuration['consumer_key'];
                                            $mpesa->consumersecret = $payment_method->configuration["consumer_secret"];
                                            $mpesa->shortcode      = $payment_method->configuration["shortcode"];
                                            $mpesa->trxcode        = 'PB';
                                            $mpesa->merchantname   = $payment_method->name;
                                            $mpesa->amount         = ceil($amount);
                                            $mpesa->ref            = $trans->identifier;
                                            $mpesa->qrformat       = 1;
                                            $mpesa->qrtype         = 'D';
                                            $mpesa->size           = 300;
                                            $qr                    = null;

                                            try
                                                {
                                                    $qr = $mpesa->qr();
                                                }
                                            catch (\Exception $e)
                                                {
                                                    report($e);
                                                }

                                            $data = [
                                                'amount' => $amount, 'account' => $trans->identifier, 'sub' => $subs->identifier, 'paybill' => $payment_method->configuration["shortcode"], 'qr' => $qr
                                            ];
                                            return response()->json($data);

                                        }
                                }
                            else
                                {
                                    return response()->json(new SubscriptionResource($subscribed));
                                }
                        }
                    else
                        {

                            return response()->json([
                                                        'status' => false, 'error' => 'payment method not found!'
                                                    ], 403);

                        }
                }

            public function notify(Request $request)
                {

                    $service = PaymentMethod::whereIdentifier($request->identifier)->first();
                    if (!is_null($service))
                        {
                            Log::alert(PaymentStageEnum::from($service->configuration['environment'])->name);
                            $mpesa                   = new Mpesa(PaymentStageEnum::from($service->configuration['environment'])->name);
                            $mpesa->consumerkey      = $service->configuration['consumer_key'];
                            $mpesa->consumersecret   = $service->configuration["consumer_secret"];
                            $mpesa->shortcode        = $service->configuration["shortcode"];
                            $mpesa->confirmation_url = route("mpesa.confirmation");
                            $mpesa->validation_url   = route("mpesa.validation");

                            $res = $mpesa->RegisterURL();
                            Log::info("response: " . json_encode($res));
                            if ($res->ResponseCode == 0)
                                {
                                    $service->notifying = 1;
                                    $service->save();
                                    return $res;
                                }

                        }
                    return false;
                }

            public function mpesa_view($identifier)
                {
                    try
                        {
                            $trans                        = Transaction::with(['subscription', 'payment_method'])->where('identifier',
                                                                                                                         $identifier)->firstOrFail();
                            $this->data['account']        = $trans->identifier;
                            $this->data['amount']         = $trans->amount;
                            $this->data['payment_method'] = $trans->payment_method;
                            $mpesa                        = new Mpesa(PaymentStageEnum::from($trans->payment_method->configuration['environment'])->name);
                            $mpesa->consumerkey           = $trans->payment_method->configuration['consumer_key'];
                            $mpesa->consumersecret        = $trans->payment_method->configuration["consumer_secret"];
                            $mpesa->shortcode             = $trans->payment_method->configuration["shortcode"];
                            $mpesa->trxcode               = 'PB';
                            $mpesa->merchantname          = $trans->payment_method->name;
                            $mpesa->amount                = ceil($trans->amount);
                            $mpesa->ref                   = $trans->identifier;
                            $mpesa->qrformat              = 1;
                            $mpesa->qrtype                = 'D';
                            $mpesa->size                  = 300;
                            $this->data['qr']             = $mpesa->qr();

                            return view('modules.front.mpesa', $this->data);
                        }
                    catch (\Exception $e)
                        {
                            Log::error('Mpesa Renewal', ['error' => $e->getMessage()]);
                        }


                }

            public function b2b(Request $request)
                {
                    $callbackJSONData                    = file_get_contents('php://input');
                    $callbackData                        = json_decode($callbackJSONData)->Result;
                    $resultCode                          = $callbackData->ResultCode;
                    $resultDesc                          = $callbackData->ResultDesc;
                    $originatorConversationID            = $callbackData->OriginatorConversationID;
                    $conversationID                      = $callbackData->ConversationID;
                    $transactionID                       = $callbackData->TransactionID;
                    $transactionReceipt                  = $callbackData->ResultParameters->ResultParameter[0]->Value;
                    $transactionAmount                   = $callbackData->ResultParameters->ResultParameter[1]->Value;
                    $b2CWorkingAccountAvailableFunds     = $callbackData->ResultParameters->ResultParameter[2]->Value;
                    $b2CUtilityAccountAvailableFunds     = $callbackData->ResultParameters->ResultParameter[3]->Value;
                    $transactionCompletedDateTime        = $callbackData->ResultParameters->ResultParameter[4]->Value;
                    $receiverPartyPublicName             = $callbackData->ResultParameters->ResultParameter[5]->Value;
                    $B2CChargesPaidAccountAvailableFunds = $callbackData->ResultParameters->ResultParameter[6]->Value;
                    $B2CRecipientIsRegisteredCustomer    = $callbackData->ResultParameters->ResultParameter[7]->Value;


                }

            public function b2c(Request $request)
                {
                    //$request->createFromGlobals();
                    $callbackJSONData                 = file_get_contents('php://input');
                    $callbackData                     = json_decode($callbackJSONData);
                    $resultCode                       = $callbackData->Result->ResultCode;
                    $resultDesc                       = $callbackData->Result->ResultDesc;
                    $originatorConversationID         = $callbackData->Result->OriginatorConversationID;
                    $conversationID                   = $callbackData->Result->ConversationID;
                    $transactionID                    = $callbackData->Result->TransactionID;
                    $initiatorAccountCurrentBalance   = $callbackData->Result->ResultParameters->ResultParameter[0]->Value;
                    $debitAccountCurrentBalance       = $callbackData->Result->ResultParameters->ResultParameter[1]->Value;
                    $amount                           = $callbackData->Result->ResultParameters->ResultParameter[2]->Value;
                    $debitPartyAffectedAccountBalance = $callbackData->Result->ResultParameters->ResultParameter[3]->Value;
                    $transCompletedTime               = $callbackData->Result->ResultParameters->ResultParameter[4]->Value;
                    $debitPartyCharges                = $callbackData->Result->ResultParameters->ResultParameter[5]->Value;
                    $receiverPartyPublicName          = $callbackData->Result->ResultParameters->ResultParameter[6]->Value;
                    $currency                         = $callbackData->Result->ResultParameters->ResultParameter[7]->Value;


                }

            public function validation(Request $request)
                {

                    $callbackJSONData  = file_get_contents('php://input');
                    $callbackData      = json_decode($callbackJSONData);
                    $transactionType   = optional($callbackData)->TransactionType;
                    $transID           = optional($callbackData)->TransID;
                    $transTime         = optional($callbackData)->TransTime;
                    $transAmount       = optional($callbackData)->TransAmount;
                    $businessShortCode = optional($callbackData)->BusinessShortCode;
                    $billRefNumber     = optional($callbackData)->BillRefNumber;
                    $invoiceNumber     = optional($callbackData)->InvoiceNumber;
                    $orgAccountBalance = optional($callbackData)->OrgAccountBalance;
                    $thirdPartyTransID = optional($callbackData)->ThirdPartyTransID;

                    $MSISDN     = optional($callbackData)->MSISDN;
                    $firstName  = optional($callbackData)->FirstName;
                    $middleName = optional($callbackData)->MiddleName;
                    $lastName   = optional($callbackData)->LastName;

                    $blacklist = MpesaBlacklist::where('phone', $MSISDN)->first();

                    if($blacklist)
                    {
                        return response()->json([
                            "ResultCode" => "C2B00015", "ResultDesc" => "Phone number blacklisted."
                        ]);
                    }

                    //Log::error('Validation: '.$callbackJSONData);
                    $cart = Cart::where('identifier', $billRefNumber)->whereNotNull('identifier')->first();

                    if ($cart)
                        {
                            UpdatedPhoneJob::dispatch($billRefNumber,$MSISDN);
                            $subscription = Subscription::where('cart_id', $cart->id)->first();

                            if ($subscription->created_at->lt(Carbon::now()->subDay()))
                            {
                                return response()->json([
                                    "ResultCode" => "C2B00014", "ResultDesc" => "Stale Transaction"
                                ]);
                            }

                            $sum = Transaction::whereHas('subscription', function ($query) use ($cart)
                                {
                                    $query->where('cart_id', $cart->id);
                                })->where('channel', 'like', '%mpesa%')->sum('amount');

                            if ((int)$transAmount >= (int)$sum)
                                {
                                    return response()->json([
                                                                "ResultCode" => "0", "ResultDesc" => "Accepted"
                                                            ]);
                                }
                            else
                                {
                                    //event(new PaymentFailed($billRefNumber, "Invalid Amount"));
                                    return response()->json([
                                                                "ResultCode" => "C2B00013", "ResultDesc" => "Invalid Amount"
                                                            ]);
                                }
                        }

                    $trans = Transaction::where('identifier', $billRefNumber)->first();

                    Log::info($trans);
                    if (!is_null($trans))
                        {
                            if(is_null($trans->phone))
                            {
                                UpdatedPhoneJob::dispatch($billRefNumber,$MSISDN);
                            }

                            if ((int)$trans->amount != (int)$transAmount)
                                {
                                    event(new PaymentFailed($trans->identifier, "Invalid Amount"));
                                    return response()->json([
                                                                "ResultCode" => "C2B00013", "ResultDesc" => "Invalid Amount"
                                                            ]);
                                }

                            if(!in_array($trans->currency, ['KES']))
                            {
                                return response()->json([
                                    "ResultCode" => "C2B00013", "ResultDesc" => "Invalid Currency"
                                ]);
                            }

                            $subscription = Subscription::find($trans->subscription_id);

                            if ($subscription && $subscription->created_at->lt(Carbon::now()->subDay()))
                            {
                                return response()->json([
                                    "ResultCode" => "C2B00014", "ResultDesc" => "Stale Transaction"
                                ]);
                            }
                            //	MpesaPaymentJob::dispatch($billRefNumber, $transAmount, $transID, $firstName, $MSISDN,$transTime, $callbackData)->onQueue('high');
                            return response()->json([
                                                        "ResultCode" => "0", "ResultDesc" => "Accepted"
                                                    ]);
                        }
                    else
                        {
                            event(new PaymentFailed($billRefNumber, "Invalid Account Number"));
                            return response()->json([
                                                        "ResultCode" => "C2B00012", "ResultDesc" => "Invalid Account Number"
                                                    ]);
                        }

                }

            public function confirmation(Request $request)
                {
                    //Log::error('confirm'.$request->getContent());

                    $callbackJSONData  = $request->getContent();
                    $callbackData      = json_decode($callbackJSONData);
                    $transactionType   = optional($callbackData)->TransactionType;
                    $transID           = optional($callbackData)->TransID;
                    $transTime         = optional($callbackData)->TransTime;
                    $transAmount       = optional($callbackData)->TransAmount;
                    $businessShortCode = optional($callbackData)->BusinessShortCode;
                    $billRefNumber     = optional($callbackData)->BillRefNumber;
                    $invoiceNumber     = optional($callbackData)->InvoiceNumber;
                    $orgAccountBalance = optional($callbackData)->OrgAccountBalance;
                    $thirdPartyTransID = optional($callbackData)->ThirdPartyTransID;
                    $MSISDN            = optional($callbackData)->MSISDN;
                    $firstName         = optional($callbackData)->FirstName;
                    $middleName        = optional($callbackData)->MiddleName;
                    $lastName          = optional($callbackData)->LastName;
                    //Log::debug('Confirmation: ' . $callbackJSONData);
                    $this->update_payment($billRefNumber, $transAmount, $transID, $firstName, $MSISDN, $transTime, $callbackJSONData);

                    return response()->json([
                                                "ResultCode" => 0, "ResultDesc" => "Success"
                                            ]);

                }

            public function update_payment($transcode, $amount, $receipt, $name, $number, $transtime, $response)
                {

                    try
                        {
                            $cart = Cart::where('identifier', $transcode)->first();

                            if ($cart)
                                {
                                    activate_mpesa_cart_subscription($cart, $transcode, $amount, $receipt, $name, $number, $transtime, $response);
                                    $cart->status = 1;
                                    $cart->save();
                                }
                            else
                                {
                                    $transaction = Transaction::with(['subscription.rate', 'user'])->where('identifier',
                                                                                                      $transcode)->first();

                                    if (!is_null($transaction))
                                        {

                                            try
                                                {
                                                    $kafka_data = [
                                                        'transaction'    => $transaction,
                                                        'user'           => $transaction->user,
                                                        'payment_method' => 'Mpesa',
                                                        'subscription'   => $transaction->subscription,
                                                    ];

                                                    DB::transaction(function () use ($kafka_data) {
                                                        SuccessPaymentEventJob::dispatch($kafka_data);
                                                    });
                                                }
                                            catch (\Exception $e)
                                                {
                                                    Log::error("Kafka successful payment", [$e->getMessage()]);
                                                }

                                            if ($transaction->amount <= $amount)
                                                {
                                                    if($transaction->status != 1)
                                                        {
                                                            $transaction->increment('amount_paid', $amount);
                                                            $transaction->status           = 1;
                                                            $transaction->receipt          = $receipt;
                                                            $transaction->initiator        = $name . ' - ' . $number;
                                                            $transaction->response         = $response;
                                                            $transaction->transaction_date = Carbon::parse($transtime)->toDateTimeString();
                                                            $res                           = $transaction->save();
                                                        }
                                                    else
                                                        {
                                                            $res = true;
                                                        }

                                                    if ($res)
                                                        {
                                                            $subscription = Subscription::find($transaction->subscription_id);

                                                            if ($transaction->subscription->status == 0)
                                                            {
                                                                $subscription->status = 1;
                                                                $subscription->save();
                                                            }
                                                            deactivate_after_upgrade($transaction->identifier);
                                                        }

                                                }
                                            else
                                                {
                                                    if($transaction->status != 1)
                                                        {
                                                            $transaction->decrement('amount', $amount);
                                                            $transaction->increment('amount_paid', $amount);
                                                            $transaction->receipt          = $receipt;
                                                            $transaction->initiator        = $name . ' - ' . $number;
                                                            $transaction->response         = $response;
                                                            $transaction->transaction_date = Carbon::parse($transtime)->toDateTimeString();
                                                            $res                           = $transaction->save();
                                                        }
                                                    else
                                                        {
                                                            $res = true;
                                                        }
                                                }
                                            if ($res)
                                                {
                                                    event(new PaymentMade($transaction));

//                                                    DB::transaction(function () use ($transaction) {
//                                                        ExtendBundleChildSubscriptions::dispatch($transaction->subscription->id);
//                                                    });
                                                    //Log::info('event fire');
                                                }
                                            else
                                                {
                                                    //Log::error('Transaction failed to update');
                                                    MpesaPaymentJob::dispatch($transcode, $amount, $receipt, $name, $number,
                                                                              $transtime, $response)->onQueue('high');
                                                }
                                            //notify callbacks
                                            $product = @$transaction->subscription->product;
                                            if($product)
                                            {
                                                $site = @$product->site;
                                                if(!is_null($site->callback_url))
                                                {
                                                    SendWebhook::dispatch($transaction);
                                                }
                                            }

                                        }
                                    else
                                        {
                                            Log::error('Transaction not found');
                                        }
                                }

                        }
                    catch (\Exception $e)
                        {
                            report($e);
                            //Log::info($e->getMessage());
                        }
                }

            public function account_balance(Request $request)
                {
                    $callbackJSONData = file_get_contents('php://input');

                    $callbackData             = json_decode($callbackJSONData);
                    $resultType               = $callbackData->Result->ResultType;
                    $resultCode               = $callbackData->Result->ResultCode;
                    $resultDesc               = $callbackData->Result->ResultDesc;
                    $originatorConversationID = $callbackData->Result->OriginatorConversationID;
                    $conversationID           = $callbackData->Result->ConversationID;
                    $transactionID            = $callbackData->Result->TransactionID;
                    $accountBalance           = $callbackData->Result->ResultParameters->ResultParameter[0]->Value;
                    $BOCompletedTime          = $callbackData->Result->ResultParameters->ResultParameter[1]->Value;


                }

            public function reversal(Request $request)
                {

                    $callbackJSONData = file_get_contents('php://input');

                    $callbackData             = json_decode($callbackJSONData);
                    $resultType               = $callbackData->Result->ResultType;
                    $resultCode               = $callbackData->Result->ResultCode;
                    $resultDesc               = $callbackData->Result->ResultDesc;
                    $originatorConversationID = $callbackData->Result->OriginatorConversationID;
                    $conversationID           = $callbackData->Result->ConversationID;
                    $transactionID            = $callbackData->Result->TransactionID;


                }

            public function stk_push_request(Request $request)
                {


                    $body = $request->getContent();
                    $callbackJSONData = json_decode($body, true);

                    $data = json_decode($body, false);

                    if(!property_exists($data, 'Body'))
                    {
                        return response()->json([
                            "ResultCode" => "0",
                            "ResultDesc" => "Success",
                        ]);
                    }

                    $data = $data->Body->stkCallback;
                    $checkoutRequestID = $data->CheckoutRequestID;
                    $transactions = Transaction::with('subscription.cart')->where('checkout_request_id', $checkoutRequestID)->get();

                    Transaction::where('checkout_request_id', $checkoutRequestID)->update(['result' => $data->ResultDesc]);

                    if($transactions->count() < 1)
                    {
                        return response()->json([
                            "ResultCode" => "0",
                            "ResultDesc" => "Success",
                        ]);
                    }

                    if($data->ResultCode != "0")
                    {
                        $initial = $transactions->first();
                        $payref = $initial->identifier;

                        if(!is_null(@$initial->subscription->cart))
                        {
                            $payref = $initial->subscription->cart->identifier;
                        }

                        event(new PaymentFailed($payref,"Stk cancelled!"));
                    }

                    if($data->ResultCode == "0")
                    {
                        $transAmount = 0;
                        $transID = null;
                        $firstName = null;
                        $MSISDN = null;
                        $transTime = null;

                        $items = $data->CallbackMetadata->Item;
                        foreach($items as $item){
                            switch($item->Name){
                                case "Amount":
                                    $transAmount = $item->Value;
                                    break;
                                case "MpesaReceiptNumber":
                                    $transID = $item->Value;
                                    break;
                                case "PhoneNumber":
                                    $MSISDN = $item->Value;
                                    break;
                                case "TransactionDate":
                                    $transTime = $item->Value;
                            }
                        }

                        $transTime = Carbon::createFromFormat('YmdHis', $transTime)->format('Y-m-d H:i:s');
                        $first =  $transactions->first();
                        $reference = $first->identifier;

                        $subscription = $first->subscription;
                        $cart_id =  @$subscription->cart_id;
                        if($cart_id)
                        {
                            $cart = Cart::find($cart_id);
                            $reference = $cart->identifier;
                        }

                        $billRefNumber = $reference;

                        $this->update_payment($billRefNumber, $transAmount, $transID, $firstName, $MSISDN, $transTime, $callbackJSONData);
                    }

                    return response()->json([
                        "ResultCode" => "0",
                        "ResultDesc" => "Success",
                    ]);
                }

            public function stk_push_query(Request $request)
                {
                    $callbackJSONData = file_get_contents('php://input');
                    Log::info('stk', $callbackJSONData);
                    $callbackData        = json_decode($callbackJSONData);
                    $responseCode        = $callbackData->ResponseCode;
                    $responseDescription = $callbackData->ResponseDescription;
                    $merchantRequestID   = $callbackData->MerchantRequestID;
                    $checkoutRequestID   = $callbackData->CheckoutRequestID;
                    $resultCode          = $callbackData->ResultCode;
                    $resultDesc          = $callbackData->ResultDesc;


                }

            public function transaction_status(Request $request)
                {
                    $callbackJSONData         = file_get_contents('php://input');
                    $callbackData             = json_decode($callbackJSONData);
                    $resultCode               = $callbackData->Result->ResultCode;
                    $resultDesc               = $callbackData->Result->ResultDesc;
                    $originatorConversationID = $callbackData->Result->OriginatorConversationID;
                    $conversationID           = $callbackData->Result->ConversationID;
                    $transactionID            = $callbackData->Result->TransactionID;
                    $ReceiptNo                = $callbackData->Result->ResultParameters->ResultParameter[0]->Value;
                    $ConversationID           = $callbackData->Result->ResultParameters->ResultParameter[1]->Value;
                    $FinalisedTime            = $callbackData->Result->ResultParameters->ResultParameter[2]->Value;
                    $Amount                   = $callbackData->Result->ResultParameters->ResultParameter[3]->Value;
                    $TransactionStatus        = $callbackData->Result->ResultParameters->ResultParameter[4]->Value;
                    $ReasonType               = $callbackData->Result->ResultParameters->ResultParameter[5]->Value;
                    $TransactionReason        = $callbackData->Result->ResultParameters->ResultParameter[6]->Value;
                    $DebitPartyCharges        = $callbackData->Result->ResultParameters->ResultParameter[7]->Value;
                    $DebitAccountType         = $callbackData->Result->ResultParameters->ResultParameter[8]->Value;
                    $InitiatedTime            = $callbackData->Result->ResultParameters->ResultParameter[9]->Value;
                    $OriginatorConversationID = $callbackData->Result->ResultParameters->ResultParameter[10]->Value;
                    $CreditPartyName          = $callbackData->Result->ResultParameters->ResultParameter[11]->Value;
                    $DebitPartyName           = $callbackData->Result->ResultParameters->ResultParameter[12]->Value;

                }

            public function mpesa_transaction_status(Request $request)
                {
                    $json = $request->getContent();
                    $data = json_decode($json);

                    if (!is_null(@$data->Result))
                        {
                            $conversation_id = @$data->Result->ConversationID;
                            $description     = @$data->Result->ResultDesc;
                            $account_number  = @$data->Result->ReferenceData->ReferenceItem->Value;

                            $cart = Cart::where('identifier', $account_number)->whereNotNull('identifier')->first();

                            if ($cart)
                                {
                                    $transaction = Transaction::whereHas('subscription', function ($query) use ($cart)
                                        {
                                            $query->where('cart_id', $cart->id);
                                        })->where('channel', 'like', '%mpesa%')->first();
                                }
                            else
                                {
                                    $transaction = Transaction::where('conversation_id', $conversation_id)->first();
                                }

                            $result_code = $data->Result->ResultCode;
                            if ($result_code == '0' && @$transaction->status == 0)
                                {

                                    $result_params = @$data->Result->ResultParameters->ResultParameter ?? [];

                                    $billRefNumber    = $account_number;
                                    $name             = '';
                                    $status           = '';
                                    $transAmount      = '';
                                    $transID          = '';
                                    $transTime        = '';
                                    $callbackJSONData = $json;

                                    foreach ($result_params as $param)
                                        {
                                            switch ($param->Key)
                                                {
                                                case "DebitPartyName":
                                                        $name = $param->Value;
                                                        break;
                                                case "TransactionStatus":
                                                        $status = $param->Value;
                                                        break;
                                                case "Amount":
                                                        $transAmount = $param->Value;
                                                        break;
                                                case "ReceiptNo":
                                                        $transID = $param->Value;
                                                        break;
                                                case "FinalisedTime":
                                                        $transTime = $param->Value;
                                                        break;
                                                }
                                        }
                                    $firstName = @explode("-", $name)[1];
                                    $firstName = trim($firstName);
                                    $MSISDN    = @explode("-", $name)[0];
                                    $MSISDN    = trim($MSISDN);

                                    $existing = Transaction::where('receipt', $transID)->first();

                                    if ($status == 'Completed' && !$existing && date_create($transaction->created_at) < date_create($transTime))
                                        {
                                            $this->update_payment($billRefNumber, $transAmount, $transID, $firstName, $MSISDN, $transTime,
                                                                  $callbackJSONData);

                                            if ($cart)
                                                {
                                                    Transaction::whereHas('subscription', function ($query) use ($cart)
                                                        {
                                                            $query->where('cart_id', $cart->id);
                                                        })->where('channel', 'like', '%mpesa%')->update(['result' => $description]);
                                                }
                                            else
                                                {
                                                    $transaction->result = $description;
                                                    $transaction->save();
                                                }

                                        }
                                    else
                                        {
                                            if ($cart)
                                                {
                                                    Transaction::whereHas('subscription', function ($query) use ($cart)
                                                        {
                                                            $query->where('cart_id', $cart->id);
                                                        })->where('channel', 'like', '%mpesa%')->update(['result' => 'Suspicious Attempt']);
                                                }
                                            else
                                                {
                                                    $transaction->result = 'Suspicious Attempt';
                                                    $transaction->save();
                                                }
                                        }
                                }
                            else
                                {
                                    if ($cart)
                                        {
                                            Transaction::whereHas('subscription', function ($query) use ($cart)
                                                {
                                                    $query->where('cart_id', $cart->id);
                                                })->where('channel', 'like', '%mpesa%')->update(['result' => $description]);
                                        }
                                    else
                                        {
                                            $transaction->result = $description;
                                            $transaction->save();
                                        }
                                }
                        }
                }

            public function queue_timeout(Request $request)
                {
                }

        }
