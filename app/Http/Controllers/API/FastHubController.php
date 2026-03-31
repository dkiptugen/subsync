<?php

namespace App\Http\Controllers\API;

use App\Enums\PaymentStageEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Libs\FastHub;
use App\Libs\Mpesa;
use App\Models\Coupon;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Rate;
use App\Models\Region;
use App\Models\Subscription;
use App\Models\SubscriptionGroup;
use App\Models\Transaction;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FastHubController extends Controller
{
    //
    public function fasthub_payment(Request $request)
    {
        try{
            $request->validate([
                'payment_method_identifier' => 'required|exists:payment_methods,identifier',
                'rate_id'                   => 'required|exists:rates,id',
                'subscription_date'         => 'required',
                'product'                   => 'required|exists:products,identifier',
                'msisdn'                   => 'required',
            ]);
        }catch (ValidationException $e)
        {
            return response()->json([
                'status' => false,
                'data'   => $e->validator->errors()->first()
            ]);
        }

        $payment_method = PaymentMethod::whereIdentifier($request->payment_method_identifier)->where('type','fasthub')->first();
        $region         = Region::where('code', 'TZ')->first();
        $product         = Product::where('identifier', $request->product)->first();
        $subscription_date = Carbon::parse($request->subscription_date);
        $subg = SubscriptionGroup::firstOrCreate([
            'subdate' => $subscription_date->format('Y-m-d')
        ], [
            'identifier' => Str::ulid()
        ]);

        $rate = Rate::with(['product'])->where('status', 1)->find($request->rate_id);

        if (!is_null($payment_method))
        {
            $subscription_date = Carbon::parse($request->subscription_date);
            if($product->type == 'paywall')
            {
                $subscription_date = Carbon::parse($request->subscription_date . ' ' . now()->format('H:i:s'));
            }

            $subscribed = Subscription::with(['product', 'transaction', 'transaction.rate'])->where('subscription_date',
                '<=',
                $subscription_date->toDateTimeString())->where('expiry_date',
                '>=',
                $subscription_date->toDateTimeString())->where('rate_id',
                $request->rate_id)->where('user_id',
                $request->user()->id)->where('status',
                1)->first();


            if (is_null($subscribed))
            {

                $subg = SubscriptionGroup::firstOrCreate([
                    'subdate' => $subscription_date->format('Y-m-d')
                ], [
                    'identifier' => Str::ulid()
                ]);

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


                if ($subg)
                {
                    $start_date      = ($rate->product->type == 'paywall') ? $subscription_date->toDateTimeString() : $subscription_date->startOfDay()->toDateTimeString();
                    $end_date        = ($rate->product->type == 'paywall') ? $subscription_date->addDays(($rate->period + $rate->compensation_days))->toDateTimeString() : $subscription_date->addDays(($rate->period + $rate->compensation_days) - 1)->endOfDay()->toDateTimeString();
                    $subs            = Subscription::updateOrCreate([
                        'user_id' => $request->user()->id, 'product_id' => $rate->product_id, 'rate_id' => $rate->id, 'subscription_date' => $start_date, 'expiry_date' => $end_date
                    ], [
                        'identifier' => $this->identifer('Subscription',
                            'identifier',
                            8), 'subscription_group_id' => $subg->id, 'reccuring' => 0
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
                    if ($request->has('coupon'))
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


                    try
                    {
                        $util = new FastHub($payment_method);
                    }
                    catch (\Exception $e)
                    {
                        report($e);
                    }

                    $data = [
                        'amount' => $amount, 'account' => $trans->identifier, 'sub' => $subs->identifier,
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

    public function callback(Request $request)
    {

    }
}
