<?php

    namespace App\Http\Controllers\B2b;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\B2bTransactionStoreRequest;
    use App\Http\Requests\B2bTransactionUpdateRequest;
    use App\Models\B2bTransaction;
    use Illuminate\Http\Request;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Auth;

    class TransactionController extends Controller
        {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
         */
            public function index($organizationId)
                {

                    $this->data['organizationId'] = $organizationId;

                    return view('modules.b2b.admin.transaction.index', $this->data);
                }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\View\View
         */
            public function create($organizationId)
                {

                    $this->data['organizationId'] = $organizationId;

                    return view('modules.b2b.admin.transaction.add', $this->data);
                }

        /**
         * Store a newly created resource in storage.
         *
         * @param \App\Http\Requests\B2bTransactionStoreRequest $request
         *
         * @return array
         */
            public function store(B2bTransactionStoreRequest $request)
                {

                    $validateddated = $request->validated();
                    if ($validateddated)
                        {
                            try
                                {
                                    $transaction              = new B2bTransaction();
                                    $transaction->status      = $request->status;
                                    $transaction->amount_paid = $request->amount;
                                    $transaction->receipt     = $request->receipt;
                                    $transaction->pay_channel = $request->channel;
                                    $res                      = $transaction->save();

                                    if ($res)
                                        {
                                            $transaction->subscription()->update([
                                                                                     'amount_paid'       => $transaction->amount_paid,
                                                                                     'receipt'           => $transaction->receipt,
                                                                                     'activator_id'      => Auth::user()->id,
                                                                                     'activatior_reason' => $request->reason,
                                                                                     'status'            => $transaction->status
                                                                                 ]);

                                            return self::success('Transaction', "Successful update", route('organization.transaction.index', $organizationId));
                                        }

                                    return self::failed('Transaction', "failed to update transaction", route('organization.transaction.index', $organizationId));
                                }
                            catch (\Exception $e)
                                {
                                    return self::failed('Transaction', $e->getMessage(), route('organization.transaction.index', $organizationId));
                                }
                        }
                    else
                        {
                            return self::failed('Transaction', $validateddated, route('organization.transaction.index', $organizationId));
                        }
                }

        /**
         * Display the specified resource.
         *
         * @param \App\Models\B2bTransaction $b2bTransaction
         *
         * @return \Illuminate\Http\Response
         */
            public function show(B2bTransaction $b2bTransaction)
                {
                    //
                }

        /**
         * Show the form for editing the specified resource.
         *
         * @param \App\Models\B2bTransaction $b2bTransaction
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\View\View
         */
            public function edit($organizationId, $transaction)
                {

                    $this->data['organizationId'] = $organizationId;
                    $this->data['transaction']    = B2bTransaction::find($transaction);

                    return view('modules.b2b.admin.transaction.edit', $this->data);
                }

        /**
         * Update the specified resource in storage.
         *
         * @param \App\Http\Requests\B2bTransactionUpdateRequest $request
         * @param                                                $organizationId
         * @param                                                $trans
         *
         * @return array
         */
            public function update(B2bTransactionUpdateRequest $request, $organizationId, $trans)
                {

                    $validateddated = $request->validated();
                    if ($validateddated)
                        {
                            try
                                {
                                    $transaction                   = B2bTransaction::find($trans);
                                    $transaction->status           = $request->status;
                                    $transaction->amount_paid      = $request->amount;
                                    $transaction->receipt          = $request->receipt;
                                    $transaction->pay_channel      = $request->channel;
                                    $transaction->activator_id     = Auth::user()->id;
                                    $transaction->activator_reason = $request->reason;
                                    $res                           = $transaction->save();

                                    if ($res)
                                        {
                                            $transaction->subscription()->update([
                                                                                     'amount_paid' => $transaction->amount_paid,
                                                                                     'receipt'     => $transaction->receipt,
                                                                                     'status'      => $transaction->status
                                                                                 ]);

                                            return self::success('Transaction', "Successful update", route('organization.transaction.index', $organizationId));
                                        }

                                    return self::failed('Transaction', "failed to update transaction", route('organization.transaction.index', $organizationId));
                                }
                            catch (\Exception $e)
                                {
                                    return self::failed('Transaction', $e->getMessage(), route('organization.transaction.index', $organizationId));
                                }
                        }
                    else
                        {
                            return self::failed('Transaction', $validateddated, route('organization.transaction.index', $organizationId));
                        }

                }

        /**
         * Remove the specified resource from storage.
         *
         * @param \App\Models\B2bTransaction $b2bTransaction
         *
         * @return \Illuminate\Http\Response
         */
            public function destroy(B2bTransaction $b2bTransaction)
                {
                    //
                }

            public function get(Request $request, $organizationId)
                {

                    $columns     = ['id', 'organization_id', 'product_id', 'receipt', 'b2b_subscription_id', 'amount_paid', 'date_paid', 'user_id'];
                    $transaction = B2bTransaction::query();

                    $transaction->with(['subscription.product', 'subscription.organization']);
                    $transaction->when($organizationId != 0, function ($query) use ($organizationId)
                        {

                            return $query->where('organization_id', $organizationId);
                        });


                    $totalFiltered = $totalData = $transaction->count();
                    $limit         = $request->input('length');
                    $start         = $request->input('start');
                    $order         = $columns[$request->input('order.0.column')];
                    $dir           = $request->input('order.0.dir');

                    if (empty($request->input('search.value')))
                        {
                            $transaction->offset($start)
                                        ->limit($limit);
                            $transaction->orderBy($order, $dir);

                            $posts = $transaction->get();
                        }
                    else
                        {

                            $search = $request->input('search.value');

                            $transaction->where('balance', 'LIKE', "%{$search}%")
                                        ->orWhere('status', '=', $this->search($search))
                                        ->orWhere('created_at', 'LIKE', "%{$search}%")
                                        ->orWhereHas('subscription.organization', function ($ql) use ($search)
                                            {

                                                return $ql->where('name', 'LIKE', "%{$search}%");
                                            })
                                        ->orWhereHas('subscription.rate', function ($ql) use ($search)
                                            {

                                                return $ql->where('cost', 'LIKE', "%{$search}%");
                                            })
                                        ->orWhereHas('user', function ($ql) use ($search)
                                            {

                                                return $ql->where('name', 'LIKE', "%{$search}%")
                                                          ->orWhere('email', 'LIKE', "%{$search}%");
                                            });

                            $p = $transaction->offset($start)
                                             ->limit($limit);
                            $p->orderBy($order, $dir);

                            $posts = $p->get();

                            $totalFiltered = $transaction->count();
                        }

                    $data = [];
                    if (!empty($posts))
                        {
                            $pos = $start + 1;
                            foreach ($posts as $post)
                                {
                                    $addition                   = [];
                                    $btn                        = self::button_generate('organization.transaction', [$organizationId, $post->id], $addition, ['show', 'destroy']);
                                    $nestedData['pos']          = $pos;
                                    $nestedData['organization'] = optional($post->subscription)->organization->name ?? '';
                                    $nestedData['product']      = optional($post->subscription)->product->product_name ?? '';
                                    $nestedData['receipt']      = $post->receipt;
                                    $nestedData['amount']       = number_format(optional($post->subscription)->amount);
                                    $nestedData['amount_paid']  = number_format($post->amount_paid);
                                    $nestedData['channel']      = $post->pay_channel;
                                    $nestedData['date_paid']    = Carbon::parse($post->date_paid)->toDayDateTimeString();
                                    $nestedData['agent']        = optional($post->activator)->name ?? '';
                                    $nestedData['action']       = $btn;
                                    $data[]                     = $nestedData;
                                    $pos++;
                                }
                        }

                    $json_data = ["draw" => (int)$request->input('draw'), "recordsTotal" => $totalData, "recordsFiltered" => $totalFiltered, "data" => $data];

                    return response()->json($json_data);
                }
        }
