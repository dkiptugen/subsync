<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rate;
use App\Models\RateType;
use App\Models\Subscription;
use App\Models\SubscriptionGroup;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriberSubsController extends Controller
    {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function index(int $userid)
            {

                $this->data['user'] = User::find($userid);

                return view('modules.subscribersub.index', $this->data);
            }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function create($userid)
            {

                $this->data['user']     = User::find($userid);
                $this->data['rates']    = RateType::get();
                $this->data['products'] = Product::whereStatus(1)
                                                 ->get();

                return view('modules.subscribersub.add', $this->data);
            }

        public function get(Request $request, $userid)
            {

                $columns      = ['id', 'identifier', 'recurrent_cycle', 'unit_cost', 'amount_paid','receipt' ,'reccuring', 'subscription_date', 'expiry_date', 'status','updatedate'];
                $subscription = Subscription::query();
                $subscription->with(['product', 'user', 'rate', 'payment_method', 'transaction']);
                $subscription->where('user_id', $userid);
                $totalData     = $subscription->count();
                $totalFiltered = $totalData;
                $limit         = $request->input('length');
                $start         = $request->input('start');
                $order         = $columns[$request->input('order.0.column')];
                $dir           = $request->input('order.0.dir');

                if (empty($request->input('search.value')))
                    {
                        $posts = $subscription->offset($start)
                                              ->limit($limit)
                                              ->orderBy($order, $dir)
                                              ->get();
                    }
                else
                    {

                        $search = $request->input('search.value');
                        $sub    = $subscription->whereHas('product', function ($ql) use ($search)
                            {

                                return $ql->where('product_name', 'LIKE', "%{$search}%");
                            })
                                               ->whereHas('rate', function ($ql) use ($search)
                                                   {

                                                       return $ql->where('name', 'LIKE', "%{$search}%");
                                                   })
                                               ->orWhereHas('transaction.payment_method', function ($ql) use ($search)
                                                   {

                                                       return $ql->where('name', 'LIKE', "%{$search}%");
                                                   })
                                               ->orWhere('subscription_date', 'LIKE', "%{$search}%")
                                               ->orWhere('expiry_date', 'LIKE', "%{$search}%");
                        $posts  = $sub->offset($start)
                                      ->limit($limit)
                                      ->orderBy($order, $dir)
                                      ->get();

                        $totalFiltered = $sub->count();
                    }

                $data = [];
                if (!empty($posts))
                    {
                        $pos = $start + 1;
                        $i   = 0;
                        foreach ($posts as $post)
                            {
                                $additional                 = [];
                                $actionbtn                  = self::button_generate('user.subscription', [$post->user_id, $post->id], $additional, ['destroy', 'show']);
                                $nestedData['pos']          = $pos;
                                $nestedData['identifier']   = $post->identifier;
                                $nestedData['product']      = optional($post->product)->product_name;
                                $nestedData['st']           = @$post->rate->name;
                                $nestedData['transactions'] = '<a href="' . route('subscription.transaction.index', $post->id) . '">' . (optional($post->transaction)->count() ?? 1 ). '</a>';
                                $nestedData['cost']         = $post->transaction->last()->currency ?? '' . ' ' . number_format($post->rate->cost, 2) . '/=';
                                $nestedData['amount_paid']  = $post->transaction->last()->currency ?? '' . ' ' . number_format($post->transaction->sum('amount_paid'), 2) . '/=';
                                $nestedData['receipt']      = @$post->transaction->last()->receipt;
                                $nestedData['recurrent']    = $post->reccuring == 0 ? 'No' : 'Yes';
                                $nestedData['subdate']      = $post->subscription_date;
                                $nestedData['expirydate']   = $post->expiry_date;
                                $nestedData['status']       = $post->status ? 'Active' : 'Inactive';
                                $nestedData['updatedate']   = date_create($post->updated_at)->format('Y-m-d H:i:s');
                                $nestedData['action']       = $actionbtn;
                                $data[]                     = $nestedData;
                                $pos++;

                            }
                    }

                $json_data = ['draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

                return response()->json($json_data);
            }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
        public function store(Request $request, $userid)
            {

                try
                    {
                        $rate = Rate::where('product_id', $request->product)
                                    ->where('rate_type_id', $request->rate)
                                    ->first();
                        $user = User::find($userid);
                        if (is_null($rate))
                            {
                                return self::fail('Subscription', 'This subscription type is not available for this product', route('user.subscription.index', $userid));
                            }
                        else
                            {
                                $subg = SubscriptionGroup::firstOrCreate([
                                                                             'subdate' => Carbon::parse($request->startdate)->format('Y-m-d'),
                                                                         ]
                                    ,                                    [
                                                                             'identifier' => $this->identifer('SubscriptionGroup', 'identifier')
                                                                         ]);
                                if ($subg)
                                    {
                                        $subs = Subscription::updateOrCreate([
                                                                                 'subscription_date' => Carbon::parse($request->startdate)->startOfDay(),
                                                                                 'expiry_date'       => Carbon::parse($request->startdate)->addDays($rate->period - 1)->endOfDay(),
                                                                                 'user_id'           => $userid,
                                                                                 'product_id'        => $request->product
                                                                             ],
                                                                             [
                                                                                 'identifier'            => $this->identifer('Subscription', 'identifier'),
                                                                                 'subscription_group_id' => $subg->id,
                                                                                 'reccurent_cycle'       => 1,
                                                                                 'rate_id'               => $rate->id,
                                                                                 'reccuring'             => 0,
                                                                                 'status'                => 1,
                                                                                 'activator_id'          => Auth::user()->id,
                                                                                 'activator_reason'      => $request->reason
                                                                             ]);
                                        if ($subs)
                                            {

                                                $trans                           = new Transaction();
                                                $trans->identifier               = $this->identifer('Transaction', 'identifier');
                                                $trans->subscription_id          = $subs->id;
                                                $trans->payment_method_id        = 1;
                                                $trans->{'channel'}              = $request->channel;
                                                $trans->receipt                  = $request->receipt;
                                                $trans->initiator                = $user->email;
                                                $trans->total_amount             = $request->amount;
                                                $trans->amount                   = $request->amount;
                                                $trans->status                   = 1;
                                                $trans->user_id                  = $userid;
                                                $trans->currency                 = $request->currency;
                                                $trans->reserved_currency        = 'USD';
                                                $trans->reserved_currency_amount = $this->currency_convert($request->amount, $request->currency, 'USD');
                                                $trans->transaction_date         = Carbon::now()->toDateTimeString();
                                                $trans->amount_paid              = $request->amount;
                                                $trans->type                     = 'initial';
                                                $trans->save();
                                                try
                                                    {
                                                        $user->notify(new NewSubscriptionNotification($user, $rate->product));
                                                    }
                                                catch (Exception $e)
                                                    {
                                                        Log::error($e->getMessage());
                                                    }
                                                return self::success('Subscription', 'saved successfully', route('user.subscription.index', $userid));
                                            }
                                    }
                            }
                    }
                catch (Exception $e)
                    {
                        return self::fail('Subscription', $e->getMessage(), route('user.subscription.index', $userid));
                    }

            }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function show($userid, $id)
            {

                $this->data['user']         = User::find($userid);
                $this->data['subscription'] = Subscription::find($id);

                return view('modules.subscribersub.view', $this->data);
            }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function edit($userid, $id)
            {

                $this->data['user']         = User::find($userid);
                $this->data['subscription'] = Subscription::with(['transaction'])->whereId($id)->first();
                $this->data['transaction']  = $this->data['subscription']->transaction->last();

                $this->data['rates']    = RateType::get();
                $this->data['products'] = Product::whereStatus(1)
                                                 ->get();

                return view('modules.subscribersub.edit', $this->data);
            }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return array
     */
        public function update(Request $request, $userid, $id)
            {

                //return self::fail('Subscription', 'This subscription type is not available for this product', route('user.subscription.index', $userid));
                try
                    {
                        $rate = Rate::where('product_id', $request->product)
                                    ->where('rate_type_id', $request->rate)
                                    ->first();
                        $user = User::find($userid);
                        if (is_null($rate))
                            {
                                return self::fail('Subscription', 'This subscription type is not available for this product', route('user.subscription.index', $userid));
                            }
                        else
                            {
                                $subg = SubscriptionGroup::firstOrCreate([
                                                                             'subdate' => Carbon::parse($request->startdate)->format('Y-m-d'),
                                                                         ]
                                    ,                                    [
                                                                             'identifier' => $this->identifer('SubscriptionGroup', 'identifier')
                                                                         ]);
                                if ($subg)
                                    {
                                        $subs = Subscription::find($id)
                                                            ->update([
                                                                         'subscription_group_id' => $subg->id,
                                                                         'rate_id'               => $rate->id,
                                                                         'status'                => $request->status ?? 0,
                                                                         'activator_id'          => Auth::user()->id,
                                                                         'activator_reason'      => $request->reason
                                                                     ]);
                                        if ($subs)
                                            {

                                                $trans = Transaction::where('subscription_id', $id)
                                                                    ->orderBy('id', 'DESC')
                                                                    ->first();
                                                if (is_null($trans))
                                                    {
                                                        $trans->identifier = self::identifer('Transaction', 'identifier');
                                                    }
                                                $trans->{'channel'} = $request->channel;
                                                $trans->receipt     = $request->receipt;
                                                $trans->initiator   = $user->email;
                                                $trans->status      = $request->status ?? 0;
                                                $trans->user_id     = $userid;
                                                $trans->currency    = $request->currency;
                                                $trans->amount_paid = $request->amount;
                                                $trans->save();

                                                return self::success('Subscription', 'saved successfully', route('user.subscription.index', $userid));
                                            }

                                        return self::fail('Subscription', 'subs failed', route('user.subscription.index', $userid));

                                    }

                                return self::fail('Subscription', 'failed', route('user.subscription.index', $userid));
                            }

                    }
                catch (Exception $e)
                    {
                        Log::error($e->getMessage());

                        return self::fail('Subscription', $e->getMessage(), route('user.subscription.index', $userid));
                    }
            }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
        public function destroy($userid, $id)
            {
                //
            }
    }
