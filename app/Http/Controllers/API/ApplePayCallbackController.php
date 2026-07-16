<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ApplePayLogs;
use App\Models\PaymentMethod;
use App\Models\Rate;
use App\Models\Subscription;
use App\Models\SubscriptionGroup;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class ApplePayCallbackController extends Controller
{
    public function get_transaction_id(Request $request)
    {
        $payload = [
            'headers' => $request->headers->all(),
            'body'    => $request->all(),
        ];

        ApplePayLogs::create([
            'source' =>'epaper',
            'request_payload' => $payload,
        ]);

        try
        {
            $check = Http::connectTimeout(3)
                ->timeout(10)
                ->retry([100, 500], throw: false)
                ->get(config('custom.APP.IOS_VERIFICATION'), ['transactionId' => $request->transactionId]);
            $token = $request->bearerToken();

            $userid = null;

            //dd('in check');


            if ($check->successful())
            {
                $authenticatedUser = Auth::guard('sanctum')->user();

                if ($authenticatedUser)
                {
                    $userid = $authenticatedUser->getAuthIdentifier();
                }
                else if(!is_null($token) && is_null($userid) )
                {
                    $userid = PersonalAccessToken::findToken($token)?->tokenable_id;
                }
                else
                {
                    $check2 = User::where('email', $request->email)
                        ->first();
                    if (!is_null($check2))
                    {
                        $userid = $check2->id;
                    }
                    else
                    {
                        $user = User::create([
                            'email'    => $request->email,
                            'status'   => 1,
                            'password' => bcrypt('Nation.1234'),
                            'type'     => 'customer'
                        ]);
                        $userid = $user->id;
                    }
                }
                $result = $check->object();
                //dd($result);
                if ($result->status && $result->statusCode == 200)
                {
                    if (!empty($result->data->transactions))
                    {
                        $transactions        = collect((array)$result->data->transactions);
                        $highestPurchaseDate = $transactions->max('purchaseDate');

                        $last_trans = $transactions->where('purchaseDate', $highestPurchaseDate)->first();
                        $rate = Rate::where('apple_product_id', $last_trans->productId)
                            ->first();

                        if (!is_null($rate))
                        {

                            $existing = Transaction::with(['subscription'])
                                ->where('receipt',$last_trans->webOrderLineItemId)->limit(1)->first();

                            if(is_null($existing))
                            {
                                $subg                                = SubscriptionGroup::firstOrCreate(
                                    [
                                        'subdate' => Carbon::createFromTimestamp($last_trans->purchaseDate / 1000)
                                    ],
                                    [
                                        'identifier' => $this->identifer('SubscriptionGroup', 'identifier')
                                    ]
                                );
                                $subscription                        = new Subscription();
                                $subscription->identifier            = $this->identifer('Subscription', 'identifier');
                                $subscription->subscription_group_id = $subg->id;
                                $subscription->product_id            = $rate->product_id;
                                $subscription->status                = 1;
                                $subscription->subscription_date     = Carbon::createFromTimestamp($last_trans->purchaseDate / 1000);
                                $subscription->rate_id               = $rate->id;
                                $subscription->expiry_date           = Carbon::createFromTimestamp($last_trans->expiresDate / 1000);
                                $subscription->user_id               = $userid;
                                $subscription->reccuring             = 1;
                                $subscription->reccurent_cycle       = 1;
                                $subscription->save();

                                $payment_method = PaymentMethod::orderBy('id','asc')->limit(1)->first();
                                $trans                           = new Transaction();
                                $trans->identifier               = $this->identifer('Transaction', 'identifier', 8);
                                $trans->subscription_id          = $subscription->id;
                                $trans->payment_method_id        = $payment_method->id;
                                $trans->receipt                  = $last_trans->webOrderLineItemId;
                                $trans->{'channel'}              = "Apple Pay";
                                $trans->total_amount             = $rate->cost;
                                $trans->amount                   = $rate->cost;
                                $trans->amount_paid              = $rate->cost;
                                $trans->discount                 = 0;
                                $trans->coupon_code              = "";
                                $trans->currency                 = $rate->currency;
                                $trans->reserved_currency        = config('custom.BILLING.RESERVED_CURRENCY');
                                $trans->reserved_currency_amount = $this->currency_convert($rate->cost, $rate->currency, config('custom.BILLING.RESERVED_CURRENCY'));
                                $trans->status                   = 1;
                                $trans->user_id                  = $userid;
                                $trans->type                     = 'initial';
                                $res                             = $trans->save();
                                if ($res)
                                {
                                    return response()->json(['status'       => true,
                                        'subscription' => true,
                                        'data'         => $subscription
                                    ]);
                                }
                            }
                            else
                            {
                                $subscription = $existing->subscription;

                                return response()->json(['status'       => true,
                                    'subscription' => true,
                                    'data'         => $subscription
                                ]);
                            }
                        }
                    }
                    else
                    {
                        return response()->json([
                            'status' => false,
                            'subscription' => false,
                            'data'   => "No transaction found"
                        ], 404);
                    }

                }
                else
                {
                    return response()->json([
                        'status' => false,
                        'subscription' => false,
                        'data'   => "Wrong transaction ID provided"
                    ], 301);
                }
            }
            else
            {
                return response()->json([
                    'status' => false,
                    'subscription' => false,
                    'data'   => "Server unreachable, try again later"
                ], 404);
            }
        }
        catch (\Exception $e)
        {
            return response()->json([
                'status' => false,
                'subscription' => false,
                'data'   => $e->getMessage()
            ], 301);
        }

    }
}
