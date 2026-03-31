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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FinanceApprovalController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
        public function index()
            {

                return view('modules.subscription_approval.index', $this->data);
            }
        public function approve(Subscription $subscription)
            {

            }
        public function disapprove(Subscription $subscription)
            {

            }
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
        public function get(Request $request)
            {

                $columns      = ['id', 'identifier', 'product_id', 'transactions', 'unit_cost', 'amount_paid', 'reccuring', 'subscription_date', 'expiry_date', 'user.name', 'user.email', 'status'];
                $subscription = Subscription::query();
                $subscription->with(['product', 'user', 'rate', 'transaction']);
                $totalData     = $subscription->where('finance_approval_status',3)->count();
                $totalFiltered = $totalData;
                $limit         = $request->input('length');
                $start         = $request->input('start');
                $order         = $columns[$request->input('order.0.column')];
                $dir           = $request->input('order.0.dir');

                if (empty($request->input('search.value')))
                    {
                        $posts = $subscription->where('finance_approval_status',3)
                                              ->offset($start)
                                              ->limit($limit)
                                              ->orderBy($order, $dir)
                                              ->get();
                    }
                else
                    {

                        $search = $request->input('search.value');
                        $sub    = $subscription->whereHas('user', function ($ql) use ($search)
                            {

                                return $ql->where('email', 'LIKE', "%{$search}%");
                            });

                        $posts = $sub->where('finance_approval_status',3)
                                     ->offset($start)
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
                                $additional = [];
                                //$actionbtn = self::button_generate('subscription' ,$post->id ,$additional);
                                $nestedData['pos']          = $pos;
                                $nestedData['identifier']   = $post->identifier;
                                $nestedData['product']      = optional($post->product)->product_name;
                                $nestedData['st']           = $post->rate->name;
                                $nestedData['transactions'] = '<a href="' . route('subscription.transaction.index', $post->id) . '">' . optional($post->transaction)->count()??1 . '</a>';
                                $nestedData['cost']         = $post->rate->currency . ' ' . number_format($post->rate->cost, 2);
                                $nestedData['amount_paid']  = $post->rate->currency . ' ' . number_format($post->transaction->where('status', 1)->sum('amount_paid'), 2);
                                $nestedData['recurrent']    = $post->reccuring == 0 ? 'No' : 'Yes';
                                $nestedData['subdate']      = $post->subscription_date;
                                $nestedData['expirydate']   = $post->expiry_date;
                                $nestedData['name']         = optional($post->user)->name;
                                $nestedData['email']        = optional($post->user)->email;
                                $nestedData['status']       = $post->status ? 'Active' : 'Inactive';
                                $nestedData['action']       = '<a href="' . route('subscription-approval.approve', $post->id) . '">Approve Subscription</a><a href="' . route('subscription-approval.disapprove', $post->id) . '">Disapprove Subscription</a>';
                                $data[] = $nestedData;
                                $pos++;

                            }
                    }

                $json_data = ['draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

                return response()->json($json_data);
            }

    }


