<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\B2bSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
    {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
     */
        public function index()
            {
                return view('modules.b2b.client.subscription.index', $this->data);
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
     *
     * @return \Illuminate\Http\Response
     */
        public function store(Request $request)
            {
                //
            }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
        public function show($id)
            {
                //
            }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
        public function edit($id)
            {
                //
            }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
        public function update(Request $request, $id)
            {
                //
            }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
        public function destroy($id)
            {
                //
            }

        public function get(Request $request)
            {
                $columns      = ['id', 'product_id', 'start_date', 'expiry_date', 'accounts', 'records', 'rate_id', 'paid', 'status'];
                $subscription = B2bSubscription::query();
                $subscription->with(['product', 'purchase']);
                $subscription->where('organization_id', Auth::user()->organization_id);
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
                                               ->orWhereHas('purchase', function ($ql) use ($search)
                                                   {
                                                       return $ql->where('is_paid', $this->search($search));
                                                   })
                                               ->orWhere('start_date', 'LIKE', "%{$search}%")
                                               ->orWhere('expiry_date', 'LIKE', "%{$search}%");
                        $posts  = $sub->offset($start)
                                      ->limit($limit)
                                      ->orderBy($order, $dir)
                                      ->get();

                        $totalFiltered = $sub->count();
                    }

                $data = array();
                if (!empty($posts))
                    {
                        $pos = $start + 1;
                        $i   = 0;
                        foreach ($posts as $post)
                            {
                                $additional               = [];
                                $actionbtn                = self::button_generate('client_subscription', $post->id, $additional);
                                $nestedData['pos']        = $pos;
                                $nestedData['product']    = optional($post->product)->product_name;
                                $nestedData['paid']       = ($post->paid == 1) ? 'Paid' : 'Due';
                                $nestedData['users']      = $post->accounts;
                                $nestedData['assigned']   = count($post->users->toArray());
                                $nestedData['subdate']    = $post->start_date;
                                $nestedData['expirydate'] = $post->expiry_date;
                                $nestedData['status']     = $this->check($post->status);
                                $nestedData['action']     = $actionbtn;
                                $data[]                   = $nestedData;
                                $pos++;

                            }
                    }

                $json_data = array('draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data);

                return response()->json($json_data);
            }
    }
