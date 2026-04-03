<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscription;
use App\Models\Product;
use App\Models\Rate;
use App\Models\RateType;
use App\Models\Subscription;
use App\Models\SubscriptionGroup;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {

        return view('modules.subscription.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {

        $this->data['rates'] = RateType::get();
        $this->data['products'] = Product::whereStatus(1)
            ->get();

        return view('modules.subscription.add', $this->data);
    }

    /**
     * @return JsonResponse
     */
    public function get(Request $request)
    {

        $columns = ['id', 'identifier', 'product_id', 'transactions', 'unit_cost', 'amount_paid', 'receipt', 'reccuring', 'subscription_date', 'expiry_date', 'status', 'category'];
        $subscription = Subscription::query();
        $subscription->with(['product', 'user', 'rate', 'transaction']);
        $totalData = $subscription->count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if ($order == 'id') {
            $order = 'subscriptions.id';
        }

        if (empty($request->input('search.value'))) {
            $posts = $subscription->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {

            $search = $request->input('search.value');
            $sub = $subscription->where(function ($query) use ($search) {

                if (str_contains($search, '@')) {
                    $query->whereHas('user', function ($ql) use ($search) {
                        return $ql->where('email', 'LIKE', "{$search}%");
                    });
                } else {
                    $query->whereHas('transaction', function ($ql) use ($search) {
                        $ql->where('receipt', 'LIKE', "{$search}%");
                    });
                }
            });

            /* ->whereHas('product', function ($ql) use ($search)
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
             ->orWhere('expiry_date', 'LIKE', "%{$search}%")*/
            $posts = $sub->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = $sub->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            $i = 0;
            foreach ($posts as $post) {
                $additional = [];
                // $actionbtn = self::button_generate('subscription' ,$post->id ,$additional);
                $nestedData['pos'] = $pos;
                $nestedData['identifier'] = $post->identifier;
                $nestedData['product'] = optional($post->product)->product_name.'('.optional($post->product)->type.')';
                $nestedData['st'] = $post->rate->name;
                $nestedData['transactions'] = '<a href="'.route('subscription.transaction.index', $post->id).'">'.optional($post->transaction)->count() ?? 1 .'</a>';
                $nestedData['cost'] = $post->rate->currency.' '.number_format($post->rate->cost, 2);
                $nestedData['amount_paid'] = $post->rate->currency.' '.number_format($post->transaction->where('status', 1)->sum('amount_paid'), 2);
                $nestedData['receipt'] = @$post->transaction->last()->receipt;
                $nestedData['recurrent'] = $post->reccuring == 0 ? 'No' : 'Yes';
                $nestedData['subdate'] = $post->subscription_date;
                $nestedData['expirydate'] = $post->expiry_date;
                $nestedData['name'] = optional($post->user)->name;
                $nestedData['email'] = optional($post->user)->email;
                $nestedData['status'] = $post->status ? 'Active' : 'Inactive';
                $nestedData['category'] = ($post->type);
                // $nestedData['action'] = $actionbtn;
                $data[] = $nestedData;
                $pos++;

            }
        }

        $json_data = ['draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

        return response()->json($json_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return array|RedirectResponse
     */
    public function store(StoreSubscription $request)
    {

        $rate = Rate::where('product_id', $request->product)
            ->where('rate_type_id', $request->rate)
            ->limit(1)
            ->first();
        $user = User::where('email', $request->email)
            ->first();

        if (in_array($request->bundle, ['premium', 'premium plus'])) {
            $bundle_rate = Rate::where('product_id', $request->product)
                ->where('rate_type_id', $request->rate)
                ->where('currency', $request->currency)
                ->where('category', $request->bundle)
                ->limit(1)
                ->first();

            if ($bundle_rate) {
                $rate = $bundle_rate;
            }
        }

        if (is_null($user)) {
            return self::failed('Subscription', 'This user does not have an account, create his/her account', route('product.subscriber.create', 0));
        }
        if (is_null($rate)) {
            return self::failed('Subscription', 'This subscription type is not available for this product', route('subscription.index'));
        } else {
            $subg = SubscriptionGroup::firstOrCreate([
                'subdate' => Carbon::parse($request->startdate)->format('Y-m-d'),
            ], [
                'identifier' => $this->identifer('SubscriptionGroup', 'identifier'),
            ]);
            if ($subg) {
                $subs = Subscription::updateOrCreate([
                    'subscription_date' => Carbon::parse($request->startdate)->startOfDay(),
                    'expiry_date' => Carbon::parse($request->startdate)->addDays($rate->period - 1)->endOfDay(),
                    'user_id' => $user->id,
                    'product_id' => $request->product,
                ],
                    [
                        'identifier' => $this->identifer('Subscription', 'identifier'),
                        'subscription_group_id' => $subg->id,
                        'reccurent_cycle' => 1,
                        'rate_id' => $rate->id,
                        'reccuring' => 0,
                        'status' => 1,
                        'finance_approval_status' => 3,
                        'activator_id' => Auth::user()->id,
                        'activator_reason' => $request->reason,
                        'type' => $request->bundle,
                    ]);
                if ($subs) {

                    $trans = new Transaction;
                    $trans->identifier = $this->identifer('Transaction', 'identifier');
                    $trans->subscription_id = $subs->id;
                    $trans->payment_method_id = 1;
                    $trans->{'channel'} = $request->channel;
                    $trans->receipt = $request->receipt;
                    $trans->initiator = $user->email;
                    $trans->total_amount = $request->amount;
                    $trans->amount = $request->amount;
                    $trans->status = 1;
                    $trans->user_id = $user->id;
                    $trans->currency = $request->currency;
                    $trans->reserved_currency = 'USD';
                    $trans->reserved_currency_amount = $this->currency_convert($request->amount, $request->currency, 'USD');
                    $trans->transaction_date = Carbon::now()->toDateTimeString();
                    $trans->amount_paid = $request->amount;
                    $trans->type = 'initial';
                    $res = $trans->save();
                    if ($res) {
                        if (in_array($request->bundle, ['premium', 'premium plus'])) {
                            $product = Product::with(['children', 'counterpart'])->where('id', $request->product)->first();
                            $ids = [];

                            if ($request->bundle == 'premium') {
                                if (! is_null($product->counterpart)) {
                                    array_push($ids, $product->counterpart->id);
                                }

                            } elseif ($request->bundle == 'premium plus') {
                                if (! $product->children->isEmpty()) {
                                    $ids = $product->children->pluck('id')->toArray();
                                }
                            }
                            $subs->products()->attach($ids);
                            // attach_products($subs);
                        }

                        try {
                            $user->notify(new NewSubscriptionNotification($user, $rate->product));
                        } catch (Exception $e) {
                            Log::error($e->getMessage());
                        }

                        return self::success('Subscription', 'This subscription was added successfully', route('subscription.index'));
                    }

                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function show($id)
    {

        return view('modules.subscription.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function edit($id)
    {

        return view('modules.subscription.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
