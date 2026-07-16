<?php


namespace App\Http\Controllers\API;

use AmrShawky\LaravelCurrency\Facade\Currency;
use App\Enums\PaymentStageEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\B2bSubscriptionResource;
use App\Http\Resources\CorporateNotificationUserResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RateResource;
use App\Http\Resources\SubscriptionResource;
use App\Jobs\ExtendBundleChildSubscriptions;
use App\Jobs\Kafka\STKPushEventJob;
use App\Jobs\StkQueryJob;
use App\Libs\BillingLibrary;
use App\Libs\DPO;
use App\Libs\Mpesa;
use App\Models\B2bSubscription;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Lead;
use App\Models\MediaEvent;
use App\Models\PaymentMeta;
use App\Models\PaymentMethod;
use App\Models\Point;
use App\Models\PointHistory;
use App\Models\Product;
use App\Models\Rate;
use App\Models\Reason;
use App\Models\Region;
use App\Models\Site;
use App\Models\Subscription;
use App\Models\SubscriptionGroup;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use stdClass;
use function Symfony\Component\Translation\t;


class BillingController extends Controller
    {
        public function __construct(protected DPO $dpo)
            {
            }

        public function check_mpesa_payment(Request $request)
            {

                if (!$request->has('account'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'account not set!!'
                        ]);
                    }

                $cart = Cart::where('identifier', $request->account)->first();

                if ($cart)
                    {
                        $transactions = Transaction::with(['subscription', 'user'])
                                                   ->whereHas('subscription', function ($query) use ($cart)
                                                       {
                                                           $query->where('cart_id', $cart->id);
                                                       })->where('channel', 'like', '%mpesa%')->get();

                        $status = 0;
                        $trans  = null;
                        foreach ($transactions as $transaction)
                            {
                                $transaction->identifier = $cart->identifier;

                                if ($transaction->status == 1)
                                    {
                                        $status = 1;
                                        $trans  = $transaction;
                                    }

                            }

                        if ($status && $transactions->count() > 0)
                            {
                                return [
                                    'status' => true, 'subscription' => true, 'data' => new SubscriptionResource($trans->subscription), 'transaction_code' => $trans->identifier, 'SubscriptionActivated' => true
                                ];
                            }
                        return [
                            'status' => false, 'subscription' => false, 'data' => null, 'transaction_code' => $request->account, 'SubscriptionActivated' => false
                        ];
                    }

                $transaction = Transaction::where('identifier', $request->account)->first();
                if (is_null($transaction))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'Transactional account not found'
                        ]);
                    }
                else
                    {
                        if ($transaction->status == 0)
                            {
                                if ($request->has('checkout_request_id'))
                                    {
                                        $mpesa                    = new Mpesa();
                                        $mpesa->consumerkey       = $transaction->payment_method->configuration['consumer_key'];
                                        $mpesa->consumersecret    = $transaction->payment_method->configuration["consumer_secret"];
                                        $mpesa->shortcode         = $transaction->payment_method->configuration["shortcode"];
                                        $mpesa->CheckoutRequestID = $request->checkout_request_id;
                                        $mpesa->checkout_query();
                                    }

                                return [
                                    'status' => false, 'subscription' => false, 'data' => $transaction->response, 'transaction_code' => $transaction->identifier, 'SubscriptionActivated' => false
                                ];
                            }
                        else
                            {
                                return [
                                    'status' => true, 'subscription' => true, 'data' => new SubscriptionResource($transaction->subscription), 'transaction_code' => $transaction->identifier, 'SubscriptionActivated' => true
                                ];
                            }
                    }
            }

        public function verify_token()
            {
                try
                    {

                        $transactions = Transaction::with([
                            'subscription', 'subscription.rate'
                        ])->where('status',
                            0)->whereNotNull('transaction_token')->where('created_at',
                            '>=',
                            '2023-08-11 00:00:00')->get();

                        foreach ($transactions as $trans)
                            {
                                if (is_null($trans->transaction_token))
                                    {
                                        print_r($trans);
                                        continue;

                                    }

                                //dd($trans);
                                $this->dpo->transaction_token = optional($trans)->transaction_token;
                                $this->dpo->company_token     = optional($trans)->payment_method->configuration['company_token'];
                                $this->dpo->accountref        = optional($trans)->identifier;
                                $statusResult                 = $this->dpo->verifyToken();

                                $statusCode = simplexml_load_string($statusResult);
                                //dd($trans);
                                //Log::error($statusResult);
                                if ($statusCode->Result == '000')
                                    {
                                        //dd($this->currency_convert($statusCode->TransactionAmount, $statusCode->TransactionCurrency, $trans->currency));
                                        try
                                            {
                                                $trans->amount_paid      = $trans->amount;
                                                $trans->status           = 1;
                                                $trans->receipt          = (string)$statusCode->TransactionApproval ?? '';
                                                $trans->initiator        = $statusCode->CustomerName ?? '';
                                                $trans->transaction_date = \Illuminate\Support\Carbon::parse($statusCode->TransactionSettlementDate)->toDateTimeString();
                                                //$trans->response         = json_encode($statusCode);
                                                $res = $trans->save();
                                                if ($res)
                                                    {

                                                        //Log::info($trans);
                                                        $trans->subscription()->where('id',
                                                            $trans->subscription_id)->update(['status' => 1]);
                                                        if ($trans->subscription->recurring == 1)
                                                            {
                                                                $subtoken                = new DPO();
                                                                $subtoken->company_token = $trans->payment_method->configuration['company_token'];
                                                                $subtoken->email         = $trans->user->email;
                                                                $result                  = $subtoken->retrieveTokenSub();
                                                                $resultCode              = simplexml_load_string($result);
                                                                $ata                     = [];
                                                                $ata['status']           = 1;

                                                                //Log::error($result);

                                                                if ($resultCode->Result == '000')
                                                                    {
                                                                        $ata['subscription_token'] = $resultCode->subscriptionToken;
                                                                    }

                                                                if ($trans->subscription->reccurent_cycle > 0 && strtolower($trans->rate->name) != 'archive')
                                                                    {
                                                                        $ata['subscription_date'] = Carbon::parse($trans->subscription->subscription_date)->startOfDay();
                                                                        $ata['expiry_date']       = Carbon::parse($trans->subscription->subscription_date)->addDays($trans->subscription->rate->period)->endOfDay();

                                                                    }
                                                                $trans->subscription()->where('id',
                                                                    $trans->subscription_id)->update($ata);
                                                                //Log::error($trans->subscription->refresh());

                                                                $trans->subscription->metadata()->insert([
                                                                    'start_date' => Carbon::now()->startOfDay(), 'next_renewal_date' => Carbon::now()->addDays($trans->subscription->rate->period + 1)->startOfDay(), 'expiry_date' => Carbon::now()->addDays($trans->rate->period)->endOfDay()
                                                                ]);
                                                            }


                                                    }
                                                //dd($trans);
                                            }
                                        catch (Exception $e)
                                            {
                                                echo $e->getMessage();
                                            }

                                    }
                            }
                    }
                catch (Exception $e)
                    {
                        Log::error($e->getMessage());
                    }

            }

        public function email_renewal($identifier)
            {
                $sub = Subscription::where('identifier', $identifier)->first();

                $transaction = Transaction::where('subscription_id', $sub->id)->where('status',
                    1)->orderBy('created_at',
                    'desc')->first();

                if (!is_null($sub) && !is_null($transaction))
                    {
                        $start_date  = Carbon::now()->startOfDay();
                        $expiry_date = Carbon::parse($sub->expiry_date)->startOfDay();
                        if ($expiry_date > Carbon::now()->startOfDay())
                            {
                                $start_date = $expiry_date;
                            }

                        $new_sub                        = new Subscription();
                        $new_sub->identifier            = self::identifer('Subscription', 'identifier', 8);
                        $new_sub->user_id               = $sub->user_id;
                        $new_sub->product_id            = $sub->product_id;
                        $new_sub->rate_id               = $sub->rate_id;
                        $new_sub->subscription_group_id = $sub->subscription_group_id;
                        $new_sub->subscription_date     = $start_date;
                        $new_sub->reccurent_cycle       = 0;
                        $new_sub->reccuring             = $sub->reccuring;
                        $new_sub->expiry_date           = Carbon::parse($new_sub->subscription_date)->addDays($sub->rate->period)->startOfDay();
                        $new_sub->status                = 0;
                        $new_sub->activator_id          = $sub->activator_id;
                        $new_sub->save();

                        if (!is_null($transaction))
                            {
                                $trans                    = new Transaction();
                                $trans->identifier        = self::identifer('Transaction', 'identifier', 8);
                                $trans->type              = 'recurrent';
                                $trans->subscription_id   = $new_sub->id;
                                $trans->payment_method_id = $transaction->payment_method_id;
                                $trans->{'channel'}       = $transaction->channel;
                                $trans->discount          = 0;
                                $trans->currency          = $sub->rate->currency;
                                $trans->total_amount      = $sub->rate->cost;
                                $trans->amount            = $sub->rate->cost;
                                $trans->user_id           = $transaction->user_id;
                                $trans->redirect_url      = $transaction->redirect_url;
                                $trans->back_url          = $transaction->back_url;
                                $trans_save               = $trans->save();
                                if ($trans_save)
                                    {
                                        $sub->increment('reccurent_cycle');
                                        $sub->save();
                                        $region = Region::find($sub->rate->region_id);

                                        $bill = BillingLibrary::payment($new_sub, $trans, $new_sub->user,
                                            $trans->payment_method, 0, $region,
                                            $new_sub->rate->cost, $new_sub->rate->currency,
                                            $trans->back_url ?? $new_sub->product->site->site_url,
                                            $trans->redirect_url ?? $new_sub->product->site->site_url);
                                        if (isset($bill['data']))
                                            {
                                                return redirect($bill['data']);
                                            }

                                    }

                            }

                    }
            }

        public function purchase_subscription(Request $request)
            {

                $this->data['cart'] = Cart::with([
                    'items', 'items.rate', 'items.rate.product', 'items.rate.rate_type'
                ])->find($request->cart_id);

                return response()->view('modules.front.cart', $this->data);
            }

        public function getPaymentMethods()
            {

                $paymentmethods = PaymentMethod::whereStatus(1)->get();

                return response()->json([
                    'status' => true, 'data' => PaymentMethodResource::collection($paymentmethods)
                ]);
            }

        public function getRegions(Request $request)
            {

                if (!$request->has('start'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'start not set!!'
                        ]);
                    }
                if (!$request->has('end'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'end not set!!'
                        ]);
                    }
                if (!$request->has('orderBy'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'orderBy column not set!!'
                        ]);
                    }
                if (!$request->has('orderFormat'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'orderFormat not set'
                        ]);
                    }
                $region = Region::offset($request->start)->limit($request->end)->orderBy($request->orderBy,
                    $request->orderFormat)->get();

                return response()->json([
                    'status' => true, 'data' => $region
                ]);
            }

        public function getRegionDetails($iso2)
            {

                $region = Region::where('code', $iso2)->first();

                return response()->json([
                    'status' => true, 'data' => $region
                ]);
            }

        public function getCouponDetails(Request $request)
            {
                try
                    {
                        $detail = new stdClass();
                        $amount = $request->amount;
                        $sub    = Transaction::where('user_id', Auth::user()->id)->where('coupon_code',
                            $request->coupon_code)->where('status',
                            1)->first();
                        $rate   = Rate::where('status', 1)->find($request->rate_id);
                        //Log::debug("rate: " . $rate);
                        $region = Region::where('code', $request->region)->first();
                        //Log::debug("region: " . $region);
                        if (is_null($sub))
                            {

                                $coupon = Coupon::where('code', $request->coupon_code)->where('start_date', '<=',
                                    Carbon::now()->toDateTimeString())->where('expiry_date',
                                    '>=',
                                    Carbon::now()->toDateTimeString())->where('rate_type',
                                    $rate->id)->where('region_id',
                                    $region->id)->whereJsonContains('products',
                                    (string)$rate->product_id)->first();
                                //Log::debug('Coupon : ' . $coupon);
                                if (!is_null($coupon))
                                    {
                                        if ($coupon->type == 0)
                                            {
                                                $disc = floor(($coupon->discount / 100) * $amount);
                                                $cost = $amount - $disc;
                                            }
                                        else
                                            {
                                                $disc = ($coupon->discount);
                                                $cost = $amount - $disc;
                                            }
                                        $detail->discount     = $disc;
                                        $detail->amount       = $cost;
                                        $detail->total_amount = $amount;
                                        return response()->json([
                                            'status' => true, 'data' => 'This coupon code is avaliable for this user', 'detail' => $detail
                                        ]);
                                    }
                                else
                                    {
                                        $detail->discount     = 0;
                                        $detail->amount       = $amount;
                                        $detail->total_amount = $amount;
                                        return response()->json([
                                            'status' => false, 'data' => 'This coupon code is expired', 'detail' => $detail
                                        ]);
                                    }

                            }
                        else
                            {
                                $detail->discount     = 0;
                                $detail->amount       = $amount;
                                $detail->total_amount = $amount;
                                return response()->json([
                                    'status' => false, 'data' => 'already used this coupon code', 'detail' => $detail
                                ]);
                            }
                    }
                catch (Exception $e)
                    {
                        Log::error($e->getMessage());
                        $detail->discount     = 0;
                        $detail->amount       = $amount;
                        $detail->total_amount = $amount;
                        return response()->json([
                            'status' => false, 'data' => $e->getMessage(), 'detail' => $detail
                        ]);
                    }


            }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
        public function stkpush(Request $request)
            {
                try
                    {

                        $request->validate([
                            'identifier' => 'required',
                            'msisdn'     => 'required',
                            'amount'     => 'required|numeric',
                            'account'    => 'required',
                        ]);

                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['status' => false, 'data' => $e->validator->errors()->first()]);
                    }

                $pay = PaymentMethod::where('identifier', $request->identifier)->first();

                $msisdn = $request->msisdn;
                if (str_starts_with($msisdn, '0'))
                    $msisdn = preg_replace('/0/', '254', $msisdn, 1);


                if (str_starts_with($msisdn, '+'))
                    $msisdn = preg_replace('/\+/', '', $msisdn, 1);

                if (!is_null($pay))
                    {
                        if ($pay->provider == 'mpesa')
                            {

                                $mpesa                 = new Mpesa(PaymentStageEnum::from($pay->configuration['environment'])->name);
                                $mpesa->consumerkey    = $pay->configuration['consumer_key'];
                                $mpesa->consumersecret = $pay->configuration['consumer_secret'];
                                $mpesa->passkey        = $pay->configuration['pass_key'];
                                $mpesa->shortcode      = $pay->configuration['shortcode'];
                                $mpesa->msisdn         = $msisdn;
                                $mpesa->amount         = (int)$request->amount;
                                $mpesa->ref            = $request->account;
                                $mpesa->desc           = 'Payment of ' . $request->product;
                                $mpesa->type           = $pay->type;
                                $response              = $mpesa->stkpush();


                                $cart = Cart::where('identifier', $request->account)
                                            ->when($request->has('cart_id'), function ($query) use ($request)
                                                {
                                                    return $query->where('id', $request->cart_id);
                                                })
                                            ->first();

                                if ($cart)
                                    {
                                        $transactions = Transaction::with(['subscription', 'user'])
                                                                   ->whereHas('subscription', function ($query) use ($cart)
                                                                       {
                                                                           $query->where('cart_id', $cart->id);
                                                                       })->get();

                                        $trans = null;
                                        foreach ($transactions as $transaction)
                                            {
                                                $transaction->response            = $response;
                                                $transaction->phone               = $msisdn;
                                                $transaction->source              = 'stkpush';
                                                $transaction->checkout_request_id = @$response->CheckoutRequestID;
                                                $transaction->save();

                                                $kafka_data = [
                                                    "transaction" => $transaction,
                                                    "account"     => $request->account,
                                                    "shortcode"   => $pay->configuration['shortcode'],
                                                    "amount"      => $request->amount,
                                                    "msisdn"      => $request->msisdn,
                                                    "response"    => $response
                                                ];
                                                STKPushEventJob::dispatch($kafka_data);

                                                $trans = $transaction;
                                            }

                                        $trans->identifier = $request->account;
                                        $trans->amount     = $request->amount;

//                    StkQueryJob::dispatch($response, $trans)
//                        ->delay(now()->addSeconds(15))
//                        ->onQueue('high');

                                    }
                                else
                                    {
                                        $transaction = Transaction::where('identifier',
                                            $request->account)->first();

                                        $transaction->response            = $response;
                                        $transaction->phone               = $msisdn;
                                        $transaction->source              = 'stkpush';
                                        $transaction->checkout_request_id = @$response->CheckoutRequestID;
                                        $transaction->save();
                                        try
                                            {
                                                $kafka_data = [
                                                    "transaction" => $transaction,
                                                    "account"     => $request->account,
                                                    "shortcode"   => $pay->configuration['shortcode'],
                                                    "amount"      => $request->amount,
                                                    "msisdn"      => $request->msisdn,
                                                    "response"    => $response
                                                ];

                                                DB::transaction(function () use ($kafka_data)
                                                    {
                                                        STKPushEventJob::dispatch($kafka_data);
                                                    });
                                            }
                                        catch (\Exception $e)
                                            {
                                                Log::error("Kafka Stk", [$e->getMessage()]);
                                            }

                                        StkQueryJob::dispatch($response, $transaction)
                                                   ->delay(now()->addSeconds(15))
                                                   ->onQueue('high');
                                    }

                                return response()->json([
                                    'status' => true, 'data' => $response
                                ]);

                            }

                        return response()->json([
                            'status' => false, 'data' => 'This is not an mpesa payment method'
                        ]);
                    }

                return response()->json([
                    'status' => false, 'data' => 'No payment method found'
                ]);

            }

        public function getProducts(Request $request)
            {

                try
                    {
                        $start = $request->start ?? 0;
                        $end   = $request->end ?? 10;

                        $products = Site::with([
                            'region',
                            'products'      => function ($query) use ($request)
                                {
                                    $query->where('status', 1)->when($request->has('product'), function ($query) use ($request)
                                        {
                                            $query->where('identifier', $request->product);
                                        });
                                },
                            'otherProducts' => function ($query)
                                {
                                    $query->with(['rates' => function ($query)
                                        {
                                            $query->where('status', 1);
                                        }])->where('status', 1)->whereHas('rates', function ($query)
                                        {
                                            $query->where('status', 1);
                                        });
                                },
                            'products.rates',
                            'products.children'
                        ])->whereHas('products.rates', function ($q)
                            {
                                $q->whereStatus(1);

                            })->whereHas('products', function ($q) use ($request)
                            {
                                $q->where(function ($query) use ($request)
                                    {
                                        $query->whereStatus(1)->orWhere('type', $request->type);
                                    })->when($request->has('product'), function ($query) use ($request)
                                    {
                                        $query->where('identifier', $request->product);
                                    });

                            })->when($request->has('region'), function ($q) use ($request)
                            {
                                return $q->whereHas('region', function ($query) use ($request)
                                    {

                                        return $query->where('name', $request->region)->orWhere('code',
                                            $request->region);
                                    });
                            })->when($request->has('site'), function ($q) use ($request)
                            {

                                return $q->where('site_name', $request->site);

                            })->offset($start)->limit($end)->get();

                        $products = $products->map(function ($product)
                            {
                                $product->products = $product->products->merge($product->otherProducts);
                                return $product;
                            });

                        //dd($products);

                        return response()->json([
                            'status' => true, 'data' => ProductResource::collection($products)
                        ]);
                    }
                catch (Exception $e)
                    {
                        return response()->json([
                            'status' => false, 'data' => $e->getMessage()
                        ]);
                    }

            }


        public function getUserSubscription(Request $request)
            {

                try
                    {
                        $user = User::with([
                            'whitelist', 'organization', 'organization.whitelist'
                        ])->find(Auth::user()->id);

                        $product = Product::where('identifier', $request->product)->orWhere('id',
                            $request->product)->first();

                        $is_seasonal_product = in_array($request->product, ['EP_THE_EAST_AFRICAN', 'EA-KE']);
                        $is_compensatable    = (Carbon::parse($request->subscription_date)->gte(today()->subDays(6)) && Carbon::parse($request->subscription_date)->lt(today()));

                        if (!is_null($product))
                            {
                                if (!(bool) $product->is_premium)
                                    {
                                        return [
                                            'identifier' => 'free', 'product' => $product->product_name, 'productIdentifier' => $product->identifier, 'type' => 'free', 'period' => 1, 'subscriptionDate' => Carbon::parse($request->subscription_date)->startOfDay()->toDateTimeString(), 'expiryDate' => Carbon::parse($request->subscription_date)->endOfDay()->toDateTimeString(), 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => 'N/A', 'SubscriptionActivated' => true
                                        ];
                                    }
                                else
                                    {
                                        //check for user whitelist
                                        $check_whitelist = $user
                                            ->whitelist()
                                            ->with([
                                                'product',
                                                'products' => function ($q) use ($product)
                                                    {
                                                        $q->where('product_id', $product->id);
                                                    }
                                            ])
                                            ->where(function ($q) use ($product)
                                                {
                                                    $q
                                                        ->where('product_id', $product->id)
                                                        ->orWhereHas('products', function ($q) use ($product)
                                                            {
                                                                $q->where('product_id', $product->id);
                                                            });
                                                })->where('status', 1)
                                            ->whereDate('startdate', '<=', Carbon::now()->startOfDay()->toDateTimeString())
                                            ->where('enddate', '>=', Carbon::now()->startOfDay()->toDateTimeString())->first();
                                        if (!is_null($check_whitelist))
                                            {
                                                if ($check_whitelist->products->count() > 0)
                                                    {
                                                        $check_whitelist->product = $check_whitelist->products->first();
                                                    }

                                                return [
                                                    'identifier' => 'whitelisted', 'model' => 'user', 'product' => $check_whitelist->product->product_name, 'productIdentifier' => $check_whitelist->product->identifier, 'type' => 'whitelist', 'period' => Carbon::parse($check_whitelist->enddate)->diffInDays(Carbon::parse($check_whitelist->startdate)), 'subscriptionDate' => $check_whitelist->startdate, 'expiryDate' => $check_whitelist->enddate, 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => 'N/A', 'SubscriptionActivated' => true
                                                ];
                                            }

                                        if ($user->organization_id > 0)
                                            {
                                                $check_whitelist = $user->organization
                                                    ->whitelist()
                                                    ->with([
                                                        'product',
                                                        'products' => function ($q) use ($product)
                                                            {
                                                                $q->where('product_id', $product->id);
                                                            }
                                                    ])
                                                    ->where(function ($q) use ($product)
                                                        {
                                                            $q
                                                                ->where('product_id', $product->id)
                                                                ->orWhereHas('products', function ($q) use ($product)
                                                                    {
                                                                        $q->where('product_id', $product->id);
                                                                    });
                                                        })
                                                    ->where('status', 1)
                                                    ->whereDate('startdate',
                                                        '<=',
                                                        Carbon::now()->startOfDay()->toDateTimeString())->where('enddate',
                                                        '>=',
                                                        Carbon::now()->startOfDay()->toDateTimeString())->first();

                                                if (!is_null($check_whitelist))
                                                    {
                                                        if ($check_whitelist->products->count() > 0)
                                                            {
                                                                $check_whitelist->product = $check_whitelist->products->first();
                                                            }

                                                        return [
                                                            'identifier' => 'whitelisted', 'model' => 'organization', 'product' => $check_whitelist->product->product_name, 'productIdentifier' => $check_whitelist->product->identifier, 'type' => 'whitelist', 'period' => Carbon::parse($check_whitelist->enddate)->diffInDays(Carbon::parse($check_whitelist->startdate)), 'subscriptionDate' => $check_whitelist->startdate, 'expiryDate' => $check_whitelist->enddate, 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => 'N/A', 'SubscriptionActivated' => true
                                                        ];
                                                    }
                                                else
                                                    {
                                                        $subscription = B2bSubscription::with([
                                                            'product',
                                                            'products' => function ($q) use ($product)
                                                                {
                                                                    $q->where('product_id', $product->id);
                                                                }
                                                        ])->when($request->has('product'),
                                                            function ($q) use ($product)
                                                                {
                                                                    return $q->where(function ($q) use ($product)
                                                                        {
                                                                            $q
                                                                                ->where('product_id', $product->id)
                                                                                ->orWhereHas('products', function ($q) use ($product)
                                                                                    {
                                                                                        $q->where('product_id', $product->id);
                                                                                    });
                                                                        });
                                                                })->where('organization_id',
                                                            $user->organization_id)->where('status',
                                                            1)
                                                                                       ->when($request->has('subscription_date'),
                                                                                           function ($query) use ($request)
                                                                                               {
                                                                                                   return $query
                                                                                                       ->whereDate('start_date', '<=',
                                                                                                           Carbon::parse($request->subscription_date)->startOfDay()->toDateTimeString())
                                                                                                       ->where('expiry_date',
                                                                                                           '>=',
                                                                                                           Carbon::parse($request->subscription_date)->toDateTimeString());
                                                                                               })->get();

                                                        if ($subscription->isNotEmpty())
                                                            {
                                                                $subscription->map(function ($sub) use ($request)
                                                                    {
                                                                        if ($sub->products->count() > 0)
                                                                            {
                                                                                $sub->product = $sub->products->first();
                                                                            }
                                                                        return $sub;
                                                                    });

                                                                return response()->json([
                                                                    'status' => true, 'data' => B2bSubscriptionResource::collection($subscription)
                                                                ]);
                                                            }
                                                    }
                                            }

                                        $subscription_date = Carbon::parse($request->subscription_date)->toDateTimeString();

                                        if ($product->type == 'paywall')
                                            {
                                                $subscription_date = Carbon::parse($request->subscription_date)->toDateTimeString();
                                            }

                                        if ($request->has('article_id'))
                                            {
                                                //dd($request->article_id,Auth::user()->id);
                                                $article_subscriptions = Subscription::with([
                                                    'rate', 'transaction', 'product', 'products'
                                                ])->where('status', 1)->where('article_id', $request->article_id)
                                                                                     ->where('user_id', Auth::user()->id)
                                                                                     ->where('expiry_date', '>=', $subscription_date)
                                                                                     ->get();

                                                if ($article_subscriptions->isNotEmpty())
                                                    {
                                                        return response()->json([
                                                            'status' => true, 'data' => SubscriptionResource::collection($article_subscriptions)
                                                        ]);
                                                    }
                                            }

                                        $subscription = Subscription::with([
                                            //'product',
                                            'rate', 'transaction', 'product', 'products' => function ($q) use ($product)
                                                {
                                                    $q->where('product_id', $product->id);
                                                }
                                        ])->when($request->has('product'),
                                            function ($q) use ($product)
                                                {
                                                    return $q->where(function ($q) use ($product)
                                                        {
                                                            $q
                                                                ->Where('product_id', $product->id)
                                                                ->orWhereHas('products', function ($q) use ($product)
                                                                    {
                                                                        $q->where('product_id', $product->id);
                                                                    });
                                                        });
                                                })
                                                                    ->where('user_id', Auth::user()->id)
                                                                    ->where('status', 1)
                                                                    ->when($request->has('subscription_date'),
                                                                        function ($query) use ($request, $subscription_date, $product, $is_compensatable, $is_seasonal_product)
                                                                            {
                                                                                return $query
                                                                                    ->when(!($is_seasonal_product && $is_compensatable), function ($query) use ($subscription_date)
                                                                                        {
                                                                                            $query->whereDate('subscription_date', '<=', $subscription_date);
                                                                                        })
                                                                                    ->where(function ($query) use ($subscription_date, $product)
                                                                                        {
                                                                                            if ($product->type == 'paywall')
                                                                                                return $query->where('expiry_date', '>=', $subscription_date);

                                                                                            $query->whereDate('expiry_date', '>=', $subscription_date);
                                                                                        });
                                                                            })
                                                                    ->whereNull('article_id')
                                                                    ->get();

                                        //dd($product,$subscription[0]->products);

                                        foreach ($subscription as $key => $subsc)
                                            {
                                                if (($subsc->product->children->count() > 0) && ($subsc->rate->period == 1) && ($product->type == 'epaper'))
                                                    {
                                                        if (
                                                            Carbon::parse($subsc->expiry_date)->toDateString() !== Carbon::parse($subsc->subscription_date)->toDateString()
                                                            && Carbon::parse($request->subscription_date)->toDateString() == Carbon::parse($subsc->expiry_date)->toDateString()
                                                        )
                                                            {
                                                                $subscription->forget($key);
                                                            }
                                                    }
                                            }

                                        if ($subscription->isNotEmpty())
                                            {
                                                $subscription->map(function ($sub) use ($request)
                                                    {
                                                        if ($sub->products->count() > 0)
                                                            {
                                                                $sub->product = $sub->products->first();
                                                            }
                                                        $sub['subdate'] = $request->subscription_date;
                                                        return $sub;
                                                    });

                                                return response()->json([
                                                    'status' => true, 'data' => SubscriptionResource::collection($subscription)
                                                ]);
                                            }
                                    }

                                return response()->json([
                                    'status' => false, 'data' => 'No subscription found', 'subscriptionStatus' => 'N/A', 'SubscriptionActivated' => false
                                ]);
                            }
                        else
                            {
                                return response()->json([
                                    'status' => false, 'data' => 'Product Not found', 'subscriptionStatus' => 'N/A', 'SubscriptionActivated' => false
                                ]);
                            }

                    }
                catch (Exception $e)
                    {
                        return response()->json([
                            'status' => false, 'data' => $e->getMessage(), 'subscriptionStatus' => 'N/A', 'SubscriptionActivated' => false
                        ]);
                    }
            }

        public function getRates(Request $request, string $prod)
            {
                $is_archive = false;

                try
                    {
                        $product = Product::with(['site.region'])->where('identifier', $prod)->first();
                        $start   = $request->start ?? 0;
                        $end     = $request->end ?? 10;

                        if (!$product)
                            return response()->json([
                                'status' => false, 'data' => 'No product found',
                            ]);

                        if ($request->has('release_date') && $product->type == 'epaper')
                            {
                                if (date_create($request->release_date)->format('Y-m-d') <= date_create('now')->sub(new \DateInterval('P' . $product->archive_days . 'D'))->format('Y-m-d'))
                                    $is_archive = true;

                                if ($is_archive)
                                    {
                                        $today    = now()->format('D');
                                        $skipdays = $product->archive_skip_days;
                                        $skipdays = explode(',', $skipdays);
                                        $skipdays = array_map(function ($day)
                                            {
                                                return trim($day);
                                            }, $skipdays);

                                        $release_date = Carbon::parse($request->release_date)->addDays(count($skipdays))->startOfDay();
                                        $now          = Carbon::now()->startOfDay();

                                        if (in_array($today, $skipdays) && $release_date->gte($now))
                                            $is_archive = false;
                                    }
                            }

                        $region   = $product->site->region;
                        $currency = $region->currency_code;

                        if (!is_null($request->region))
                            {
                                $currency = resolve_currency($request->region, $currency);
                            }

                        $exemptions       = [];
                        $prorate          = 0;
                        $additional_rates = [];
                        //if user has BD subscription yearly sub no upgrade option.
                        if (\auth()->user() && $request->has('upgrade') && $request->upgrade == "true")
                            {
                                $user  = \auth()->user();
                                $rates = Rate::where('product_id', $product->id)->where('type', 'individual')->where('status', 1)
                                             ->whereNotIn('name', ['article'])->orderBy('period', 'desc')->get();

                                $subscription = Subscription::with(['rate'])->where('user_id', $user->id)
                                                            ->where(function ($q) use ($product)
                                                                {
                                                                    $q
                                                                        ->where('product_id', $product->id)
                                                                        ->orWhereHas('products', function ($q) use ($product)
                                                                            {
                                                                                $q->where('product_id', $product->id);
                                                                            });
                                                                })
                                                            ->where('status', 1)
                                                            ->where('expiry_date', '>=', date_create('now')->format('Y-m-d H:i:s'))
                                                            ->orderBy('expiry_date', 'desc')->first();

                                if ($subscription)
                                    {

                                        if ($subscription->rate->category == 'premium')
                                            {
                                                $add_rate = Rate::where('rate_type_id', $subscription->rate->rate_type_id)
                                                                ->where('product_id', $product->id)->where('category', 'premium plus')->where('status', 1)->first();

                                                if ($add_rate)
                                                    array_push($additional_rates, $add_rate->id);
                                            }

                                        if ($subscription->rate->category == 'normal' && $subscription->rate->period > 360)
                                            {
                                                $add_rates = Rate::where('product_id', $product->id)
                                                                 ->whereIn('category', ['premium plus', 'premium'])
                                                                 ->where('period', '>', 360)
                                                                 ->where('status', 1)->get();

                                                if ($add_rates->count() > 0)
                                                    $additional_rates = array_merge($additional_rates, $add_rates->pluck('id')->toArray());

                                            }

                                        $transaction = Transaction::where('subscription_id', $subscription->id)
                                                                  ->where('status', 1)
                                                                  ->orderBy('id', 'desc')->limit(1)->first();

                                        $original_days  = $subscription->rate->period;
                                        $original_cost  = ($transaction->amount_paid + $transaction->discount); // transaction amount_paid + discount
                                        $remaining_days = \Illuminate\Support\Carbon::parse($subscription->expiry_date)->diffInDays(now());
                                        if ($remaining_days > 0)
                                            $prorate = ceil(($original_cost / $original_days) * $remaining_days);

                                        if ($prorate > 0)
                                            {
                                                $prorate = match_upgrade_currency($prorate, $subscription->rate->currency, $currency);
                                            }

                                        $rate_ids    = $rates->pluck('id')->toArray();
                                        $determinant = $subscription->rate_id;

                                        $exemptions = Rate::where('product_id', $product->id)
                                                          ->where('status', 1)->where('period', '<=', $subscription->rate->period)->pluck('id')->toArray();

//                    $index = array_search($determinant, $rate_ids);
//                    if ($index !== false) {
//                        $exemptions = array_slice($rate_ids, $index);
//                    }
//                    if(!empty($rate_ids) && $determinant == $rate_ids[0]) {
//                        $exemptions = $rate_ids;
//                    }
                                    }
                            }

                        $products = Rate::whereStatus(1)->whereHas('product', function ($query) use ($prod)
                            {
                                $query->where('identifier', $prod);
                            })
                                        ->when($is_archive, function ($query)
                                            {
                                                $query->where('name', 'like', '%Archive%')->where('type', 'individual');
                                            })
                                        ->when(!$is_archive, function ($query)
                                            {
                                                $query->where('name', 'not like', '%Archive%');
                                            })
                                        ->when($request->has('type'), function ($query) use ($request)
                                            {
                                                $query->where('type', $request->type);
                                            })->when($request->has('article') && ($request->article == "true"), function ($query) use ($request)
                                {
                                    $query->where('name', 'article');
                                })
                                        ->when(!$request->has('article') || ($request->article == "false"), function ($query) use ($request)
                                            {
                                                $query->where('name', '!=', 'article');
                                            })
                                        ->where('currency', $currency)
                                        ->where('status', 1)
                                        ->whereNotIn('id', $exemptions)
                                        ->where('category', 'normal')
                                        ->orderBy('listorder', 'ASC')->offset($start)->limit($end)->get();

                        if ($request->has('upgrade') && $request->upgrade == "true")
                            {
                                $products = $products->map(function ($rate) use ($prorate)
                                    {
                                        $price      = $rate->cost - $prorate;
                                        $rate->cost = number_format((($price < 1) ? 1 : $price), 2, '.', '');
                                        return $rate;
                                    });
                            }

                        $bundles = [];
                        $bundled = Rate::where('product_id', $product->id)
                                       ->where('status', 1)
                                       ->where('name', '!=', 'article')
                                       ->where(function ($query)
                                           {
                                               $query
                                                   ->where('category', '!=', 'normal')
                                                   ->orWhere('include_in_bundle', 1);
                                           })
                                       ->where('currency', $currency)
                                       ->where(function ($query) use ($exemptions, $additional_rates)
                                           {
                                               $query
                                                   ->whereNotIn('id', $exemptions)
                                                   ->orWhereIn('id', $additional_rates);
                                           })
                                       ->orderBy('listorder', 'ASC')
                                       ->get()
                                       ->groupBy('name');

                        if ($bundled->count() > 0 && $request->has('upgrade') && $request->upgrade == "true")
                            {
                                $bundled = $bundled->map(function ($bundle) use ($prorate)
                                    {
                                        $bundle = $bundle->map(function ($rate) use ($prorate)
                                            {
                                                $price      = $rate->cost - $prorate;
                                                $rate->cost = number_format((($price < 1) ? 1 : $price), 2, '.', '');
                                                return $rate;
                                            });
                                        return $bundle;
                                    });
                            }

                        foreach ($bundled as $name => $plans)
                            {
                                $temp = [
                                    'name'  => $name,
                                    'rates' => RateResource::collection($plans)
                                ];
                                array_push($bundles, $temp);
                            }

                        return response()->json([
                            'status'            => true, 'isPremium' => (bool)$product->is_premium, 'data' => RateResource::collection($products)
                            , 'payment_methods' => $product->payment_methods->map->only(['identifier', 'name', 'type', 'icon']),
                            'bundles'           => $bundles,
                        ]);
                    }
                catch (Exception $e)
                    {
                        return response()->json([
                            'status' => false, 'data' => $e->getMessage()
                        ]);
                    }
            }

    /**
     * @queryParam uuid string required if the user is not authenticated. Example: "123e4567-e89b-12d3-a456-426614174000"
     * @queryParam region string optional Used to determine cart currency if the user is authenticated. Example: "US"
     * @security ApiKey
     * @security BearerAuth
     *
     */

        public function get_cart(Request $request)
            {
                if (!\auth()->check())
                    {
                        try
                            {
                                $request->validate(['uuid' => ['required', 'string'], 'region' => 'sometimes|string']);
                            }
                        catch (ValidationException $e)
                            {
                                return response()->json(['message' => $e->validator->errors()->first()], 422);
                            }
                    }

                $cart = Cart::with(['items'])
                            ->when($request->has('uuid'), function ($query) use ($request)
                                {
                                    $query->where('uuid', $request->uuid);
                                })
                            ->when(\auth()->check() && !$request->has('uuid'), function ($query)
                                {
                                    $query->where('user_id', Auth::user()->id);
                                })
                            ->where('status', 0)->orderBy('id', 'desc')->first();

                if (!$cart)
                    {
                        $uuid = null;
                        if ($request->has('region') && \auth()->check())
                            {
                                $region = Region::where('code', $request->region)->first();
                                if ($region)
                                    {
                                        $cart                  = new Cart();
                                        $cart->user_id         = @Auth::user()->id;
                                        $cart->organization_id = @Auth::user()->id ?? 0;
                                        $cart->amount          = 0;
                                        $cart->currency        = $region->currency_code;
                                        $cart->uuid            = $uuid;
                                        $cart->status          = 0;
                                        $cart->save();
                                    }
                            }
                        return response()->json(['status' => true, 'data' => !is_null($cart) ? $cart : (object)[]]);
                    }
                else
                    {
                        if (\auth()->check())
                            {
                                $cart->user_id         = @Auth::user()->id;
                                $cart->organization_id = @Auth::user()->id ?? 0;
                                $cart->save();
                            }
                    }

                $cart->items = $cart->items->map(function ($item)
                    {
                        $item->name = $item->rate_type;
                        return $item;
                    });

                return response()->json([
                    'status' => true, 'data' => $cart
                ]);
            }

        public function remove_from_cart(Request $request)
            {
                try
                    {
                        $request->validate(['cart_item_id' => ['required', 'numeric', 'exists:cart_items,id']]);
                        if (!\auth()->check())
                            {
                                $request->validate(['uuid' => ['required', 'string']]);
                            }

                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['message' => $e->validator->errors()->first()], 422);
                    }

                $cartitem = CartItem::with(['cart'])->find($request->cart_item_id);
                $cart     = $cartitem->cart;


                if (!is_null($cartitem))
                    {
                        if ((\auth()->check()) && (\auth()->user()->id == $cart->user_id) || ($request->has('uuid') && $cart->uuid == $request->uuid))
                            {
                                $rate = Rate::find($cartitem->rate_id);
                                $cartitem->delete();

                                if ($rate)
                                    {
                                        if ($cart->amount >= $rate->cost)
                                            $cart->decrement('amount', $rate->cost);
                                        else
                                            $cart->update(['amount' => 0]);

                                        if ($cart->items()->count() == 0)
                                            {
                                                $cart->amount = 0;
                                                $cart->save();
                                            }

                                        if (\auth()->check())
                                            {
                                                $cart->user_id         = @Auth::user()->id;
                                                $cart->organization_id = @Auth::user()->id ?? 0;
                                                $cart->save();
                                            }
                                    }
                            }
                        else
                            {

                                $cart        = Cart::with(['items'])
                                                   ->when($request->has('uuid'), function ($query) use ($request)
                                                       {
                                                           return $query->where('uuid', $request->uuid);
                                                       })
                                                   ->when(\auth()->check(), function ($query)
                                                       {
                                                           return $query->where('user_id', \auth()->id());
                                                       })
                                                   ->orderBy('id', 'desc')
                                                   ->first();
                                $cart->items = $cart->items->map(function ($item)
                                    {
                                        $item->name = $item->rate_type;
                                        return $item;
                                    });

                                return response()->json([
                                    'status' => false, 'data' => ['message' => "You don't have that item in your cart",
                                                                  'cart'    => $cart
                                    ]
                                ], 200);
                            }

                        $cart->load(['items']);
                        $cart->items = $cart->items->map(function ($item)
                            {
                                $item->name = $item->rate_type;
                                return $item;
                            });

                        return response()->json([
                            'status' => true, 'data' => [
                                'message' => 'Cart item removed successfully',
                                'cart'    => $cart
                            ]
                        ]);
                    }

                $cart->items = $cart->items->map(function ($item)
                    {
                        $item->name = $item->rate_type;
                        return $item;
                    });

                return response()->json([
                    'status' => false, 'data' => [
                        'message' => 'Cart item not found',
                        'cart'    => $cart
                    ]
                ], 200);

            }

        public function initiate_cart(Request $request)
            {
                try
                    {
                        $request->validate([
                            'region' => 'required|string|size:2|exists:regions,code',
                        ]);
                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['message' => $e->validator->errors()->first()], 422);
                    }

                $region = Region::where('code', $request->region)->first();

                start:
                $uuid = strtoupper(Str::uuid()->toString());
                $cart = Cart::where('uuid', $uuid)->first();
                if ($cart)
                    goto start;

                if (\auth()->check())
                    {
                        $cart = Cart::where('user_id', \auth()->id())->where('status', 0)->orderby('id', 'desc')->first();
                        if ($cart)
                            {
                                if (is_null(@$cart->uuid))
                                    $cart->uuid = $uuid;

                                $cart->currency        = $region->currency_code;
                                $cart->user_id         = @Auth::user()->id;
                                $cart->organization_id = @Auth::user()->id ?? 0;
                                $cart->save();
                            }

                    }

                if (!$cart)
                    {
                        $cart                  = new Cart();
                        $cart->user_id         = @Auth::user()->id;
                        $cart->organization_id = @Auth::user()->id ?? 0;
                        $cart->amount          = 0;
                        $cart->currency        = $region->currency_code;
                        $cart->uuid            = $uuid;
                        $cart->status          = 0;
                        $cart->save();
                    }


                return response()->json([
                    'status' => true,
                    'data'   => [
                        'message' => 'Cart initiated',
                        'uuid'    => $cart->uuid,
                        'cart'    => $cart
                    ]
                ]);
            }

        public function cart(Request $request)
            {
                try
                    {
                        $request->validate([
                            'rate_id'      => ['required', 'exists:rates,id', 'numeric'],
                            'release_date' => ['required', 'date'],
                        ]);
                        if (!\auth()->check())
                            {
                                $request->validate(['uuid' => ['required', 'string']]);
                            }
                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['message' => $e->validator->errors()->first()], 422);
                    }

                $cart = Cart::with(['items'])
                            ->when($request->has('uuid'), function ($query) use ($request)
                                {
                                    $query->where('uuid', $request->uuid);
                                })
                            ->when(\auth()->check() && !$request->has('uuid'), function ($query)
                                {
                                    $query->where('user_id', Auth::user()->id);
                                })
                            ->where('status', 0)->orderBy('id', 'desc')->first();

                $rate = Rate::find((int)$request->rate_id);


//        $cost = $this->currency_convert($rate->cost, $rate->currency,
//            config('custom.BILLING.RESERVED_CURRENCY'));

                $cost     = $rate->cost;
                $currency = config('custom.BILLING.RESERVED_CURRENCY');
                $currency = $rate->currency;

                if ($cart)
                    {
                        $cart->currency        = $currency;
                        $cart->user_id         = @Auth::user()->id;
                        $cart->organization_id = @Auth::user()->id ?? 0;
                        $cart->save();
                    }

                if (is_null($cart))
                    {
                        $cart                  = new Cart();
                        $cart->user_id         = @Auth::user()->id;
                        $cart->organization_id = @Auth::user()->id ?? 0;
                        $cart->amount          = 0;
                        $cart->currency        = $currency;
                        $cart->status          = 0;
                        $cart->uuid            = @$request->uuid;
                        $cart->save();
                    }

                $exists = $cart->items()->where(['rate_id' => $request->rate_id, 'release_date' => $request->release_date])->first();

                $checks = [
                    'cart_id'      => $cart->id,
                    'rate_id'      => $request->rate_id,
                    'release_date' => $request->release_date
                ];

                $cart->items()->updateOrCreate($checks, [
                    'cart_id'      => $cart->id,
                    'product'      => $rate->product->product_name,
                    'rate_type'    => $rate->rate_type->name,
                    'cost'         => $cost,
                    'currency'     => $currency,
                    'rate_id'      => $request->rate_id,
                    'release_id'   => $request->release_id,
                    "release_date" => $request->release_date,
                    'thumbnail'    => $request->thumbnail
                ]);

                if (!$exists)
                    {
                        $cart->increment('amount', $cost);
                        $cart->save();

                        $cart->load(['items']);
                        $cart->items = $cart->items->map(function ($item)
                            {
                                $item->name = $item->rate_type;
                                return $item;
                            });

                        return response()->json([
                            'status' => true, 'data' => [
                                'message' => 'Cart item added successfully',
                                'cart'    => $cart
                            ]
                        ]);
                    }

                $cart->load(['items']);
                $cart->items = $cart->items->map(function ($item)
                    {
                        $item->name = $item->rate_type;
                        return $item;
                    });

                return response()->json([
                    'status' => true, 'data' => [
                        'message' => 'Item exists in cart',
                        'cart'    => $cart
                    ]
                ]);

            }

        public function add_edition(Request $request)
            {
                try
                    {
                        $request->validate([
                            'product'      => 'required|string',
                            'release_date' => ['required', 'date']
                        ]);

                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['message' => $e->validator->errors()->first()], 422);
                    }
                $product = Product::where('identifier', $request->product)
                                  ->where('is_premium', 0)
                                  ->where('status', 1)->first();
                if (!$product)
                    {
                        return response()->json([
                            'status'  => false,
                            'message' => "Product is not free"
                        ]);
                    }

                $rate = Rate::where('product_id', $product->id)->orderBy('id', 'asc')->first();

                $user                     = auth()->user();
                $identifier               = strtoupper(Str::random(8));
                $subg                     = SubscriptionGroup::firstOrCreate(['subdate' => Carbon::now()->format('Y-m-d')],
                    ['identifier' => Str::random(8)]);
                $subscription             = new Subscription();
                $subscription->identifier = $identifier = strtoupper(Str::random(8));;
                $subscription->product_id            = $product->id;
                $subscription->subscription_group_id = $subg->id;
                $subscription->subscription_date     = Carbon::parse($request->release_date)->startOfDay();
                $subscription->expiry_date           = Carbon::parse($subscription->subscription_date)->addDays(1)->endOfDay();
                $subscription->rate_id               = @$rate->id;
                $subscription->status                = 1;
                $subscription->user_id               = $user->id;
                $subscription->save();
                $identifier              = strtoupper(Str::random(8));
                $payment_method          = PaymentMethod::orderBy('id', 'asc')->limit(1)->first();
                $transaction             = new Transaction();
                $transaction->identifier = $identifier = strtoupper(Str::random(8));;
                $transaction->subscription_id   = $subscription->id;
                $transaction->payment_method_id = $payment_method->id;
                $transaction->channel           = 'Mpesa';
                $transaction->currency          = @$rate->currency ?? 'KES';
                $transaction->status            = 1;
                $transaction->user_id           = $user->id;
                $transaction->save();

                return response()->json([
                    'status'  => true,
                    'message' => 'Edition added successfully'
                ]);

            }

        public function check_payment(Request $request)
            {

                if (!$request->has('transaction_identifier'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'transaction_identifier not set!!'
                        ], 403);
                    }

                $query = Subscription::with([
                    'product', 'rate', 'transaction'
                ])->wherehas('transaction', function ($query) use ($request)
                    {

                        $query->where('identifier', $request->transaction_identifier);
                    })->first();

                if (is_null($query))
                    {
                        $cart = Cart::where('identifier', $request->transaction_identifier)->first();
                        if ($cart)
                            {
                                $query                          = Subscription::with(['product', 'rate', 'transaction'])
                                                                              ->where('cart_id', $cart->id)->first();
                                $query->transaction->identifier = $cart->identifier;
                            }
                    }

                if (!is_null($query))
                    {
                        if ((bool)$query->status)
                            {

                                return response()->json([
                                    'status' => true, 'data' => $query
                                ]);
                            }
                        else
                            {
                                return response()->json([
                                    'status' => false, 'data' => 'Payment Not confirmed'
                                ]);
                            }
                    }
                else
                    {
                        return response()->json([
                            'status' => false, 'data' => 'No payment information available'
                        ]);
                    }

            }

        public function subscribe(Request $request)
            {
                if ($request->has('payment_method_identifiers') && $request->has('payment_method_identifier'))
                    {
                        return response()->json(['message' => "payment_method_identifiers and payment_method_identifier parameters cannot be passed simultaneously"], 422);
                    }

                try
                    {

                        $request->validate([
                            'cart_id'   => 'required|exists:carts,id|numeric',
                            'region'    => 'required|string',
                            'recurring' => 'required'
                        ]);

                        if (!$request->has('payment_method_identifiers') || !is_array($request->payment_method_identifiers))
                            $request->validate(['payment_method_identifier' => 'required|string|exists:payment_methods,identifier']);
                        else
                            $request->validate(['payment_method_identifiers' => 'required|array|min:1']);

                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['message' => $e->validator->errors()->first()], 422);
                    }

                $payment_methods = [];
                $payment_method  = PaymentMethod::where('identifier', $request->payment_method_identifier)->first();

                if (is_null($payment_method))
                    {
                        $methods = PaymentMethod::whereIn('identifier', $request->payment_method_identifiers)->get();
                        foreach ($methods as $method)
                            {
                                array_push($payment_methods, $method);
                            }
                    }
                else
                    {
                        array_push($payment_methods, $payment_method);
                    }

                foreach ($payment_methods as $payment_method)
                    {
                        if ($payment_method->type == 'dpo')
                            {
                                try
                                    {
                                        $request->validate([
                                            'redirect_url' => 'required|string',
                                            'back_url'     => 'required|string',
                                        ]);
                                    }
                                catch (ValidationException $e)
                                    {
                                        return response()->json(['message' => $e->validator->errors()->first()], 422);
                                    }

                                if (is_bad_url($request->redirect_url))
                                    {
                                        return response()->json(['message' => 'Redirect URL ' . $request->redirect_url . ' is not allowed'], 422);
                                    }
                                if (is_bad_url($request->back_url))
                                    {
                                        return response()->json(['message' => 'Back URL ' . $request->back_url . ' is not allowed'], 422);
                                    }
                            }
                    }

                $cart = Cart::with(['items'])->find($request->cart_id);

                if ($cart->items->count() == 0)
                    {
                        return response()->json(['message' => "Cart is Empty"], 422);
                    }

                if (!is_null($cart))
                    {
                        if (is_null($cart->identifier))
                            $cart->identifier = strtoupper(Str::random(8));

                        if (is_null($cart->user_id))
                            {
                                $cart->user_id         = \auth()->user()->id;
                                $cart->organization_id = $cart->user_id;
                            }
                        $cart->save();


                        $bills = [];
                        Subscription::where('cart_id', $cart->id)->update(["cart_id" => null]);//->delete();

                        foreach ($payment_methods as $payment_method)
                            {
                                $f_amount = 0;

                                $dates     = [];
                                $activated = 0;

                                foreach ($cart->items as $key => $item)
                                    {
                                        $identifier       = strtoupper(Str::random(8));
                                        $trans_identifier = strtoupper(Str::random(8));

                                        $rate = Rate::with(['product'])->find($item->rate_id);

                                        $subscription = Subscription::with(['transaction'])->where('product_id', $rate->product_id)
                                            //->where('status',1)
                                                                    ->where('cart_id', $cart->id)
                                                                    ->where('user_id', $cart->user_id)
                                                                    ->whereDate('expiry_date', '>=', date_create('now')->format('Y-m-d'))
                                                                    ->orderBy('expiry_date', 'desc')
                                                                    ->first();


                                        $subg = SubscriptionGroup::firstOrCreate(['subdate' => Carbon::now()->format('Y-m-d')],
                                            ['identifier' => Str::random(8)]);

//                    $startdate = (!is_null($subscription) ? Carbon::parse($subscription->expiry_date)->toDateTimeString()
//                        :Carbon::parse($item->release_date)->startOfDay()->toDateTimeString());
//
//                    if(array_key_exists($rate->product_id,$dates))
//                    {
//                        if(Carbon::parse($dates[$rate->product_id]) >= Carbon::now()->startOfDay() )
//                            $startdate = Carbon::parse($dates[$rate->product_id])->addDays(1)->startOfDay()->toDateTimeString();
//                    }

                                        $startdate = Carbon::parse($item->release_date)->startOfDay()->toDateTimeString();
                                        if ($startdate >= Carbon::now()->format('Y-m-d'))
                                            {

                                                if ($subscription && $startdate <= Carbon::parse($subscription->expiry_date)->startOfDay()->toDateTimeString() && (@$subscription->transaction->first()->payment_method_id == $payment_method->id))
                                                    {
                                                        $startdate = Carbon::parse($subscription->expiry_date)->addDays(1)->startOfDay()->toDateTimeString();
                                                    }
                                            }
                                        else
                                            {

                                                if (!str_contains(strtolower($rate->name), 'archive'))
                                                    {
                                                        //$startdate = Carbon::now()->startOfDay()->toDateTimeString();

                                                    }
                                            }

                                        if (@$rate->product->type == 'epaper')
                                            $enddate = Carbon::parse($startdate)->addDays(($item->rate->period + $item->rate->compensation_days) - 1)->endOfDay()->toDateTimeString();
                                        else
                                            $enddate = Carbon::parse($startdate)->addDays(($item->rate->period + $item->rate->compensation_days))->endOfDay()->toDateTimeString();


                                        $region = Region::where('code', $request->region)->first();
                                        if ($subg)
                                            {
                                                $subs = Subscription::create([
                                                    'user_id'           => Auth::user()->id, 'product_id' => $item->rate->product_id,
                                                    'subscription_date' => $startdate, 'expiry_date' => $enddate, 'cart_id' => $request->cart_id, 'identifier' => $identifier . '-' . $request->cart_id, 'subscription_group_id' => $subg->id, 'rate_id' => $item->rate->id, 'reccuring' => $request->recurring
                                                ]);
                                                if ($subs)
                                                    {
                                                        //attach products to subs
                                                        attach_products($subs);

//                        $check = Transaction::whereJsonContains('subscription_ids',
//                            $subs->id)->first();

                                                        $check = Transaction::where('subscription_id', $subs->id)->first();

                                                        $amount   = $item->cost;
                                                        $currency = $item->currency;

                                                        //dd($rate->product_id);
                                                        $coupon = null;
                                                        if ($request->has('coupon'))
                                                            {
                                                                $coupon       = $request->get('coupon');
                                                                $cost         = $this->discount_calc($coupon, $amount,
                                                                    $region,
                                                                    $rate->product_id,
                                                                    Auth::user()->id,
                                                                    $rate->id);
                                                                $discount     = $cost->discount;
                                                                $amount       = $cost->amount;
                                                                $total_amount = $cost->total_amount;

                                                            }
                                                        else
                                                            {
                                                                $discount = 0;
                                                                /** @var TYPE_NAME $amount */
                                                                $total_amount = $amount;
                                                            }

                                                        if (is_null($check))
                                                            {
                                                                $trans                    = new Transaction();
                                                                $trans->identifier        = $trans_identifier;
                                                                $trans->subscription_id   = $subs->id;
                                                                $trans->payment_method_id = $payment_method->id;
                                                                $trans->{'channel'}       = $payment_method->name;
                                                                $trans->total_amount      = $total_amount;
                                                                $trans->amount            = $amount;
                                                                $trans->discount          = $discount;
                                                                $trans->coupon_code       = $coupon;
                                                                $trans->currency          = $currency;
                                                                if ($trans->amount == 0)
                                                                    {
                                                                        $trans->status = 1;
                                                                        $trans->subscription()->update(['status' => 1]);
                                                                    }
                                                                else
                                                                    {
                                                                        $trans->status = 0;
                                                                    }
                                                                $trans->user_id = Auth::user()->id;
                                                                $trans->type    = 'initial';
                                                                $trans->save();
                                                                $subs->reccurent_cycle = 1;
                                                                $subs->save();
                                                            }
                                                        else
                                                            {
                                                                $trans                    = new Transaction();
                                                                $trans->identifier        = $trans_identifier;
                                                                $trans->subscription_id   = $subs->id;
                                                                $trans->payment_method_id = $payment_method->id;
                                                                $trans->{'channel'}       = $payment_method->name;
                                                                $trans->total_amount      = $total_amount;
                                                                $trans->amount            = $amount;
                                                                $trans->discount          = $discount;
                                                                $trans->coupon_code       = $request->coupon;
                                                                $trans->currency          = $currency;
                                                                $trans->redirect_url      = !is_null($request->redirect_url) ? trim($request->redirect_url) : null;
                                                                $trans->back_url          = !is_null($request->back_url) ? trim($request->back_url) : null;
                                                                if ($trans->amount == 0)
                                                                    {
                                                                        $trans->status = 1;
                                                                        $trans->subscription()->update(['status' => 1]);
                                                                    }
                                                                else
                                                                    {
                                                                        $trans->status = 0;
                                                                    }
                                                                $trans->user_id = Auth::user()->id;
                                                                $trans->type    = 'recurrent';
                                                                $trans->save();
                                                                $subs->increment('reccurent_cycle');
                                                                $subs->save();
                                                            }
                                                    }
                                            }
                                        $f_amount += $amount;

                                        $dates[$rate->product_id] = $enddate;

                                        if ($trans->status == 1)
                                            {
                                                $activated += 1;
                                            }
                                    }

                                $cart->update(['status' => 0]);

                                try
                                    {
                                        $bill = BillingLibrary::payment($subs, $trans, $request->user(), $payment_method,
                                            $request->recurrent, $region,
                                            number_format($f_amount, 2), $cart->currency,
                                            route('dpo_callback'), route('dpo_callback'), $cart->identifier);

                                        if (array_key_exists('dpo_transaction_code', $bill))
                                            {
                                                Transaction::whereHas('subscription', function ($query) use ($cart)
                                                    {
                                                        $query->where('cart_id', $cart->id);
                                                    })->update(['transaction_code'  => $bill['dpo_transaction_code'],
                                                                'transaction_token' => $bill['dpo_transaction_token'],
                                                                'back_url'          => $request->back_url,
                                                                'redirect_url'      => $request->redirect_url
                                                ]);
                                            }

                                        if ($activated == $cart->items->count())
                                            {
                                                $bill['SubscriptionActivated'] = true;
                                                $cart->update(['status' => 1]);
                                            }

                                        $bills[] = (object)$bill;
                                        //
                                    }
                                catch (Exception $e)
                                    {
                                        Log::info('Payment: ' . $e->getMessage());
                                    }
                            }

                        if (count($bills) > 1)
                            {
                                $data = [
                                    'status'       => true,
                                    'subscription' => ($activated == $cart->items->count()) ? true : false,
                                    'mpesa'        => (collect($bills)->filter(function ($bill_item)
                                        {
                                            return $bill_item->type == 'mpesa';
                                        }))->first(),
                                    'dpo'          => (collect($bills)->filter(function ($bill_item)
                                        {
                                            return $bill_item->type == 'dpo';
                                        }))->first()
                                ];
                                return response()->json($data);
                            }

                        return response()->json($bill);
                    }
            }

        public function get_mpesa_qr(Request $request)
            {
                try
                    {
                        $request->validate([
                            'account' => 'string|required',
                        ]);
                    }
                catch (ValidationException $e)
                    {
                        return response()->json([
                            'status' => false,
                            'data'   => $e->validator->errors()->first()
                        ]);
                    }

                $amount = 0;

                $cart        = Cart::where('identifier', $request->account)->first();
                $transaction = null;
                if ($cart)
                    {
                        $transactions = Transaction::with(['subscription', 'user', 'payment_method'])
                                                   ->whereHas('subscription', function ($query) use ($cart)
                                                       {
                                                           $query->where('cart_id', $cart->id);
                                                       })->where('channel', 'like', '%mpesa%')->get();

                        foreach ($transactions as $trans)
                            {
                                $amount = $amount + $trans->amount;
                            }

                        $transaction = $transactions->first();
                    }
                else
                    {

                        $transaction = Transaction::with(['subscription', 'payment_method'])->where('identifier', $request->account)->first();
                        $amount      = $transaction->amount;
                    }

                if (is_null($transaction))
                    {
                        return response()->json([
                            'status' => false,
                            'error'  => 'account not found!!'
                        ]);
                    }

                $payment_method = $transaction->payment_method;

                $mpesa                 = new Mpesa(PaymentStageEnum::from($payment_method->configuration['environment'])->name);
                $mpesa->consumerkey    = $payment_method->configuration['consumer_key'];
                $mpesa->consumersecret = $payment_method->configuration["consumer_secret"];
                $mpesa->shortcode      = $payment_method->configuration["shortcode"];
                $mpesa->trxcode        = 'PB';
                $mpesa->merchantname   = $payment_method->name;
                $mpesa->amount         = ceil($amount);
                $mpesa->ref            = $request->account;
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
                    'status'  => true,
                    'amount'  => $amount,
                    'account' => $request->account,
                    'sub'     => $transaction->subscription->identifier,
                    'paybill' => $payment_method->configuration["shortcode"],
                    'qr'      => $qr
                ];
                return response()->json($data);

            }

        public function currency_convertor(Request $request)
            {

                $cost = $this->currency_convert($request->amount, $request->from, $request->to);

                return response()->json([
                    'status' => true, 'data' => $cost
                ], 200);
            }

        public function mpesa_view($encdata)
            {

                $d = PaymentMeta::where('uuid', $encdata)->first();

                $data                  = $d->data;
                $cost                  = Currency::convert()->from('USD')->to($data['currency_code'])->amount($data['amount'])->get();
                $dx                    = [];
                $dx['consumer_key']    = $data['consumer_key'];
                $dx['consumer_secret'] = $data['consumer_secret'];
                $mpesa                 = new Mpesa($dx);
                //dd($mpesa);
                $detail               = new stdClass();
                $detail->qrversion    = '1';
                $detail->trxcode      = 'PB';
                $detail->cpi          = $data['payment_method']['identifier'];
                $detail->merchantname = 'NMG';
                $detail->amount       = ceil($cost);
                $detail->refno        = $data['transcode'];
                $detail->qrformat     = 1;
                $detail->qrtype       = 'D';

                $qr = $mpesa->qr($detail);

                $this->data['pay'] = (object)$data['payment_method'];
                //$this->data['cart']       = (object)$data['cart'];
                $this->data['recurrency'] = $data['recurring'];
                $this->data['qr']         = $qr->QRCode;
                $this->data['sub']        = (object)$data['subs'];
                $this->data['cost']       = ceil($cost);

                return response()->view('modules.front.mpesa', $this->data);
            }

        public function save_leads(Request $request)
            {

                if (!$request->has('product'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'product not set!!'
                        ], 403);
                    }
                if (!$request->has('link'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'link not set!!'
                        ], 403);
                    }
                if (!$request->has('amount'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'amount not set!!'
                        ], 403);
                    }

                if (!$request->has('package'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'package not set!!'
                        ], 403);
                    }
                $prod = Product::where('identifier', $request->product)->first();
                if (!is_null($prod))
                    {
                        $lead = Lead::where('product_id', $prod->id)->where('link',
                            $request->link)->where('type',
                            $request->package)->first();
                        if (is_null($lead))
                            {
                                $lead             = new Lead();
                                $lead->product_id = $prod->id;
                                $lead->title      = $request->title;
                                $lead->link       = $request->link;
                                $lead->clicks     = 1;
                                $lead->amount     = $request->amount;
                                $lead->type       = $request->package;
                                $lead->save();
                            }
                        else
                            {
                                $lead->increment('clicks');
                                $lead->increment('amount', $request->amount);
                                $lead->save();
                            }
                        if ($lead)
                            {
                                return response()->json([
                                    'status' => true, 'data' => 'Saved successfully'
                                ], 200);
                            }

                        return response()->json([
                            'status' => false, 'data' => 'Not Saved '
                        ], 200);
                    }
                else
                    {
                        return response()->json([
                            'status' => false, 'data' => 'Product not found '
                        ], 200);
                    }

            }

        public function direct_checkout(Request $request)
            {

                if (!$request->has('payment_method_identifier'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'payment_method_identifier not set!!'
                        ], 403);
                    }

                if (!$request->has('rate_id'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'rate_id not set!!'
                        ], 403);
                    }
                if (!$request->has('subscription_date'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'subscription_date not set!!'
                        ], 403);
                    }


                if (!$request->has('recurrent'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'recurrent not set!!'
                        ], 403);
                    }

                if (!$request->has('region'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'region not set!!'
                        ], 403);
                    }
                if (!$request->has('redirect_url'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'redirect_url not set!!'
                        ], 403);
                    }

                if (!$request->has('back_url'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'back_url not set!!'
                        ], 403);
                    }
                try
                    {

                        //Log::info(json_encode($request));
                        $user = User::with([
                            'whitelist', 'organization', 'organization.whitelist'
                        ])->find(Auth::user()->id);
                        $user->meta()->insert([
                            'user_id' => $user->id, 'action' => 'Subscription - Direct Checkout', 'result' => Carbon::now(), 'ip_address' => $request->ip(), 'source' => $request->redirect_url ?? '', 'date_created' => Carbon::now()->format('Y-m-d')
                        ]);

                        $region = Region::where('code', $request->region)->first();
                        if (is_null($user))
                            {
                                return response()->json([
                                    'status' => false, 'error' => 'user not found'
                                ], 403);
                            }

                        $subscribed = Subscription::with([
                            'product', 'transaction', 'transaction.rate'
                        ])->where('subscription_date', '<=',
                            Carbon::parse($request->subscription_date)->format('Y-m-d H:is'))->where('expiry_date',
                            '>=',
                            Carbon::parse($request->subscription_date)->format('Y-m-d H:is'))->where('rate_id', $request->rate_id)
                                                  ->where('user_id', Auth::user()->id)->where('status', 1)
                                                  ->when($request->has('article_id') && is_numeric($request->article_id), function ($q) use ($request)
                                                      {
                                                          $q->where('article_id', $request->article_id);
                                                      })
                                                  ->first();

                        if (is_null($subscribed))
                            {
                                $payment_method = PaymentMethod::whereIdentifier($request->payment_method_identifier)->first();
                                if (is_null($payment_method))
                                    {
                                        return response()->json([
                                            'status' => false, 'error' => 'payment method not found!'
                                        ], 403);
                                    }
                                $subg = SubscriptionGroup::firstOrCreate(['subdate' => Carbon::parse($request->subscription_date)->format('Y-m-d')],
                                    ['identifier' => Str::ulid()]);


                                $rate = Rate::with(['product'])->where('status', 1)->find($request->rate_id);

                                if ($rate->cost == 0)
                                    {
                                        return [
                                            'identifier' => 'whitelisted', 'product' => $rate->product->product_name, 'productIdentifier' => $rate->product->identifier, 'type' => 'whitelist', 'period' => 1, 'subscriptionDate' => Carbon::now()->startOfDay(), 'expiryDate' => Carbon::now()->endOfDay(), 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => 'N/A', 'subscriptionActivated' => true
                                        ];
                                    }
                                $check2 = Subscription::with([
                                    'product', 'transaction', 'transaction.rate'
                                ])->where('subscription_date', '<=',
                                    Carbon::parse($request->subscription_date)->format('Y-m-d H:is'))->where('expiry_date',
                                    '>=',
                                    Carbon::parse($request->subscription_date)->format('Y-m-d H:is'))->where('product_id',
                                    $rate->product_id)->where('rate_id', $request->rate_id)->where('user_id', Auth::user()->id)
                                                      ->where('status', 0)
                                                      ->when($request->has('article_id') && is_numeric($request->article_id), function ($q) use ($request)
                                                          {
                                                              $q->where('article_id', $request->article_id);
                                                          })
                                                      ->first();

                                $elapsed_days = 0;
                                if ($rate->category !== 'normal' && $rate->name !== 'Archive Issue' && today()->gt(Carbon::parse($request->subscription_date)))
                                    {
                                        $elapsed_days = today()->diffInDays(Carbon::parse($request->subscription_date));
                                    }

                                if ($subg)
                                    {
                                        $start_date = ($rate->product->type == 'paywall') ? Carbon::parse($request->subscription_date)->toDateTimeString() : Carbon::parse($request->subscription_date)->startOfDay()->toDateTimeString();
                                        $end_date   = ($rate->product->type == 'paywall') ? Carbon::parse($request->subscription_date)->addDays(($rate->period + $rate->compensation_days + $elapsed_days))->toDateTimeString() : Carbon::parse($request->subscription_date)->addDays(($rate->period + $rate->compensation_days + $elapsed_days) - 1)->endOfDay()->toDateTimeString();
                                        $subs       = Subscription::updateOrCreate([
                                            'user_id' => Auth::user()->id, 'product_id' => $rate->product_id, 'rate_id' => $rate->id, 'subscription_date' => $start_date, 'expiry_date' => $end_date
                                        ], [
                                            'identifier'                    => $this->identifer('Subscription',
                                                'identifier',
                                                8), 'subscription_group_id' => $subg->id, 'reccuring' => $request->recurrent, 'article_id' => ($rate->name == 'article') ? $request->article_id : null, 'type' => $rate->category
                                        ]);

                                        //attach products to subs
                                        attach_products($subs);

                                        $check = Transaction::where('subscription_id',
                                            $subs->id)->where('status', 1)->first();
                                        $bal   = $rate->cost;

                                        if (!is_null($check2))
                                            {
                                                $tr = Transaction::where('subscription_id',
                                                    $check2->id)->whereDate('created_at',
                                                    Carbon::now()->format('Y-m-d'))->wherehas('subscription',
                                                    function ($query) use ($request)
                                                        {
                                                            return $query->where('rate_id', $request->rate);
                                                        })->first();
                                                if (!is_null($tr))
                                                    {
                                                        $bal = $tr->amount - $tr->amount_paid;
                                                        if ($bal == 0)
                                                            {
                                                                $check2->update(['status' => 1]);
                                                                $check2->subscription()->update(['status' => 1]);
                                                                return response()->json([
                                                                    'status' => true, 'subscription' => true, 'data' => $check2->subscription->refresh(), 'subscriptionActivated' => true
                                                                ]);
                                                            }
                                                    }
                                            }
                                        //dd($bal);
                                        /* $amount   = $bal;
                     $currency = $region->currency_code;*/
                                        $amount   = $rate->cost;
                                        $currency = $rate->currency;

//                    if ($rate->currency == $region->currency_code)
//                    {
//                        $amount   = $bal;
//                        $currency = $region->currency_code;
//                    }
//                    else
//                    {
//                        if (
//                            in_array($region->code, explode(',',
//                                config('custom.CUSTOMER.COVERED_REGIONS')))
//                        )
//                        {
//                            $amount   = $this->currency_convert($bal, $rate->currency,
//                                $region->currency_code);
//                            $currency = $region->currency_code;
//                        }
//                        else
//                        {
//                            $amount   = $this->currency_convert($bal, $rate->currency,
//                                config('custom.BILLING.RESERVED_CURRENCY'));
//                            $currency = config('custom.BILLING.RESERVED_CURRENCY');
//                        }
//
//                    }
                                        $reserved_amount = $this->currency_convert($amount, $rate->currency,
                                            config('custom.BILLING.RESERVED_CURRENCY'));
                                        //dd($rate->product_id);
                                        $status = 0;
                                        if ($request->has('coupon') && !empty(trim($request->coupon)))
                                            {

                                                $cost = $this->discount_calc($request->coupon, $amount, $region,
                                                    $rate->product_id, Auth::user()->id,
                                                    $rate->rate_type_id);

                                                //Log::debug(json_encode($cost));
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
                                        //if($check->where('status',0))
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
                                        $trans->type                     = is_null($check) ? 'initial' : 'recurrent';
                                        $trans->redirect_url             = trim($request->redirect_url);
                                        $trans->back_url                 = trim($request->back_url);
                                        $trans->save();
                                        //dd($trans);
                                        $subs->status = $status;
                                        if (is_null($check))
                                            {
                                                $subs->reccurent_cycle = 1;
                                            }
                                        else
                                            {
                                                $subs->increment('reccurent_cycle');
                                            }

                                        $subs->save();

                                        try
                                            {
                                                if ($status == 1)
                                                    {
                                                        return [
                                                            'status' => true, 'subscription' => true, 'data' => $request->redirect_url ?? 'https://epaper.nation.africa', 'transaction_code' => $trans->identifier, 'SubscriptionActivated' => true
                                                        ];
                                                        /*return response()->json(['status' => true, 'subscription' => true, 'data' => new SubscriptionResource($subs->refresh())]);*/
                                                    }
                                                else
                                                    {
                                                        $bill = BillingLibrary::payment($subs, $trans,
                                                            $request->user(),
                                                            $payment_method,
                                                            $request->recurrent,
                                                            $region, (float)($amount),
                                                            $currency,
                                                            //$request->back_url,
                                                            //$request->redirect_url
                                                            route('dpo_callback'),
                                                            route('dpo_callback')
                                                        );
                                                        //Log::error($trans->amount);

                                                        return response()->json($bill);
                                                    }

                                            }
                                        catch (Exception $e)
                                            {
                                                Log::info('Payment: ' . $e->getMessage());
                                            }
                                    }
                            }
                        else
                            {
                                //dd($subscribed);
                                return response()->json([
                                    'status' => true, 'subscription' => true, 'data' => $subscribed, 'subscriptionActivated' => true
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

        public function configuration()
            {

                try
                    {
                        $data = config('custom');

                        return response()->json([
                            'status' => true, 'data' => $data['BILLING']
                        ]);
                    }
                catch (Exception $e)
                    {
                        return response()->json([
                            'status' => false, 'data' => $e->getMessage()
                        ]);
                    }
            }

        public function get_subscriptions(Request $request)
            {

                if (!$request->has('start'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'start not set!!'
                        ]);
                    }
                if (!$request->has('limit'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'limit not set!!'
                        ]);
                    }

                try
                    {
                        $product = Product::where('identifier', $request->product)->limit(1)->first();
                        $subs    = [];
                        //dd(Auth::user()->id);
                        $sub = Subscription::with([
                            'product', 'transaction', 'rate'
                        ])->where('user_id', Auth::user()->id)
                                           ->where('status', 1)
                                           ->when($product, function ($query) use ($product)
                                               {
                                                   $query
                                                       ->where('product_id', $product->id)
                                                       ->orWhereHas('products', function ($q) use ($product)
                                                           {
                                                               $q->where('product_id', $product->id);
                                                           });
                                               })
                                           ->offset($request->start ?? 0)
                                           ->limit($request->limit ?? 10)
                                           ->orderBy('created_at', 'DESC')->get();

                        if ($sub->isNotEmpty())
                            {
                                $subs = array_merge($subs, SubscriptionResource::collection($sub)->toArray($request));
                            }

                        if (\auth()->user()->organization_id > 0)
                            {
                                $org_subs = B2bSubscription::with(['product'])
                                                           ->where('organization_id', auth()->user()->organization_id)
                                                           ->where('status', 1)->orderBy('created_at', 'DESC')->get();

                                if ($org_subs->isNotEmpty())
                                    {
                                        $subs = array_merge(B2bSubscriptionResource::collection($org_subs)->toArray($request), $subs);
                                    }
                            }

                        if (count($subs) > 0)
                            {
                                return response()->json([
                                    'status' => true, 'data' => $subs
                                ]);
                            }

                        return response()->json([
                            'status' => false, 'data' => 'Subscription not found'
                        ]);
                    }
                catch (Exception $e)
                    {
                        return response()->json([
                            'status' => false, 'data' => $e->getMessage()
                        ]);
                    }
            }

        public function notification_users(Request $request)
            {

                if (!$request->has('product'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'product not set!!'
                        ], 403);
                    }


                if (!$request->has('limit'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'limit not set!!'
                        ]);
                    }

                /*if (!$request->has('subscription_date') || !$request->has('expires_in'))
            {
                return response()->json(['status' => false, 'error' => 'subscription_date or expires_in  not set!!']);
            }*/
                $product = Product::where('identifier', $request->product)->limit(1)->first();

                $sub = Subscription::with(['user'])
                                   ->join('users', 'users.id', '=', 'subscriptions.user_id')
                                   ->where('users.daily_notifications', 1)
                                   ->when($request->has('email'), function ($query) use ($request)
                                       {
                                           $query->where('users.email', $request->email);
                                       })
//            ->whereHas('user',function($q) use ($request){
//                return $q->where('daily_notifications', 1);
//            })
//            ->whereHas('product', function ($query) use ($request)
//        {
//
//            $query->where('identifier', $request->product);
//
//        })
                                   ->when($request->has('product'), function ($query) use ($product)
                        {
                            $query->where(function ($q) use ($product)
                                {
                                    $q
                                        ->where('product_id', $product->id)
                                        ->orWhereHas('products', function ($q) use ($product)
                                            {
                                                $q->where('product_id', $product->id);
                                            });
                                });

                        })->when($request->has('subscription_date'), function ($query) use ($request)
                        {
                            return $query
                                ->whereDate('subscription_date', '<=', $request->subscription_date)
                                ->whereDate('expiry_date', '>=', $request->subscription_date);

                        })->when($request->has('expires_in'), function ($query) use ($request)
                        {
                            return $query->whereDate('expiry_date',
                                Carbon::now()->addDays($request->expires_in)->format('Y-m-d'));
                        })->where('subscriptions.status', 1)
                                   ->when(!$request->has('subscription_date'), function ($query) use ($request)
                                       {
                                           $query->whereDate('expiry_date', '>=', now()->format('Y-m-d'));
                                       });

                $data = $sub->select('subscriptions.*')->orderBy('subscriptions.id', 'asc')->paginate($request->limit, '*', 'page',
                    $request->page ?? 1);

                if (!empty($data))
                    {
                        return response()->json([
                            'status' => true, 'data' => NotificationResource::collection($data->items()), 'pagination' => (object)[
                                'total' => $data->total(), 'per_page' => $data->perPage(), 'current_page' => $data->currentPage(), 'last_page' => $data->lastPage()
                            ]
                        ]);
                    }
            }

        public function notification_users_corporate(Request $request)
            {

                if (!$request->has('product'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'product not set!!'
                        ], 403);
                    }


                if (!$request->has('limit'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'limit not set!!'
                        ]);
                    }

                /*if (!$request->has('subscription_date') || !$request->has('expires_in'))
            {
                return response()->json(['status' => false, 'error' => 'subscription_date or expires_in  not set!!']);
            }*/
                $sub = User::join('b2b_subscription_users', 'users.id', '=',
                    'b2b_subscription_users.user_id')->join('b2b_subscriptions',
                    'b2b_subscriptions.id', '=',
                    'b2b_subscription_users.b2b_subscription_id')->join('products',
                    'products.id',
                    '=',
                    'b2b_subscriptions.product_id')
                           ->where('users.daily_notifications', 1)
                           ->when($request->has('email'), function ($query) use ($request)
                               {
                                   $query->where('users.email', $request->email);
                               })
                           ->where('products.identifier', $request->product)
                           ->when($request->has('subscription_date'),
                               function ($query) use ($request)
                                   {

                                       return $query->whereDate('start_date', '<=',
                                           $request->subscription_date)->whereDate('expiry_date', '>=',
                                           $request->subscription_date);
                                   })->when($request->has('expires_in'), function ($query) use ($request)
                        {

                            return $query->whereDate('expiry_date',
                                Carbon::now()->addDays($request->expires_in)->format('Y-m-d'));
                        });


                $data = $sub->orderBy('b2b_subscriptions.created_at', 'asc')->paginate($request->limit, [
                    'users.id', 'users.name', 'email', 'phone', 'is_verified', 'verification_count', 'start_date', 'expiry_date', 'can_notify'
                ], 'page',
                    $request->page ?? 1);

                if (!empty($data))
                    {
                        return response()->json([
                            'status' => true, 'data' => CorporateNotificationUserResource::collection($data->items()), 'pagination' => (object)[
                                'total' => $data->total(), 'per_page' => $data->perPage(), 'current_page' => $data->currentPage(), 'last_page' => $data->lastPage()
                            ]
                        ]);
                    }
            }

        public function get_reason(Request $request)
            {

                if (!$request->has('start'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'start not set!!'
                        ]);
                    }
                if (!$request->has('limit'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'limit not set!!'
                        ]);
                    }
                try
                    {
                        $reason = Reason::whereType($request->type)->offset($request->start)->limit($request->limit)->get([
                            'id', 'name', 'description'
                        ]);

                        return response()->json([
                            'status' => true, 'data' => $reason
                        ]);
                    }
                catch (Exception $e)
                    {
                        return response()->json([
                            'status' => false, 'data' => $e->getMessage()
                        ]);
                    }

            }

        public function unsubscribe(Request $request)
            {
                try
                    {
                        $sub = Subscription::whereBelongsTo($request->user())
                            ->where('identifier', $request->identifier)
                            ->first();

                        if (!is_null($sub))
                            {
                                $tr                     = Transaction::with(['payment_method'])->where('subscription_id',
                                    $sub->id)->where('status',
                                    1)->first();
                                $dpo                    = new DPO();
                                $dpo->transaction_token = $tr->transaction_token;
                                $dpo->company_token     = $tr->payment_method->configuration['company_token'];
                                $result                 = $dpo->cancel();
                                if ($result)
                                    {
                                        $sub->unsubscription_date = Carbon::now()->toDateTimeString();
                                        $sub->reason_id           = $request->reason_id;
                                        $res                      = $sub->save();
                                        if ($res)
                                            {
                                                try
                                                    {
                                                        $sub->user->meta()->insert([
                                                            'user_id' => $sub->user->id, 'action' => 'Subscription Deactivation' . $sub->product->name, 'result' => Carbon::now(), 'ip_address' => $request->ip(), 'source' => $request->getHost(), 'date_created' => Carbon::now()->format('Y-m-d')
                                                        ]);
                                                    }
                                                catch (Exception $e)
                                                    {
                                                        Log::error($e->getMessage());
                                                    }

                                                return response()->json([
                                                    'status' => true, 'data' => 'Unsubscribed successfuly from ' . $sub->product->product_name
                                                ]);
                                            }
                                    }
                            }
                        return response()->json([
                            'status' => false, 'data' => 'Subscription not found'
                        ]);
                    }
                catch (Exception $e)
                    {
                        return response()->json([
                            'status' => false, 'data' => $e->getMessage()
                        ]);
                    }

            }

        public function apple_pay(Request $request)
            {
                if (!$request->has('rate_id'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'rate_id not set!!'
                        ], 403);
                    }
                if (!$request->has('subscription_date'))
                    {
                        return response()->json([
                            'status' => false, 'error' => 'subscription_date not set!!'
                        ], 403);
                    }
                try
                    {
                        if (Auth::check())
                            {
                                $user = User::with([
                                    'whitelist', 'organization', 'organization.whitelist'
                                ])->find(Auth::user()->id);

                            }
                        else

                            {
                                if (!$request->has('email'))
                                    {
                                        return response()->json([
                                            'status' => false, 'error' => 'email not set while user is not authenticated!!'
                                        ], 403);
                                    }
                                $user = User::with([
                                    'whitelist', 'organization', 'organization.whitelist'
                                ])->where("email", $request->email)->first();


                            }

                        $user->meta()->insert([
                            'user_id' => $user->id, 'action' => 'Subscription - Apple pay', 'result' => Carbon::now(), 'ip_address' => $request->ip(), 'source' => 'https://epaper.nation.africa', 'date_created' => Carbon::now()->format('Y-m-d')
                        ]);
                        if ($user->organization_id == 0)
                            {
                                $check_whitelist = $user->whitelist()->wherehas('product',
                                    function ($query) use ($request)
                                        {
                                            return $query->where('identifier',
                                                $request->product)->orWhere('product_id',
                                                $request->product);
                                        })->where('startdate', '<=',
                                    Carbon::now()->startOfDay()->toDateTimeString())->where('enddate',
                                    '>=',
                                    Carbon::now()->endOfDay()->toDateTimeString())->first();
                                if (!is_null($check_whitelist))
                                    {
                                        return [
                                            'identifier' => 'whitelisted', 'product' => $check_whitelist->product->product_name, 'productIdentifier' => $check_whitelist->product->identifier, 'type' => 'whitelist', 'period' => Carbon::parse($check_whitelist->enddate)->diffInDays(Carbon::parse($check_whitelist->startdate)), 'subscriptionDate' => $check_whitelist->startdate, 'expiryDate' => $check_whitelist->enddate, 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => 'N/A', 'subscriptionActivated' => true
                                        ];
                                    }
                            }
                        else
                            {
                                $check_whitelist = $user->organization->whitelist()->wherehas('product',
                                    function ($query) use ($request)
                                        {
                                            return $query->where('identifier',
                                                $request->product)->orWhere('product_id',
                                                $request->product);
                                        })->where('startdate', '<=',
                                    Carbon::now()->startOfDay()->toDateTimeString())->where('enddate',
                                    '>=',
                                    Carbon::now()->startOfDay()->toDateTimeString())->first();
                                if (!is_null($check_whitelist))
                                    {
                                        return [
                                            'identifier' => 'whitelisted', 'product' => $check_whitelist->product->product_name, 'productIdentifier' => $check_whitelist->product->identifier, 'type' => 'whitelist', 'period' => Carbon::parse($check_whitelist->enddate)->diffInDays(Carbon::parse($check_whitelist->startdate)), 'subscriptionDate' => $check_whitelist->startdate, 'expiryDate' => $check_whitelist->enddate, 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => 'N/A', 'subscriptionActivated' => true
                                        ];
                                    }
                                else
                                    {
                                        $subscription = B2bSubscription::with(['product'])->whereHas('users',
                                            function ($query) use ($user)
                                                {
                                                    return $query->where('user_id', $user->id);
                                                })->when($request->has('product'), function ($q) use ($request)
                                            {

                                                return $q->wherehas('product', function ($query) use ($request)
                                                    {

                                                        return $query->where('identifier',
                                                            $request->product)->orWhere('product_id',
                                                            $request->product);
                                                    });
                                            })->where('organization_id', $user->organization_id)->where('status',
                                            1)->when($request->has('subscription_date'),
                                            function ($query) use ($request)
                                                {
                                                    return $query->where('start_date', '<=',
                                                        Carbon::parse($request->subscription_date)->startOfDay()->toDateTimeString())->where('expiry_date',
                                                        '>=',
                                                        Carbon::parse($request->subscription_date)->endOfDay()->toDateTimeString());
                                                })->get();
                                        if ($subscription->isNotEmpty())
                                            {
                                                return response()->json([
                                                    'status' => true, 'data' => B2bSubscriptionResource::collection($subscription)
                                                ]);
                                            }
                                    }
                            }
                        $subscribed = Subscription::with([
                            'product', 'transaction', 'transaction.rate'
                        ])->whereDate('subscription_date', '<=',
                            Carbon::parse($request->subscription_date))->whereDate('expiry_date',
                            '>=',
                            Carbon::parse($request->subscription_date))->where('rate_id',
                            $request->rate_id)->where('user_id',
                            Auth::user()->id)->where('status',
                            1)->first();

                        $rate = Rate::with(['product'])->where('status', 1)->find($request->rate_id);
                        $subs = new stdClass();

                        if (is_null($subscribed))
                            {
                                $payment_method = PaymentMethod::whereIdentifier($request->payment_method_identifier)->first();
                                $subg           = SubscriptionGroup::firstOrCreate(['subdate' => Carbon::parse($request->subscription_date)->format('Y-m-d')],
                                    ['identifier' => Str::ulid()]);
                                $region         = Region::where('code', $request->region)->first();


                                $check2 = Subscription::with([
                                    'product', 'transaction', 'transaction.rate'
                                ])->whereDate('subscription_date', '<=',
                                    Carbon::parse($request->subscription_date))->whereDate('expiry_date',
                                    '>=',
                                    Carbon::parse($request->subscription_date))->where('product_id',
                                    $rate->product_id)
                                    //->where('rate_id', $request->rate_id)
                                                      ->where('user_id', Auth::user()->id)->where('status',
                                        0)->first();

                                if ($subg)
                                    {
                                        $subs   = Subscription::updateOrCreate([
                                            'user_id' => Auth::user()->id, 'product_id' => $rate->product_id, 'rate_id' => $rate->id, 'subscription_date' => Carbon::parse($request->subscription_date)->startOfDay()->toDateTimeString(), 'expiry_date' => Carbon::parse($request->subscription_date)->addDays($rate->period - 1)->endOfDay()->toDateTimeString()
                                        ], [
                                            'identifier'                    => $this->identifer('Subscription',
                                                'identifier',
                                                8), 'subscription_group_id' => $subg->id, 'reccuring' => $request->recurrent
                                        ]);
                                        $check  = Transaction::where('subscription_id',
                                            $subs->id)->where('status', 1)->first();
                                        $amount = 0;
                                        $bal    = $rate->cost;

                                        if (!is_null($check2))
                                            {
                                                $tr = Transaction::where('subscription_id',
                                                    $check2->id)->whereDate('created_at',
                                                    Carbon::now()->format('Y-m-d'))->first();
                                                if (!is_null($tr))
                                                    {
                                                        $bal = $tr->amount - $tr->amount_paid;
                                                        if ($bal == 0)
                                                            {
                                                                $check2->update(['status' => 1]);
                                                                $check2->subscription()->update(['status' => 1]);
                                                                return response()->json([
                                                                    'status' => true, 'subscription' => true, 'data' => $check2->subscription->refresh()
                                                                ]);
                                                            }
                                                    }
                                            }
                                        //dd($bal);

                                        if (
                                            in_array($region->code,
                                                explode(',', config('custom.CUSTOMER.COVERED_REGIONS')))
                                        )
                                            {
                                                $amount   = $this->currency_convert($bal, $rate->currency,
                                                    $region->currency_code);
                                                $currency = $region->currency_code;
                                            }
                                        else
                                            {
                                                $amount   = $this->currency_convert($bal, $rate->currency,
                                                    config('custom.BILLING.RESERVED_CURRENCY'));
                                                $currency = config('custom.BILLING.RESERVED_CURRENCY');
                                            }
                                        //dd($amount);
                                        $reserved_amount = $this->currency_convert($amount, $region->currency_code,
                                            config('custom.BILLING.RESERVED_CURRENCY'));
                                        //dd($rate->product_id);
                                        $coupon = null;
                                        $status = 0;
                                        if ($request->has('coupon'))
                                            {

                                                $cost         = $this->discount_calc($request->coupon, $amount,
                                                    $region, $rate->product_id,
                                                    Auth::user()->id, $rate->id);
                                                $discount     = $cost->discount;
                                                $amount       = $cost->amount;
                                                $total_amount = $cost->total_amount;
                                                if ($discount > 0)
                                                    {
                                                        $coupon = $request->get('coupon');
                                                        $cpn    = Coupon::where('code', $coupon)->first();
                                                        $cpn->increment('usage');
                                                        $cpn->save();
                                                        if ($amount >= 0)
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

                                        //dd($check);
                                        //if($check->where('status',0))
                                        $trans                           = new Transaction();
                                        $trans->identifier               = $this->identifer('Transaction',
                                            'identifier', 8);
                                        $trans->subscription_id          = $subs->id;
                                        $trans->payment_method_id        = $payment_method->id;
                                        $trans->{'channel'}              = $payment_method->name;
                                        $trans->total_amount             = $total_amount;
                                        $trans->amount                   = $amount;
                                        $trans->discount                 = $discount;
                                        $trans->coupon_code              = $coupon;
                                        $trans->currency                 = $currency;
                                        $trans->reserved_currency        = config('custom.BILLING.RESERVED_CURRENCY');
                                        $trans->reserved_currency_amount = $reserved_amount;
                                        $trans->status                   = $status;
                                        $trans->user_id                  = $request->user()->id;
                                        $trans->type                     = is_null($check) ? 'initial' : 'recurrent';
                                        $trans->save();
                                        //dd($trans);
                                        $subs->status = $status;
                                        if (is_null($check))
                                            {
                                                $subs->reccurent_cycle = 1;
                                            }
                                        else
                                            {
                                                $subs->increment('reccurent_cycle');
                                            }

                                        $subs->save();

                                        try
                                            {
                                                if ($status == 1)
                                                    {

                                                        try
                                                            {
                                                                if ($subs->transaction->rate->free_rate_id == $request->rate_id)
                                                                    {
                                                                        return [
                                                                            'identifier' => 'Free Rate', 'product' => $rate->product->product_name, 'productIdentifier' => $rate->product->identifier, 'type' => 'free rate', 'period' => $rate->period, 'subscriptionDate' => $subs->subscription_date, 'expiryDate' => Carbon::parse($subs->expiry_date)->gt(Carbon::parse($subs->transaction->rate->free_rate_end_date)) ? $subs->transaction->rate->free_rate_end_date : $subs->expiry_date, 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => true, 'subscriptionActivated' => true
                                                                        ];
                                                                    }
                                                            }
                                                        catch (Exception $e)
                                                            {
                                                                Log::error($e->getMessage());
                                                            }

                                                        return [
                                                            'status' => true, 'subscription' => true, 'data' => 'https://epaper.nation.africa', 'transaction_code' => $trans->identifier, 'SubscriptionActivated' => true
                                                        ];
                                                        //return response()->json(['status' => true, 'subscription' => true, 'data' => $subs->refresh()]);
                                                    }
                                                else
                                                    {
                                                        $bill = BillingLibrary::payment($subs, $trans,
                                                            $request->user(), $payment_method, $request->recurrent,
                                                            $region,
                                                            number_format(($trans->amount - $trans->amount_paid),
                                                                2), $currency, $request->back_url,
                                                            $request->redirect_url);
                                                        return response()->json($bill);
                                                    }


                                            }
                                        catch (Exception $e)
                                            {

                                                Log::info('Payment Error: ' . $e->getMessage());


                                            }
                                    }
                            }
                        else
                            {

                                if ($subscribed->transaction->rate->free_rate_id == $request->rate_id)
                                    {
                                        return [
                                            'identifier'        => 'Free Rate', 'product' => $rate->product->product_name,
                                            'productIdentifier' => $rate->product->identifier,
                                            'type'              => 'free rate', 'period' => $rate->period,
                                            'subscriptionDate'  => $subscribed->subscription_date,
                                            'expiryDate'        => Carbon::parse($subscribed->expiry_date)->gt(Carbon::parse($subscribed->transaction->rate->free_rate_end_date)) ? $subs->transaction->rate->free_rate_end_date : $subscribed->expiry_date, 'status' => (bool)1, 'recurrent' => (bool)0, 'subscriptionStatus' => true, 'subscriptionActivated' => true
                                        ];
                                    }

                                return response()->json([
                                    'status' => true, 'subscription' => true, 'data' => $subscribed
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

        public function notification_status(Request $request)
            {
                try
                    {
                        $user = User::find(Auth::user()->id);
                        if ($request->has('can_notify'))
                            {
                                $user->can_notify = $request->can_notify;
                                $user->meta()->insert([
                                    'user_id' => $user->id, 'action' => 'Notification -' . ($request->can_notify) ? 'Allow' : 'Deny', 'result' => Carbon::now(), 'ip_address' => $request->ip(), 'source' => $request->getHost(), 'date_created' => Carbon::now()->format('Y-m-d')
                                ]);
                            }
                        if ($request->has('allow_marketing'))
                            {
                                $user->allow_marketing = $request->allow_marketing;
                                $user->meta()->insert([
                                    'user_id' => $user->id, 'action' => 'Marketing -' . ($request->can_notify) ? 'Allow' : 'Deny', 'result' => Carbon::now(), 'ip_address' => $request->ip(), 'source' => $request->getHost(), 'date_created' => Carbon::now()->format('Y-m-d')
                                ]);
                            }
                        $res = $user->save();
                        if ($res)
                            {
                                return response()->json([
                                    'status' => true, 'user' => $user, 'data' => 'updated successfully'
                                ]);
                            }

                    }
                catch (Exception $e)
                    {
                        return response()->json([
                            'status' => false, 'user' => $user, 'data' => $e->getMessage()
                        ]);
                    }


            }

        public function success_payment($identifier)
            {
                $trans = Transaction::where('identifier', $identifier)
                                    ->first();
                if (!is_null($trans))
                    {
                        $this->data['account']      = $trans->identifier;
                        $this->data['amount']       = $trans->amount;
                        $this->data['payment_date'] = $trans->transaction_date;
                        return view('modules.front.success_payment', $this->data);
                    }
            }

        public function verify_transaction(Request $request)
            {
                $request->validate([
                    'account_number' => 'required',
                    'receipt_number' => 'required'
                ]);

                $account_number = $request->account_number;
                $receipt_number = $request->receipt_number;
                $transaction    = Transaction::with(['payment_method'])->where('identifier', $account_number)->first();
                $callback_url   = url('mobile-malipo/transaction/status');
                $timeout_url    = url('mobile-malipo/queue_timeout');
//        $callback_url   = 'https://dev-subscribe.nation.africa/mobile-malipo/transaction/status';
//        $timeout_url    = 'https://dev-subscribe.nation.africa/mobile-malipo/queue_timeout';

                if (!$transaction)
                    {
                        $cart        = Cart::where('identifier', $account_number)->first();
                        $transaction = Transaction::whereHas('subscription', function ($query) use ($cart)
                            {
                                $query->where('cart_id', $cart->id);
                            })->first();
                    }

                if ($transaction)
                    {
                        $result = mpesa_transaction_status($transaction->payment_method, $receipt_number, 4, $callback_url, $timeout_url, $account_number);

                        if (@$result->ResponseCode !== "0")
                            {
                                return response()->json([
                                    'message' => 'Mpesa API Error: ' . (@$result->ResponseDescription ?? @$result->errorMessage)
                                ], 400);
                            }

                        $conversation_id = $result->ConversationID;

                        if ($cart)
                            {
                                Transaction::whereHas('subscription', function ($query) use ($cart)
                                    {
                                        $query->where('cart_id', $cart->id);
                                    })->update(['conversation_id' => $conversation_id]);
                            }
                        else
                            {

                                $transaction->conversation_id = $conversation_id;
                                $transaction->save();
                            }

                        return response()->json([
                            'message' => "Success"
                        ]);
                    }

                return response()->json([
                    'message' => 'Invalid account number'
                ], 400);

            }

        public function add_subscription(Request $request)
            {
                try
                    {
                        $request->validate([
                            'product'     => 'required|exists:products,identifier',
                            'description' => 'required',
                            'rate_id'     => 'required|exists:rates,id',
                            //'days'=>'required'
                        ]);

                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['status' => false, 'data' => $e->validator->errors()->first()]);
                    }

                $user = Auth::user();

                $product = Product::where('identifier', $request->product)->first();
                $exists  = Subscription::where('product_id', $product->id)
                                       ->where('status', 1)
                                       ->where('user_id', $user->id)
                                       ->whereDate('expiry_date', '>=', date_create('now')->format('Y-m-d'))
                                       ->orderBy('expiry_date', 'desc')
                                       ->first();

                $rate = Rate::where('product_id', $product->id)
                            ->where('id', $request->rate_id)->where('status', 1)->first();

                $days       = $request->days ?? $rate->period;
                $start_date = Carbon::now()->startOfDay();
                if ($exists)
                    {
                        $start_date = Carbon::parse($exists->expiry_date)->addDays(1)->startOfDay();
                    }

                $end_date = Carbon::parse($start_date)->addDays($days ?? 1)->endOfDay();


                if (!$rate)
                    {
                        $rate = Rate::where('product_id', $product->id)
                                    ->orderBy('period', 'asc')->where('status', 1)->first();
                    }

                $rate->period = $request->days ?? $rate->period;

                $identifier = strtoupper(Str::random(8));

                $subg = SubscriptionGroup::firstOrCreate(['subdate' => Carbon::now()->format('Y-m-d')],
                    ['identifier' => Str::random(8)]);

//        $subscription = Subscription::where('product_id',$product->id)
//            ->where('status',1)
//            ->where('user_id',$user->id)
//            ->where('activator_reason',$request->description)
//            ->whereDate('expiry_date','>=',date_create('now')->format('Y-m-d'))
//            ->orderBy('expiry_date','desc')
//            ->first();
//
//        if($subscription)
//        {
//            $subscription->rate = $rate;
//            $collection =  collect([$subscription]);
//            $data = SubscriptionResource::collection($collection);
//            return response()->json(['status' => true, 'message' => 'Subscription already exists for '.$request->description,
//                'data' => $data ]);
//        }

                $subscription                        = new Subscription();
                $subscription->identifier            = $identifier;
                $subscription->product_id            = $product->id;
                $subscription->subscription_group_id = $subg->id;
                $subscription->subscription_date     = $start_date;
                $subscription->expiry_date           = $end_date;
                $subscription->rate_id               = @$rate->id;
                $subscription->status                = 1;
                $subscription->user_id               = $user->id;
                $subscription->activator_reason      = $request->description;
                $subscription->save();
                $identifier                        = strtoupper(Str::random(8));
                $payment_method                    = PaymentMethod::orderBy('id', 'asc')->limit(1)->first();
                $transaction                       = new Transaction();
                $transaction->identifier           = $identifier;
                $transaction->subscription_id      = $subscription->id;
                $transaction->payment_method_id    = $payment_method->id;
                $transaction->total_amount         = $rate->cost ?? $request->amount;
                $transaction->amount               = $rate->cost;
                $transaction->amount_paid          = $rate->cost;
                $transaction->channel              = $request->description ?? 'promo';
                $transaction->currency             = @$rate->currency ?? $request->currency;
                $transaction->receipt              = $request->receipt;
                $transaction->apple_transaction_id = $request->transaction_id;
                $transaction->status               = 1;
                $transaction->user_id              = $user->id;
                $transaction->save();

                $subscription->rate = $rate;

                $collection = collect([$subscription]);

                $data = SubscriptionResource::collection($collection);

                return response()->json([
                    'status'  => true,
                    'message' => 'Subscription added successfully',
                    'data'    => $data]);

            }

        public function apply_promocode(Request $request)
            {
                try
                    {
                        $request->validate([
                            'coupon'  => 'required',
                            'rate_id' => 'required|exists:rates,id',
                        ]);
                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['status' => false, 'data' => $e->validator->errors()->first()]);
                    }

                //$product  = Product::with(['site.region'])->where('identifier',$request->product)->first();
                $rate    = Rate::with(['product.site.region'])->where('id', $request->rate_id)->first();
                $product = $rate->product;

                $amount = $rate->cost;
                $region = $product->site->region;

                $transaction = null;
                if ($request->has('account'))
                    {
                        $transaction = Transaction::where('identifier', trim($request->account))
                                                  ->where('amount_paid', 0)
                                                  ->orderBy('id', 'desc')->limit(1)->first();

                        if (!$transaction)
                            return response()->json(['status' => false, 'data' => 'Invalid account number']);
                    }

                $coupon_code = trim($request->coupon);
                $coupon      = Coupon::where('code', $coupon_code)
                                     ->where(function ($query) use ($rate)
                                         {
                                             $query
                                                 ->where('rate_type', $rate->rate_type_id)
                                                 ->orWhereHas('rateTypes', function ($query) use ($rate)
                                                     {
                                                         $query->where('rate_type_id', $rate->rate_type_id);
                                                     });
                                         })
                                     ->whereJsonContains('products', $product->id)
                                     ->first();

                if (!$coupon)
                    return response()->json(['status' => false, 'data' => 'Coupon code not found']);

                if ($coupon->expires)
                    {
                        if (date_create($coupon->expiry_date) <= Carbon::now())
                            {
                                return response()->json(['status' => false, 'data' => 'Expired coupon']);
                            }
                    }

                if ($coupon->multi_use == 0)
                    {
                        $user  = Auth::user();
                        $check = Transaction::where('coupon_code', $coupon->code)
                                            ->where('user_id', $user->id)
                                            ->where('status', 1)
                                            ->first();

                        if ($check)
                            return response()->json(['status' => false, 'data' => 'Coupon already used by user. Multiple use per user not allowed']);
                    }

                $prorate = 0;

                $sub = null;

                if (auth()->check() && $request->upgrade == "true")
                    {
                        $user = Auth::user();

                        $sub = Subscription::with(['product.site.region', 'rate', 'transaction'])
                                           ->where(function ($q) use ($product)
                                               {
                                                   $q
                                                       ->where('product_id', $product->id)
                                                       ->orWhereHas('products', function ($q) use ($product)
                                                           {
                                                               $q->where('product_id', $product->id);
                                                           });
                                               })
                                           ->where('status', 1)
                                           ->where('user_id', $user->id)
                                           ->where('expiry_date', '>=', now())
                                           ->orderBy('expiry_date', 'desc')
                                           ->limit(1)
                                           ->first();

                        if ($sub)
                            {
                                $old_transaction = Transaction::where('subscription_id', $sub->id)
                                                              ->where('status', 1)
                                                              ->orderBy('id', 'desc')->limit(1)->first();
                                $original_cost   = ($old_transaction->amount_paid + $old_transaction->discount);
                                $original_days   = $sub->rate->period;
                                $remaining_days  = \Illuminate\Support\Carbon::parse($sub->expiry_date)->diffInDays(now());
                                if ($remaining_days > 0)
                                    $prorate = ceil(($original_cost / $original_days) * $remaining_days);

                                if ($prorate > 0)
                                    {
                                        $prorate = match_upgrade_currency($prorate, $sub->rate->currency, $rate->currency);
                                    }
                            }

                    }

                $amount   -= $prorate;
                $discount = 0;
                $discount += $prorate;

                $cost = $this->discount_calc($coupon->code, $amount,
                    $region, $rate->product_id,
                    $request->user()->id,
                    $rate->rate_type_id);

                $discount     += $cost->discount;
                $amount       = $cost->amount;
                $total_amount = $cost->total_amount - $prorate;

                if ($amount < 0)
                    $amount = 0;

                $status = 0;

                if ($transaction && $discount > 0)
                    {
                        $coupon->increment('usage');
                        $coupon->save();
                        if ($amount == 0)
                            {
                                $status = 1;
                            }

                        $transaction->coupon_code = $coupon->code;
                        $transaction->discount    = $discount;
                        $transaction->amount      = $amount;
                        $transaction->save();
                    }

                $newsub = null;

                if ($amount == 0)
                    {
                        $elapsed_days = 0;
                        if ($rate->category !== 'normal' && $rate->name !== 'Archive Issue' && today()->gt(Carbon::parse($request->subscription_date)))
                            {
                                $elapsed_days = today()->diffInDays(Carbon::parse($request->subscription_date));
                            }

                        if ($amount < 1)
                            {
                                $status = 1;
                            }

                        $payment_method = PaymentMethod::where('name', 'like', '%dpo%')->limit(1)->first();

                        $identifier                          = strtoupper(Str::random(8));
                        $start_date                          = now()->format('Y-m-d H:i:s');
                        $end_date                            = Carbon::parse($start_date)->addDays($rate->period + $elapsed_days)->toDateTimeString();
                        $subscription                        = new Subscription();
                        $subscription->identifier            = $identifier;
                        $subscription->product_id            = $sub->product_id;
                        $subscription->subscription_group_id = $sub->subscription_group_id;
                        $subscription->subscription_date     = $start_date;
                        $subscription->expiry_date           = $end_date;
                        $subscription->rate_id               = @$rate->id;
                        $subscription->status                = $status;
                        $subscription->user_id               = $user->id;
                        $subscription->category              = ($request->upgrade == "true") ? 'upgrade' : 'normal';
                        $subscription->activator_reason      = $request->description;
                        $subscription->type                  = @$rate->category;
                        $subscription->save();

                        $identifier                     = strtoupper(Str::random(8));
                        $transaction                    = new Transaction();
                        $transaction->identifier        = $identifier;
                        $transaction->subscription_id   = $subscription->id;
                        $transaction->payment_method_id = $payment_method->id;
                        $transaction->discount          = $discount;
                        $transaction->coupon_code       = $coupon_code;
                        $transaction->total_amount      = $total_amount;
                        $transaction->amount            = $amount;
                        $transaction->channel           = $payment_method->name;
                        $transaction->currency          = $rate->currency;
                        $transaction->status            = $status;
                        $transaction->user_id           = @$user->id;
                        $transaction->redirect_url      = $request->redirect_url;
                        $transaction->back_url          = $request->back_url;
                        $transaction->save();

                        attach_products($subscription);

                        deactivate_after_upgrade($transaction->identifier);

                        if ($sub)
                            {
                                $sub->deactivation_identifier = $transaction->identifier;
                                $sub->expire_after_upgrade    = 1;
                                $sub->save();
                            }

                        $newsub = new SubscriptionResource($subscription);
                    }

                $data = [
                    'status'          => true,
                    'amount'          => $amount,
                    'discount'        => $discount,
                    'original_amount' => $total_amount,
                    'data'            => $newsub,
                ];

                if ($transaction && $status == 1)
                    {
                        $transaction->subscription()->update(['status' => 1]);
                        $data['data'] = new SubscriptionResource($transaction->subscription);
                        return response()->json($data);
                    }
                else if ($transaction && $status == 0)
                    {
                        $data['data'] = new SubscriptionResource($transaction->subscription);
                        return response()->json($data);
                    }

                return response()->json($data);
            }

        public function upgrade_subscription(Request $request)
            {
                try
                    {
                        $request->validate([
                            'product'                   => 'required|exists:products,identifier',
                            'rate_id'                   => 'required|exists:rates,id',
                            'payment_method_identifier' => 'required|exists:payment_methods,identifier',
                        ]);
                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['status' => false, 'data' => $e->validator->errors()->first()]);
                    }

                $payment_method = PaymentMethod::where('identifier', $request->payment_method_identifier)->limit(1)->first();

                if ($payment_method->type == 'dpo')
                    {
                        try
                            {
                                $request->validate([
                                    'redirect_url' => 'required',
                                    'back_url'     => 'required',
                                ]);
                            }
                        catch (ValidationException $e)
                            {
                                return response()->json(['status' => false, 'data' => $e->validator->errors()->first()]);
                            }
                    }

                $user = Auth::user();

                $product = Product::where('identifier', $request->product)->first();

                $sub = Subscription::with(['product.site.region', 'rate', 'transaction'])
                                   ->where(function ($q) use ($product)
                                       {
                                           $q
                                               ->where('product_id', $product->id)
                                               ->orWhereHas('products', function ($q) use ($product)
                                                   {
                                                       $q->where('product_id', $product->id);
                                                   });
                                       })
                                   ->where('status', 1)
                                   ->where('user_id', $user->id)
                                   ->where('expiry_date', '>=', now())
                                   ->orderBy('expiry_date', 'desc')
                                   ->limit(1)
                                   ->first();

                $prorate = 0;

                if ($sub)
                    {
                        $old_transaction = Transaction::where('subscription_id', $sub->id)
                                                      ->where('status', 1)
                                                      ->orderBy('id', 'desc')->limit(1)->first();

                        $original_cost  = ($old_transaction->amount_paid + $old_transaction->discount);
                        $original_days  = $sub->rate->period;
                        $remaining_days = \Illuminate\Support\Carbon::parse($sub->expiry_date)->diffInDays(now());
                        if ($remaining_days > 0)
                            $prorate = ceil(($original_cost / $original_days) * $remaining_days);
                    }

                if (!$sub)
                    return response()->json(['status' => false, 'data' => 'Subscription is expired']);

                $rate = Rate::where('id', $request->rate_id)
                            ->where('product_id', $product->id)
                            ->first();

                if (!$rate)
                    return response()->json(['status' => false, 'data' => 'Invalid rate']);

                if ($prorate > 0)
                    {
                        $prorate = match_upgrade_currency($prorate, $sub->rate->currency, $rate->currency);
                    }

                $identifier = strtoupper(Str::random(8));

                $discount     = 0;
                $status       = 0;
                $amount       = $rate->cost;
                $total_amount = $rate->cost;
                $region       = @$sub->product->site->region;

                if (!$region)
                    return response()->json(['status' => false, 'data' => 'Invalid region']);

                $currency = $rate->currency;
                $coupon   = null;

                $amount   -= $prorate;
                $discount += $prorate;

                if ($request->has('coupon') && !empty(trim($request->coupon)))
                    {

                        $coupon       = trim($region->coupon);
                        $cost         = $this->discount_calc($request->coupon, $amount,
                            $region, $rate->product_id,
                            $request->user()->id,
                            $rate->rate_type_id);
                        $discount     += $cost->discount;
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

                $elapsed_days = 0;
                if ($rate->category !== 'normal' && $rate->name !== 'Archive Issue' && today()->gt(Carbon::parse($request->subscription_date)))
                    {
                        $elapsed_days = today()->diffInDays(Carbon::parse($request->subscription_date));
                    }

                if ($amount < 1)
                    {
                        $status = 1;
                    }

                $start_date                          = now()->format('Y-m-d H:i:s');
                $end_date                            = Carbon::parse($start_date)->addDays($rate->period + $elapsed_days)->toDateTimeString();
                $subscription                        = new Subscription();
                $subscription->identifier            = $identifier;
                $subscription->product_id            = $sub->product_id;
                $subscription->subscription_group_id = $sub->subscription_group_id;
                $subscription->subscription_date     = $start_date;
                $subscription->expiry_date           = $end_date;
                $subscription->rate_id               = @$rate->id;
                $subscription->status                = $status;
                $subscription->user_id               = $user->id;
                $subscription->category              = 'upgrade';
                $subscription->activator_reason      = $request->description;
                $subscription->type                  = @$rate->category;
                $subscription->save();

                $identifier                     = strtoupper(Str::random(8));
                $transaction                    = new Transaction();
                $transaction->identifier        = $identifier;
                $transaction->subscription_id   = $subscription->id;
                $transaction->payment_method_id = $payment_method->id;
                $transaction->discount          = $discount;
                $transaction->coupon_code       = $coupon;
                $transaction->total_amount      = $total_amount;
                $transaction->amount            = $amount;
                $transaction->channel           = $payment_method->name;
                $transaction->currency          = $rate->currency;
                $transaction->status            = $status;
                $transaction->user_id           = @$user->id;
                $transaction->redirect_url      = $request->redirect_url;
                $transaction->back_url          = $request->back_url;
                $transaction->save();

                attach_products($subscription);

                if ($sub)
                    {
                        $sub->deactivation_identifier = $transaction->identifier;
                        $sub->expire_after_upgrade    = 1;
                        $sub->save();
                    }

                if ($status == 1)
                    {
                        deactivate_after_upgrade($transaction->identifier);
                        return response()->json(new SubscriptionResource($subscription));
                    }

                $data = BillingLibrary::payment($subscription, $transaction,
                    $request->user(),
                    $payment_method,
                    $request->recurrent,
                    $region, (float)($amount),
                    $currency,
                    $request->back_url,
                    $request->redirect_url);

                return response()->json($data);
            }

        public function getRate(Request $request, $id)
            {
                $rate = Rate::where('id', $id)->first();
                if (!$rate)
                    return response()->json(['status' => false, 'data' => 'Invalid rate']);

                $data = new RateResource($rate);

                return response()->json(['status' => true, 'data' => $data]);
            }

        public function add_points(Request $request)
            {
                try
                    {
                        $request->validate([
                            'nickname' => 'required|string|max:255',
                            'points'   => 'required|integer|min:1|max:100',
                            'type'     => 'required|string|max:255'
                        ]);
                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['status' => false, 'data' => $e->validator->errors()->first()]);
                    }
                $user     = auth()->user();
                $points   = $request->points;
                $nickname = trim($request->nickname);

                $point = Point::where('nickname', $nickname)->first();
                if ($point && $point->user_id != $user->id)
                    {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Nickname is already taken',
                            'data'    => null], 200);
                    }

                $start_date = '2025-04-07';
                $expo       = "EXPO_" . $start_date;

                $point = Point::where('user_id', $user->id)->first();
                if ($point)
                    {
                        $point->increment('points', $points);
                        $point->identifier = $expo;
                        $point->save();
                    }
                else
                    {
                        $point             = new Point();
                        $point->user_id    = $user->id;
                        $point->nickname   = $nickname;
                        $point->points     = $points;
                        $point->identifier = $expo;
                        $point->save();
                    }

                $history = PointHistory::create([
                    'user_id'  => $point->user_id,
                    'point_id' => $point->id,
                    'points'   => $points,
                    'type'     => $request->type,
                ]);

                $environment = app()->isProduction() ? 'prod' : 'dev';

                $product_id = 'PW_NATION_AFRICA';

                if ($environment !== 'prod')
                    $product_id = 'nation.africa';

                $res = activate_coin_subscription($product_id, 'daily', $expo, 50);
                $sub = null;
                if ($res)
                    {
                        $collection = collect([$res]);
                        $sub        = @SubscriptionResource::collection($collection)[0];
                    }

                return response()->json([
                    'status'       => true,
                    'message'      => 'Points added successfully',
                    'data'         => $point,
                    'subscription' => $sub
                ]);
            }

        public function list_points(Request $request)
            {
                $offset = $request->offset ?? 0;
                $limit  = $request->size ?? 100;
                $total  = Point::where('points', '>', 0)->count();
                $points = Point::with(['user'])->orderBy('points', 'desc')
                               ->when($request->nickname, function ($query) use ($request)
                                   {
                                       return $query->with(['pointHistory'])->where('nickname', $request->nickname);
                                   })
                               ->where('points', '>', 0)
                               ->offset($offset)
                               ->limit($limit)
                               ->get();

                $points = $points->map(function ($point) use ($request)
                    {
                        $data = [
                            'id'       => $point->id,
                            'nickname' => $point->nickname,
                            'points'   => $point->points,
                        ];
                        if ($request->has('nickname'))
                            {
                                $data['history'] = $point->pointHistory;
                            }
                        return $data;
                    });

                return response()->json([
                    'status'        => true,
                    'message'       => 'List points',
                    'data'          => $points,
                    'total_entries' => $total
                ]);
            }

        public function check_nickname(Request $request)
            {
                try
                    {
                        $request->validate([
                            'nickname' => 'required|string',
                        ]);
                    }
                catch (ValidationException $e)
                    {
                        return response()->json(['status' => false, 'data' => $e->validator->errors()->first()]);
                    }

                $points = Point::where('nickname', $request->nickname)->limit(1)->first();
                if ($points)
                    {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Nickname is already taken',
                        ]);
                    }

                return response()->json([
                    'status'  => true,
                    'message' => 'Nickname is available',
                ]);
            }

        public function check_user_nickname(Request $request)
            {
                $user = \auth()->user();

                $points = Point::where('user_id', $user->id)->limit(1)->first();
                if ($points)
                    {
                        return response()->json([
                            'status'  => true,
                            'message' => 'User has a nickname',
                            'data'    => $points->nickname,
                        ]);
                    }

                return response()->json([
                    'status'  => false,
                    'message' => 'No nickname found for user ',
                    'data'    => null,
                ]);
            }

        public function list_events(Request $request)
            {
                $offset = $request->offset ?? 0;
                $limit  = $request->size ?? 100;
                $total  = MediaEvent::where('status', 'active')->count();
                $events = MediaEvent::where('status', 'active')
                                    ->offset($offset)
                                    ->limit($limit)
                                    ->orderBy('created_at', 'desc')
                                    ->get();

                return response()->json([
                    'status'        => true,
                    'message'       => 'List Events',
                    'data'          => $events,
                    'total_entries' => $total
                ]);
            }
    }
