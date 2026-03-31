<?php
namespace App\Http\Services;

use App\Exceptions\MissingField;
use App\Http\Resources\B2bSubscriptionResource;
use App\Libs\BillingLibrary;
use App\Models\B2bSubscription;
use App\Models\Coupon;
use App\Models\PaymentMethod;
use App\Models\Rate;
use App\Models\Region;
use App\Models\Subscription;
use App\Models\SubscriptionGroup;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Meta;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentService
    {
            use Meta;
        private function check_user($request)
            {
                $user = User::with([
                                       'whitelist',
                                       'organization',
                                       'organization.whitelist'
                                   ])
                            ->find($request->user()->id);
                if(!is_null($user))
                    {
                        return $user;
                    }


            }

    /**
     * @param $request
     * @return array|false
     */
        public function organization_whitelist($request)
            {
                $user = $this->check_user($request);
                $check_whitelist = $user->organization->whitelist()->wherehas('product', function ($query) use ($request)
                        {
                            return $query->where('identifier', $request->product)->orWhere('product_id', $request->product);
                        })->where('startdate', '<=', Carbon::now()->startOfDay()->toDateTimeString())->where('enddate', '>=', Carbon::now()->startOfDay()->toDateTimeString())->first();
                if (!is_null($check_whitelist))
                    {
                        return [
                            'identifier'            => 'whitelisted',
                            'product'               => $check_whitelist->product->product_name,
                            'productIdentifier'     => $check_whitelist->product->identifier,
                            'type'                  => 'whitelist',
                            'period'                => Carbon::parse($check_whitelist->enddate)->diffInDays(Carbon::parse($check_whitelist->startdate)),
                            'subscriptionDate'      => $check_whitelist->startdate,
                            'expiryDate'            => $check_whitelist->enddate,
                            'status'                => (bool)1,
                            'recurrent'             => (bool)0,
                            'subscriptionStatus'    => 'N/A',
                            'subscriptionActivated' => true
                        ];
                    }
                return false;
            }

        public function user_whitelist( $request)
            {
                $user = $this->check_user($request);
                $check_whitelist = $user->whitelist()->wherehas('product', function ($query) use ($request)
                        {
                            return $query->where('identifier', $request->product)->orWhere('product_id', $request->product);
                        })->where('startdate', '<=', Carbon::now()->startOfDay()->toDateTimeString())->where('enddate', '>=', Carbon::now()->endOfDay()->toDateTimeString())->first();
                if (!is_null($check_whitelist))
                    {
                        return [
                            'identifier'            => 'whitelisted',
                            'product'               => $check_whitelist->product->product_name,
                            'productIdentifier'     => $check_whitelist->product->identifier,
                            'type'                  => 'whitelist',
                            'period'                => Carbon::parse($check_whitelist->enddate)->diffInDays(Carbon::parse($check_whitelist->startdate)),
                            'subscriptionDate'      => $check_whitelist->startdate,
                            'expiryDate'            => $check_whitelist->enddate,
                            'status'                => (bool)1,
                            'recurrent'             => (bool)0,
                            'subscriptionStatus'    => 'N/A',
                            'subscriptionActivated' => true
                        ];
                    }
                return false;
            }

        public function organization_subscription($request, $user)
            {
                $subscription = B2bSubscription::with(['product'])->whereHas('users', function ($query) use ($user)
                        {
                            return $query->where('user_id', $user->id);
                        })->when($request->has('product'), function ($q) use ($request)
                        {
                            return $q->wherehas('product', function ($query) use ($request)
                                {

                                    return $query->where('identifier', $request->product)->orWhere('product_id', $request->product);
                                });
                        })->where('organization_id', $user->organization_id)->where('status', 1)->when($request->has('subscription_date'), function ($query) use ($request)
                        {
                            return $query->where('start_date', '<=', Carbon::parse($request->subscription_date)->format('Y-m-d H:is'))->where('expiry_date', '>=', Carbon::parse($request->subscription_date)->format('Y-m-d H:is'));
                        })->get();
                if ($subscription->isNotEmpty())
                    {
                        return response()->json([
                                                    'status' => true,
                                                    'data'   => B2bSubscriptionResource::collection($subscription)
                                                ]);
                    }
                return false;
            }

        public function check_subscription($request)
            {
                $subscribed = Subscription::with([
                                                     'product',
                                                     'transaction',
                                                     'transaction.rate'
                                                 ])->where('subscription_date', '<=', Carbon::parse($request->subscription_date)->format('Y-m-d H:is'))->where('expiry_date', '>=', Carbon::parse($request->subscription_date)->format('Y-m-d H:is'))->where('rate_id', $request->rate_id)->where('user_id', $request->user()->id)->where('status', 1)->first();
                if (!is_null($subscribed))
                    {
                        return response()->json([
                                                    'status'                => true,
                                                    'subscription'          => true,
                                                    'data'                  => $subscribed,
                                                    'subscriptionActivated' => true
                                                ]);
                    }
                return false;
            }

        public function subscribe($request)
            {
                $payment_method = PaymentMethod::whereIdentifier($request->payment_method_identifier)->first();
                if (is_null($payment_method))
                    {
                        return response()->json([
                                                    'status' => false,
                                                    'error'  => 'payment method not found!'
                                                ], 403);
                    }
                $rate = Rate::with(['product'])->where('status', 1)->find($request->rate_id);
                if ($rate->cost == 0)
                    {
                        return [
                            'identifier'            => 'whitelisted',
                            'product'               => $rate->product->product_name,
                            'productIdentifier'     => $rate->product->identifier,
                            'type'                  => 'whitelist',
                            'period'                => 1,
                            'subscriptionDate'      => Carbon::now()->startOfDay(),
                            'expiryDate'            => Carbon::now()->endOfDay(),
                            'status'                => (bool)1,
                            'recurrent'             => (bool)0,
                            'subscriptionStatus'    => 'N/A',
                            'subscriptionActivated' => true
                        ];
                    }
                $subg = SubscriptionGroup::firstOrCreate([
                                                             'subdate' => Carbon::parse($request->subscription_date)->format('Y-m-d')
                                                         ], [
                                                             'identifier' => Str::ulid()
                                                         ]);
                if ($subg)
                    {
                        $start_date   = ($rate->product->type == 'paywall') ? Carbon::parse($request->subscription_date)->toDateTimeString() : Carbon::parse($request->subscription_date)->startOfDay()->toDateTimeString();
                        $end_date     = ($rate->product->type == 'paywall') ? Carbon::parse($request->subscription_date)->addDays($rate->period)->toDateTimeString() : Carbon::parse($request->subscription_date)->addDays($rate->period - 1)->endOfDay()->toDateTimeString();
                        $subscription = Subscription::with([
                                                               'product',
                                                               'transaction',
                                                               'transaction.rate'
                                                           ])->where('subscription_date', '<=', $start_date)->where('expiry_date', '>=', $end_date)->where('product_id', $rate->product_id)->where('rate_id', $request->rate_id)->where('user_id', $request->user()->id)->where('status', 0)->first();
                        $bal          = $rate->cost;
                        if (is_null($subscription))
                            {
                                $subscription    = Subscription::updateOrCreate([
                                                                                    'user_id' => $request->user()->id,
'product_id' => $rate->product_id,
'rate_id' => $rate->id,
'subscription_date' => $start_date,
'expiry_date' => $end_date
                                                                                ], [
                                                                                    'identifier'            => $this->identifer('Subscription', 'identifier', 8),
                                                                                    'subscription_group_id' => $subg->id,
                                                                                    'reccuring'             => $request->recurrent,
                                                                                    "status"                => 0
                                                                                ]);
                                $reserved_amount = $this->currency_convert($amount, $rate->currency, config('custom.BILLING.RESERVED_CURRENCY'));
                                $coupon          = null;
                                $status          = 0;
                                if ($request->has('coupon'))
                                    {

                                        $cost = $this->discount_calc($request->coupon, $amount, $region, $rate->product_id, Auth::user()->id, $rate->rate_type_id);

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

                                //dd($check);
                                //if($check->where('status',0))
                                $trans                           = new Transaction();
                                $trans->identifier               = $this->identifer('Transaction', 'identifier', 8);
                                $trans->subscription_id          = $subscription->id;
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

                            }
                        else
                            {
                                $transaction = Transaction::where('subscription_id', $subscription->id)->whereDate('created_at', Carbon::now()->format('Y-m-d'))->wherehas('subscription', function ($query) use ($request)
                                        {
                                            return $query->where('rate_id', $request->rate);
                                        })->first();
                                if (!is_null($transaction))
                                    {
                                        $bal = $transaction->amount - $transaction->amount_paid;
                                        if ($bal == 0)
                                            {
                                                $tansaction->update(['status' => 1]);
                                                $transaction->subscription()->update(['status' => 1]);
                                                return response()->json([
                                                                            'status'                => true,
                                                                            'subscription'          => true,
                                                                            'data'                  => $transaction->subscription->refresh(),
                                                                            'subscriptionActivated' => true
                                                                        ]);
                                            }
                                    }
                            }
                        if ($rate->currency == $region->currency_code)
                            {
                                $amount   = $bal;
                                $currency = $region->currency_code;
                            }
                        else
                            {
                                if (in_array($region->code, explode(',', config('custom.CUSTOMER.COVERED_REGIONS'))))
                                    {
                                        $amount   = $this->currency_convert($bal, $rate->currency, $region->currency_code);
                                        $currency = $region->currency_code;
                                    }
                                else
                                    {
                                        $amount   = $this->currency_convert($bal, $rate->currency, config('custom.BILLING.RESERVED_CURRENCY'));
                                        $currency = config('custom.BILLING.RESERVED_CURRENCY');
                                    }
                            }
                        if ($status == 1)
                            {
                                return [
                                    'status'                => true,
                                    'subscription'          => true,
                                    'data'                  => 'https://epaper.nation.africa',
                                    'transaction_code'      => $trans->identifier,
                                    'SubscriptionActivated' => true
                                ];
                                /*return response()->json(['status' => true, 'subscription' => true, 'data' => new SubscriptionResource($subs->refresh())]);*/
                            }
                        else
                            {
                                $bill = BillingLibrary::payment($subs, $trans, $request->user(), $payment_method, $request->recurrent, $region, (float)($amount), $currency, $request->back_url, $request->redirect_url);
                                //Log::error($trans->amount);
                                return response()->json($bill);
                            }


                    }

            }
    }
