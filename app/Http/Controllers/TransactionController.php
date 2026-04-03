<?php

namespace App\Http\Controllers;

use App\Libs\DPO;
use App\Models\Subscription as Sub;
use App\Models\Transaction;
use App\Traits\Meta;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class TransactionController extends Controller
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
    public function index($sub)
    {

        $this->data['subscription_id'] = $sub;

        return view('modules.transaction.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create($sub)
    {

        $this->data['subscription_id'] = $sub;

        return view('modules.transaction.add', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function show($id)
    {

        return view('modules.transaction.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function edit($sub, $id)
    {

        $this->data['transaction'] = Transaction::find($id);
        $this->data['subscription'] = Sub::find($sub);

        return view('modules.transaction.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return array|RedirectResponse
     */
    public function update(Request $request, $sub, $id)
    {

        try {
            $trans = Transaction::find($id);
            $trans->{'channel'} = $request->channel;
            $trans->receipt = $request->receipt;
            $trans->status = $request->status ?? 0;
            $trans->amount_paid = $request->amount;
            $result = $trans->save();
            if ($result) {
                $trans->subscription()->update(['activator_id' => Auth::user()->id, 'activator_reason' => $request->reason, 'status' => $request->status ?? 0]);

                return self::success('Transaction', 'Saved Successfully', route('subscription.transaction.index', [$sub, $id]));
            }

            return self::failed('Transaction', 'Failed to save', route('subscription.transaction.index', [$sub, $id]));
        } catch (\Exception $e) {
            return self::failed('Transaction', $e->getMessage(), route('subscription.transaction.index', [$sub, $id]));
        }

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

    public function recycle(Request $request, int $sub, int $transaction_id)
    {
        $trans = Transaction::find($transaction_id);
        if (! is_null($trans)) {
            $dpo = new DPO;
            $dpo->transaction_token = $trans->transaction_token;
            $dpo->company_token = $trans->payment_method->configuration['company_token'];
            $dpo->accountref = $trans->identifier;
            $statusResult = $dpo->verifyToken();

            $statusCode = simplexml_load_string($statusResult);
            // dd($trans);
            Log::error($statusResult);
            if ($statusCode->Result == '000') {
                // dd($this->currency_convert($statusCode->TransactionAmount, $statusCode->TransactionCurrency, $trans->currency));
                try {
                    $trans->amount_paid = $trans->amount;
                    $trans->status = 1;
                    $trans->receipt = (string) $statusCode->TransactionApproval ?? '';
                    $trans->initiator = $statusCode->CustomerName ?? '';
                    $trans->transaction_date = \Illuminate\Support\Carbon::parse($statusCode->TransactionSettlementDate)->toDateTimeString();
                    // $trans->response         = json_encode($statusCode);
                    $res = $trans->save();
                    if ($res) {

                        Log::info($trans);
                        $trans->subscription()->where('id', $trans->subscription_id)->update(['status' => 1]);
                        if ($trans->subscription->recurring == 1) {
                            $subtoken = new DPO;
                            $subtoken->company_token = $trans->payment_method->configuration['company_token'];
                            $subtoken->email = $trans->user->email;
                            $result = $subtoken->retrieveTokenSub();
                            $resultCode = simplexml_load_string($result);
                            $ata = [];
                            $ata['status'] = 1;

                            Log::error($result);

                            if ($resultCode->Result == '000') {
                                $ata['subscription_token'] = $resultCode->subscriptionToken;
                            }

                            if ($trans->subscription->reccurent_cycle > 0 && strtolower($trans->rate->name) != 'archive') {
                                $ata['subscription_date'] = Carbon::parse($trans->subscription->subscription_date)
                                    ->startOfDay();
                                $ata['expiry_date'] = Carbon::parse($trans->subscription->subscription_date)
                                    ->addDays($trans->subscription->rate->period)
                                    ->endOfDay();

                            }
                            $trans->subscription()->where('id', $trans->subscription_id)
                                ->update($ata);
                            Log::error($trans->subscription->refresh());

                            $trans->subscription->metadata()->insert(['start_date' => Carbon::now()->startOfDay(), 'next_renewal_date' => Carbon::now()->addDays($trans->subscription->rate->period + 1)->startOfDay(), 'expiry_date' => Carbon::now()->addDays($trans->rate->period)->endOfDay()]);
                        }
                        Session::flash('message', (string) $statusCode->ResultExplanation);
                        Session::flash('alert-class', 'alert-success');

                        return redirect(route('subscription.transaction.index', 0));
                    }

                } catch (\Exception $e) {

                    Session::flash('message', $e->getMessage());
                    Session::flash('alert-class', 'alert-danger');

                    return redirect(route('subscription.transaction.index', 0));
                }

            } else {
                Session::flash('message', (string) $statusCode->ResultExplanation);
                Session::flash('alert-class', 'alert-danger');

                return redirect(route('subscription.transaction.index', 0));
            }
        }
    }

    /**
     * @return JsonResponse
     */
    public function get(Request $request, int $sub)
    {

        $columns = ['id', 'receipt', 'payment_method_id', 'identifier', 'amount', 'amount_paid', 'name', 'email', 'transaction_date', 'status', 'action'];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $transaction = Transaction::query();
        $transaction->with(['product', 'user', 'rate', 'payment_method']);
        $transaction->when($sub != 0, function ($query) use ($sub) {

            return $query->where('subscription_id', $sub);
        });
        $totalData = $transaction->count();
        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $posts = $transaction->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {

            $search = $request->input('search.value');
            $p = $transaction->where('receipt', 'LIKE', "%{$search}%")
                ->orWhere('amount', 'LIKE', "%{$search}%")
                ->orWhere('amount_paid', 'LIKE', "%{$search}%")
                ->orWhereHas('payment_method', function ($ql) use ($search) {
                    return $ql->where('provider', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('subscription.product', function ($ql) use ($search) {
                    return $ql->where('product_name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('user', function ($ql) use ($search) {
                    return $ql->where('email', $search)
                        ->orWhere('name', $search);
                });

            $posts = $p->offset($start)
                ->limit($limit)
                ->orderBy('created_at', 'desc')
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = $p->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {

                $nestedData['pos'] = $pos;
                $nestedData['receipt'] = $post->receipt;
                $nestedData['channel'] = $post->channel; // optional($post->payment_method)->name;
                $nestedData['identifier'] = $post->identifier;
                $nestedData['amount'] = $post->currency.' '.number_format($post->amount).'/=';
                $nestedData['amount_paid'] = $post->currency.' '.number_format($post->amount_paid).'/=';
                $nestedData['name'] = optional($post->user)->name;
                $nestedData['email'] = optional($post->user)->email;
                $nestedData['time'] = Carbon::parse($post->transaction_date ?? $post->updated_at)
                    ->format('h:i:sa d-m-Y');
                $nestedData['status'] = self::payment_check($post->status);
                $nestedData['action'] = self::button_generate('subscription.transaction', [$sub, $post->id], ['recheck' => 'fas fa-recycle'], ['destroy']);
                $data[] = $nestedData;
                $pos++;
            }
        }

        $json_data = ['draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

        return response()->json($json_data);
    }
}
