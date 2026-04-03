<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Traits\Meta;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FinanceApprovalController extends Controller
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

        return view('modules.subscription_approval.index', $this->data);
    }

    public function approve(Subscription $subscription) {}

    public function disapprove(Subscription $subscription) {}

    /**
     * @return JsonResponse
     */
    public function get(Request $request)
    {

        $columns = ['id', 'identifier', 'product_id', 'transactions', 'unit_cost', 'amount_paid', 'reccuring', 'subscription_date', 'expiry_date', 'user.name', 'user.email', 'status'];
        $subscription = Subscription::query();
        $subscription->with(['product', 'user', 'rate', 'transaction']);
        $totalData = $subscription->where('finance_approval_status', 3)->count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = $subscription->where('finance_approval_status', 3)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {

            $search = $request->input('search.value');
            $sub = $subscription->whereHas('user', function ($ql) use ($search) {

                return $ql->where('email', 'LIKE', "%{$search}%");
            });

            $posts = $sub->where('finance_approval_status', 3)
                ->offset($start)
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
                $nestedData['product'] = optional($post->product)->product_name;
                $nestedData['st'] = $post->rate->name;
                $nestedData['transactions'] = '<a href="'.route('subscription.transaction.index', $post->id).'">'.optional($post->transaction)->count() ?? 1 .'</a>';
                $nestedData['cost'] = $post->rate->currency.' '.number_format($post->rate->cost, 2);
                $nestedData['amount_paid'] = $post->rate->currency.' '.number_format($post->transaction->where('status', 1)->sum('amount_paid'), 2);
                $nestedData['recurrent'] = $post->reccuring == 0 ? 'No' : 'Yes';
                $nestedData['subdate'] = $post->subscription_date;
                $nestedData['expirydate'] = $post->expiry_date;
                $nestedData['name'] = optional($post->user)->name;
                $nestedData['email'] = optional($post->user)->email;
                $nestedData['status'] = $post->status ? 'Active' : 'Inactive';
                $nestedData['action'] = '<a href="'.route('subscription-approval.approve', $post->id).'">Approve Subscription</a><a href="'.route('subscription-approval.disapprove', $post->id).'">Disapprove Subscription</a>';
                $data[] = $nestedData;
                $pos++;

            }
        }

        $json_data = ['draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

        return response()->json($json_data);
    }
}
