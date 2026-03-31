<?php

namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovePO;
use App\Models\B2bPurchase;
use App\Models\B2bSubscription;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
    {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
     */
        public function index($organizationId)
            {
                $this->data['organizationId'] = $organizationId;
                return view('modules.b2b.admin.purchase.index', $this->data);
            }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
        public function create()
            {
                //
            }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
        public function store(Request $request)
            {
                //
            }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\B2bPurchase $b2bPurchase
     * @return \Illuminate\Http\Response
     */
        public function invoice($organizationId, $id)
            {
                //
            }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\B2bPurchase $b2bPurchase
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function edit($organizationId, $id)
            {
                $this->data['organizationId'] = $organizationId;
                $this->data['po']             = B2bPurchase::find($id);
                return view('modules.b2b.admin.purchase.approve', $this->data);
            }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\B2bPurchase $b2bPurchase
     *
     * @return array
     */
        public function update(ApprovePO $request, $organizationId, $id)
            {
                ///dd($request->all());
                $validateddata = $request->validated();
                if ($validateddata)
                    {
                        try
                            {
                                $po              = B2bPurchase::find($id);
                                $po->startdate   = $request->startdate;
                                $po->reason      = $request->reason;
                                $po->status      = $request->status;
                                $po->accounts    = $request->accounts;
                                $po->approver_id = Auth::user()->id;
                                $res             = $po->save();
                                if ($res)
                                    {
                                        if ((int)$po->status == 1)
                                            {
                                                $sub                  = new B2bSubscription();
                                                $sub->organization_id = $po->organization_id;
                                                $sub->b2b_purchase_id = $po->id;
                                                $sub->product_id      = $po->rate->product_id;
                                                $sub->expiry_date     = Carbon::parse($po->startdate)->addDays($po->rate->period)->endOfDay();
                                                $sub->start_date      = Carbon::parse($po->startdate)->startOfDay();
                                                $sub->accounts        = $po->accounts;
                                                $sub->status          = 1;
                                                $res1                 = $sub->save();
                                                if ($res1)
                                                    {
                                                        return self::success('PO Approval', 'Subscription generated successfully', route('organization.purchase.index', [$organizationId]));
                                                    }
                                                return self::fail('PO Approval', 'PO approved but failed to create subscription', route('organization.purchase.index', [$organizationId]));
                                            }
                                        return self::success('PO Approval', 'PO rejected successfully', route('organization.purchase.index', [$organizationId]));

                                    }
                                return self::fail('PO Approval', 'Failed th make changes', route('organization.purchase.index', [$organizationId]));
                            }
                        catch (Exception $e)
                            {
                                Log::error($e->getMessage());
                            }
                    }
                else
                    {
                        return self::fail('PO Approval', $validateddata, route('organization.purchase.index', [$organizationId, $id]));
                    }
            }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\B2bPurchase $b2bPurchase
     * @return \Illuminate\Http\Response
     */
        public function destroy(B2bPurchase $b2bPurchase)
            {
                //
            }

    /**
     * @param Request $request
     * @param         $organizationId
     *
     * @return JsonResponse
     */
        public function get(Request $request, $organizationId)
            {
                $columns  = ['id', 'id', 'organization_id', 'full_amount', 'balance', 'created_at', 'user_id', 'approver_id', 'status'];
                $purchase = B2bPurchase::query();

                $purchase->with(['details', 'cc_approver', 'finance_approver', 'organization']);
                $purchase->when($organizationId != 0, function ($query) use ($organizationId)
                    {
                        return $query->where('organization_id', $organizationId);
                    });


                $totalFiltered = $totalData = $purchase->count();
                $limit         = $request->input('length');
                $start         = $request->input('start');
                $order         = $columns[$request->input('order.0.column')];
                $dir           = $request->input('order.0.dir');

                if (empty($request->input('search.value')))
                    {
                        $purchase->offset($start)
                                 ->limit($limit);
                        $purchase->orderBy($order, $dir);

                        $posts = $purchase->get();
                    }
                else
                    {

                        $search = $request->input('search.value');

                        $purchase->where('balance', 'LIKE', "%{$search}%")
                                 ->orWhere('status', '=', $this->search($search))
                                 ->orWhere('created_at', 'LIKE', "%{$search}%")
                                 ->orWhereHas('organization', function ($ql) use ($search)
                                     {
                                         return $ql->where('name', 'LIKE', "%{$search}%");
                                     })
                                 ->orWhereHas('user', function ($ql) use ($search)
                                     {
                                         return $ql->where('name', 'LIKE', "%{$search}%")
                                                   ->orWhere('email', 'LIKE', "%{$search}%");
                                     })
                                 ->orWhereHas('cc_approver', function ($ql) use ($search)
                                     {
                                         return $ql->where('name', 'LIKE', "%{$search}%")
                                                   ->orWhere('email', 'LIKE', "%{$search}%");
                                     })
                                 ->orWhereHas('finance_approver', function ($ql) use ($search)
                                     {
                                         return $ql->where('name', 'LIKE', "%{$search}%")
                                                   ->orWhere('email', 'LIKE', "%{$search}%");
                                     });

                        $p = $purchase->offset($start)
                                      ->limit($limit);
                        $p->orderBy($order, $dir);

                        $posts = $p->get();

                        $totalFiltered = $purchase->count();
                    }

                $data = array();
                if (!empty($posts))
                    {
                        $pos = $start + 1;
                        foreach ($posts as $post)
                            {
                                $addition = [];
                                if ($post->status == 0)
                                    {
                                        $addition['edit'] = 'fas fa-check';
                                    }
                                if ($post->is_paid == 0)
                                    {
                                        $addition['invoice'] = 'fas fa-envelope';
                                    }
                                $btn                            = self::button_generate('organization.purchase', [$organizationId, $post->id], $addition, ['destroy', 'edit']);
                                $nestedData['pos']              = $pos;
                                $nestedData['p_order']          = str_pad($post->id, 8, 0, STR_PAD_LEFT);
                                $nestedData['organization']     = optional($post->organization)->name??'';
                                $nestedData['accounts']         = $post->accounts;
                                $nestedData['products']         = '<a href="">' . count($post->details->toArray()) . '</a>';
                                $nestedData['amount']           = $post->full_amount;
                                $nestedData['balance']          = $post->balance;
                                $nestedData['intiator']         = optional($post->user)->name;
                                $nestedData['cc_approver']      = optional($post->cc_approver)->name ?? '';
                                $nestedData['finance_approver'] = optional($post->finance_approver)->name ?? '';
                                $nestedData['created_at']       = Carbon::parse($post->created_at)->format('Y-m-d H:i:s');
                                $nestedData['status']           = $this->check($post->status);
                                $nestedData['action']           = $btn;
                                $data[]                         = $nestedData;
                                $pos++;
                            }
                    }

                $json_data = array('draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data);

                return response()->json($json_data);
            }
    }
