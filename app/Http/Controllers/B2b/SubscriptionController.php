<?php

namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignCorporateRequest;
use App\Http\Requests\StoreCorporateSubscription;
use App\Http\Requests\UpdateCorporateSubscription;
use App\Imports\SubscriptionAssignImport;
use App\Mail\NewSubscriptionAlert;
use App\Models\B2bSubscription;
use App\Models\B2bSubscriptionUser;
use App\Models\B2bTransaction;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Rate;
use App\Models\RateType;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use App\Traits\Meta;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       SubscriptionController extends Controller
    {
        use Meta;

        public function __construct(protected array $data = [])
            {
                $this->data = self::site_def();
            }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
     */
        public function index($organizationId)
            {

                $this->data['organizationId'] = $organizationId;

                return view('modules.b2b.admin.subscription.index', $this->data);
            }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
        public function store(StoreCorporateSubscription $request, $organizationId)
            {

                $validateddata = $request->validated();
                if ($validateddata)
                    {
                        try
                            {
                                $rate = Rate::where('product_id', $request->product)
                                            ->where('rate_type_id', $request->ratetype)
                                            ->where('organization_id', $request->organization)
                                            ->first();
                                if (is_null($rate))
                                    {
                                        $rate = Rate::where('product_id', $request->product)
                                                    ->where('rate_type_id', $request->ratetype)
                                                    ->first();
                                    }
                                $subscription                    = new B2bSubscription();
                                $subscription->title             = $request->title;
                                $subscription->organization_id   = $request->organization;
                                $subscription->product_id        = $request->product;
                                $subscription->start_date        = Carbon::parse($request->startdate)->startOf('day');
                                $subscription->expiry_date       = Carbon::parse($request->startdate)->addDays($rate->period - 1)->endOf('day');
                                $subscription->accounts          = $request->users;
                                $subscription->status            = 1;
                                $subscription->rate_type_id      = $request->ratetype;
                                $subscription->amount            = ($rate->cost * $request->users);
                                $subscription->channel           = $request->channel;
                                $subscription->subscription_type = $rate->name;
                                $subscription->activator_reason  = $request->reason;
                                $subscription->activator_id      = Auth::user()->id;
                                $res                             = $subscription->save();
                                if ($res)
                                    {
                                        //attach_products($subscription);
                                        $transaction                      = new B2bTransaction();
                                        $transaction->identifier          = Str::ulid();
                                        $transaction->b2b_subscription_id = $subscription->id;
                                        $transaction->amount_paid         = $request->amount;
                                        $transaction->receipt             = $request->receipt;
                                        $transaction->pay_channel         = $request->channel;
                                        $transaction->date_paid           = Carbon::now()->toDateTimeString();
                                        $transaction->user_id             = Auth::user()->id;
                                        $transaction->save();
                                        try
                                            {
                                                $mail = new \stdClass();
                                                $mail->corporate = $subscription->organization->name;
                                                $mail->product = $subscription->product->product_name;
                                                $mail->startdate= Carbon::parse($request->startdate)->startOf('day');
                                                $mail->enddate = $subscription->expiry_date;
                                                $mail->activator = Auth::user()->email;
                                                $mail->users = $subscription->accounts;
                                                Mail::to('SubscriptionAlerts@ke.nationmedia.com')
                                                    //->cc('dennis.kiptugen@gmail.com')
                                                    ->send(new NewSubscriptionAlert($mail));

                                            }
                                        catch(\Exception $e)
                                            {
                                                Log::error($e->getMessage());
                                            }

                                        return self::success('Corporate Subscription', 'Added successfully', route('organization.subscription.index', $organizationId));
                                    }
                            }
                        catch (Exception $e)
                            {
                                return self::failed('Corporate Subscription', $e->getMessage(), route('organization.subscription.index', $organizationId));
                            }
                    }
                else
                    {
                        return self::failed('Corporate Subscription', $validateddata, route('organization.subscription.index', $organizationId));
                    }
            }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\B2bSubscription $b2bSubscription
     *
     * @return \Illuminate\Http\Response
     */
        public function show(B2bSubscription $b2bSubscription)
            {
                //
            }

    /**
     * Show the form for editing the specified resource.
     *
     * @param                          $organizationId
     * @param \Illuminate\Http\Request $Subscription
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
        public function edit($organizationId, $Subscription)
            {

                $this->data['organizationId'] = $organizationId;
                $this->data['organizations']  = Organization::get();
                $this->data['products']       = Product::get();
                $this->data['ratetypes']      = RateType::get();
                $this->data['subscription']   = B2bSubscription::find($Subscription);

                return view('modules.b2b.admin.subscription.edit', $this->data);
            }

        public function get(Request $request, $organizationId)
            {

                $columns      = ['id', 'organization_id', 'product_id', 'subscription_type', 'rate_id', 'paid', 'accounts', 'records', 'start_date', 'expiry_date', 'created_at', 'status','updated_at'];
                $subscription = B2bSubscription::query();
                $subscription->with(['product', 'organization', 'purchase.rate']);
                $totalData     = $subscription->count();
                $totalFiltered = $totalData;
                $limit         = $request->input('length');
                $start         = $request->input('start');
                $order         = $columns[$request->input('order.0.column')];
                $dir           = $request->input('order.0.dir');

                if($order =='paid')
                    $order = 'id';

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
                                               ->orWhereHas('organization', function ($ql) use ($search)
                                                   {
                                                       return $ql->where('name', 'LIKE', "%{$search}%");
                                                   })
                                               ->orWhere('start_date', 'LIKE', "%{$search}%")
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
                                $additional                      = ['assign' => 'fas fa-upload'];
                                $actionbtn                       = self::button_generate('organization.subscription', [$post->organization_id, $post->id], $additional);
                                $nestedData['pos']               = $pos;
                                $nestedData['product']           = optional($post->product)->product_name;
                                $nestedData['title']             = $post->title;
                                $nestedData['organization']      = optional($post->organization)->name;
                                $nestedData['subscription_type'] = $post->subscription_type;
                                $nestedData['cost']              = $post->purchase->rate->cost ?? 0;
                                $nestedData['paid']              = ($post->paid == 1) ? 'Yes' : 'No';
                                $nestedData['users']             = $post->accounts;
                                $nestedData['assigned']          = count($post->users->toArray());
                                $nestedData['subdate']           = $post->start_date;
                                $nestedData['expirydate']        = $post->expiry_date;
                                $nestedData['status']            = $this->check($post->status);
                                $nestedData['created_at']        = Carbon::parse($post->created_at)->toDateString();
                                $nestedData['updated_at']        = Carbon::parse($post->updated_at)->toDateString();
                                $nestedData['action']            = $actionbtn;
                                $data[]                          = $nestedData;
                                $pos++;

                            }
                    }

                $json_data = ['draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

                return response()->json($json_data);
            }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\B2bSubscription $b2bSubscription
     *
     * @return array
     */
        public function update(UpdateCorporateSubscription $request, $organizationId, $b2bSubscription)
            {

                $validateddata = $request->validated();
                if ($validateddata)
                    {
                        try
                            {
                                $rate = Rate::where('product_id', $request->product)
                                            ->where('rate_type_id', $request->ratetype)
                                            ->where('organization_id', $request->organization)
                                            ->first();
                                if (is_null($rate))
                                    {
                                        $rate = Rate::where('product_id', $request->product)
                                                    ->where('rate_type_id', $request->ratetype)
                                                    ->first();
                                    }
                                $subscription                    = B2bSubscription::find($b2bSubscription);
                                $subscription->title             = $request->title;
                                $subscription->organization_id   = $request->organization;
                                $subscription->product_id        = $request->product;
                                $subscription->start_date        = Carbon::parse($request->startdate)->startOf('day');
                                $subscription->expiry_date       = Carbon::parse($request->startdate)->addDays($rate->period - 1)->endOf('day');
                                $subscription->accounts          = $request->users;
                                $subscription->rate_type_id      = $request->ratetype;
                                $subscription->amount            = ($rate->cost * $request->users);
                                $subscription->channel           = $request->channel;
                                $subscription->status            = $request->status ?? 0;
                                $subscription->subscription_type = $rate->name;
                                $subscription->activator_reason  = $request->reason;
                                $subscription->activator_id      = Auth::user()->id;
                                $res                             = $subscription->save();
                                if ($res)
                                    {
                                        //attach_products($subscription);

                                        $transaction              = B2bTransaction::where('b2b_subscription_id', $subscription->id)
                                                                                  ->get()
                                                                                  ->last();
                                        if($transaction)
                                        {
                                            $transaction->amount_paid = $request->amount;
                                            $transaction->receipt     = $request->receipt;
                                            $transaction->pay_channel = $request->channel;
                                            $transaction->date_paid   = Carbon::now()->toDateTimeString();
                                            $transaction->user_id     = Auth::user()->id;
                                            $transaction->save();
                                        }
                                        else{
                                            $transaction                      = new B2bTransaction();
                                            $transaction->identifier          = Str::ulid();
                                            $transaction->b2b_subscription_id = $subscription->id;
                                            $transaction->amount_paid         = $request->amount;
                                            $transaction->receipt             = $request->receipt;
                                            $transaction->pay_channel         = $request->channel;
                                            $transaction->date_paid           = Carbon::now()->toDateTimeString();
                                            $transaction->user_id             = Auth::user()->id;
                                            $transaction->save();
                                        }

                                        return self::success('Corporate Subscription', 'Added successfully', route('organization.subscription.index', $organizationId));
                                    }
                            }
                        catch (Exception $e)
                            {
                                return self::failed('Corporate Subscription', $e->getMessage(), route('organization.subscription.index', $organizationId));
                            }
                    }
                else
                    {
                        return self::failed('Corporate Subscription', $validateddata, route('organization.subscription.index', $organizationId));
                    }
            }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\B2bSubscription $b2bSubscription
     *
     * @return array
     */
        public function destroy($organizationId, $id)
            {


                try
                    {
                        $subscription         = B2bSubscription::find($id);
                        $subscription->status = 0;
                        $res                  = $subscription->save();

                        if ($res)
                            {
                                /* $subscription->transaction()
                                              ->update(
                                                             ['status'=>0]
                                                     );*/
                                return self::success('Corporate Subscription', 'Subscription deactivated successfully', route('organization.subscription.index', $organizationId));
                            }
                        else
                            {
                                return self::failed('Corporate Subscription', 'failed ', route('organization.subscription.index', $organizationId));
                            }
                    }
                catch (Exception $e)
                    {
                        return self::failed('Corporate Subscription', $e->getMessage(), route('organization.subscription.index', $organizationId));
                    }


            }

        public function assign($organizationId, $id)
            {

                $this->data['subscription'] = B2bSubscription::find($id);
                $this->data['organization'] = Organization::find($organizationId);

                return view('modules.b2b.admin.subscription.assign', $this->data);
            }

        public function assign_upload_form($organizationId, $id)
            {
                $this->data['subscription'] = B2bSubscription::find($id);
                $this->data['organization'] = Organization::find($organizationId);

                return view('modules.b2b.admin.subscription.assign-upload', $this->data);
            }

        public function assign_upload($organizationId, $id, Request $request)
            {
                if (!$request->hasFile('files') || !is_array($request->file('files')))
                    {
                        return self::failed('Corporate Mass Assignment', 'no files were uploaded.', route('organization.subscription.assign', [$organizationId, $id]));
                    }

                $files = $request->file('files');
                foreach ($files as $file)
                    {
                        $extension = $file->getClientOriginalExtension();
                        if (!in_array($extension, ['xlsx', 'xls', 'csv']))
                            {
                                return self::failed('Corporate Mass Assignment', 'The file must be an Excel file.', route('organization.subscription.assign', [$organizationId, $id]));
                            }
                        try
                            {
//                                $path = $file->storeAs(
//                                    'uploads',
//                                    'corporate-mass-assignment-' . time() . '.' . $extension,
//                                    's3'
//                                );
                                //Excel::import(new SubscriptionAssignImport($id, Auth::user()->id), $path, 's3', \Maatwebsite\Excel\Excel::XLSX);
                                Excel::import(new SubscriptionAssignImport($id, Auth::user()->id), $file);
                                return self::success('Corporate Mass Assignment', 'import successful', route('organization.subscription.assign', [$organizationId, $id]));
                            }
                        catch (ValidationException $e)
                            {

                                return self::failed('Corporate Mass Assignment', $e->failures(), route('organization.subscription.assign', [$organizationId, $id]));

                            }
                        catch (Exception $e)
                            {
                                return self::failed('Corporate Mass Assignment', $e->getMessage(), route('organization.subscription.assign', [$organizationId, $id]));
                            }


                    }
            }

        public function assign_datatable(Request $request, $organizationId, $id)
            {

                $columns = ['id', 'name', 'email', 'created_at', 'status'];

                $totalData     = B2bSubscriptionUser::with(['user'])
                                                    ->where('b2b_subscription_id', $id)
                                                    ->count();
                $totalFiltered = $totalData;
                $limit         = $request->input('length');
                $start         = $request->input('start');
                $order         = $columns[$request->input('order.0.column')];
                $dir           = $request->input('order.0.dir');

                if (empty($request->input('search.value')))
                    {
                        $posts = B2bSubscriptionUser::with(['user'])
                                                    ->where('b2b_subscription_id', $id)
                                                    ->offset($start)
                                                    ->limit($limit)
                                                    ->orderBy($order, $dir)
                                                    ->get();
                    }
                else
                    {

                        $search = $request->input('search.value');
                        $posts  = B2bSubscriptionUser::with(['user'])
                                                     ->where('b2b_subscription_id', $id)
                                                     ->where(function ($query) use ($search)
                                                         {
                                                             $query->whereHas('user', function ($q) use ($search)
                                                                 {
                                                                     return $q->where('created_at', 'LIKE', "%{$search}%")
                                                                              ->orWhere('email', 'LIKE', "%{$search}%")
                                                                              ->orWhere('name', 'LIKE', "%{$search}%");
                                                                 });
                                                         })
                                                     ->offset($start)
                                                     ->limit($limit)
                                                     ->orderBy($order, $dir)
                                                     ->get();

                        $totalFiltered = B2bSubscriptionUser::with(['user'])
                                                            ->where('b2b_subscription_id', $id)
                                                            ->where(function ($query) use ($search)
                                                                {
                                                                    $query->whereHas('user', function ($q) use ($search)
                                                                        {
                                                                            return $q->where('created_at', 'LIKE', "%{$search}%")
                                                                                     ->orWhere('email', 'LIKE', "%{$search}%")
                                                                                     ->orWhere('name', 'LIKE', "%{$search}%");
                                                                        });
                                                                })
                                                            ->count();
                    }

                $data = [];
                if (!empty($posts))
                    {
                        $pos = $start + 1;
                        $i   = 0;
                        foreach ($posts as $post)
                            {
                                $actionbtn = '';
                                if ($post->user->status == 0)
                                    {
                                        $actionbtn .= '
                                                          <form id="activate-user-' . $post->user->id . '" action="' . route('user.activate', $post->user->id) . '" method="POST" class="create-form form">
                                                            <input type="hidden" name="_token" value="' . csrf_token() . '" />
                                                            <input type="hidden" name="_method" value="put" />
                                                            <input type="hidden" name="status" value="1">
                                                            <input type="hidden" name="location" value="' . route('organization.subscription.assign', [$organizationId, $id]) . '">
                                                            <button type="submit" class="btn btn-link text-dark "><span class="fas fa-plus-circle"></span> Activate</button>
                                                          </form>';
                                    }
                                else
                                    {
                                        $actionbtn .= '<form id="deactivate-user-' . $post->user->id . '" action="' . route('user.activate', $post->user->id) . '" method="POST" class="create-form form">
                                                            <input type="hidden" name="_token" value="' . csrf_token() . '" />
                                                            <input type="hidden" name="_method" value="put" />
                                                            <input type="hidden" name="status" value="0">
                                                            <input type="hidden" name="location" value="' . route('organization.subscription.assign', [$organizationId, $id]) . '">
                                                            <button type="submit" class="btn btn-link text-dark"><span class="fas fa-minus-circle"></span> Deactivate</button>
                                                          </form>';
                                    }
                                $sub = B2bSubscription::find($id);

                                if ($sub->records < $sub->accounts)
                                    {
                                        $usercheck = B2bSubscriptionUser::where('b2b_subscription_id', $id)
                                                                        ->where('user_id', $post->user->id)
                                                                        ->first();
                                        if (Carbon::parse($sub->expiry_date)->endOfDay()->gte(Carbon::now()))
                                            {
                                                if (is_null($usercheck))
                                                    {
                                                        $actionbtn .= '<form id="activate-' . $post->user->id . '" action="' . route('organization.subscription.assignment', [$organizationId, $id]) . '" method="POST" class="create-form form">
                                                                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                                                                        <input type="hidden" name="_method" value="put" />
                                                                        <input type="hidden" name="status" value="1">
                                                                        <input type="hidden" name="user" value="' . $post->user->id . '">
                                                                        <button type="submit" class="btn btn-link text-dark"><span class="fas fa-user-plus"></span> Assign</button>
                                                                    </form>';
                                                    }
                                                else
                                                    {

                                                        $actionbtn .= '<form id="deactivate-' . $post->user->id . '" action="' . route('organization.subscription.assignment', [$organizationId, $id]) . '" method="POST" class="create-form form">
                                                                     <input type="hidden" name="_token" value="' . csrf_token() . '" />
                                                                     <input type="hidden" name="_method" value="put" />
                                                                    <input type="hidden" name="status" value="0">
                                                                    <input type="hidden" name="user" value="' . $post->user->id . '">
                                                                    <button type="submit" class="btn btn-link text-dark">
                                                                        <span class="fas fa-user-minus"></span> Remove
                                                                    </button>
                                                                </form>';
                                                    }
                                            }
                                        else
                                            {
                                                $actionbtn .= '<button  class="btn btn-link text-dark">
                                                                        <span class="fas fa-times"></span> Expired
                                                                    </button>';
                                            }

                                    }
                                else
                                    {
                                        $usercheck = B2bSubscriptionUser::where('b2b_subscription_id', $id)
                                                                        ->where('user_id', $post->user->id)
                                                                        ->first();
                                        if (!is_null($usercheck))
                                            {
                                                if (Carbon::parse($sub->expiry_date)->endOfDay()->gte(Carbon::now()))
                                                    {
                                                        $actionbtn .= '<form id="deactivate-' . $post->user->id . '" action="' . route('organization.subscription.assignment', [$organizationId, $id]) . '" method="POST"  class="create-form form">
                                                                                 <input type="hidden" name="_token" value="' . csrf_token() . '" />
                                                                                     <input type="hidden" name="_method" value="put" />
                                                                                    <input type="hidden" name="status" value="0">
                                                                                    <input type="hidden" name="user" value="' . $post->user->id . '">
                                                                                    <button type="submit" class="btn btn-link text-dark">
                                                                                        <span class="fas fa-user-minus"></span> Remove
                                                                                    </button>
                                                                            </form>';
                                                    }
                                                else
                                                    {
                                                        $actionbtn .= '<button  class="btn btn-link text-dark">
                                                                        <span class="fas fa-times"></span> Expired
                                                                    </button>';
                                                    }

                                            }
                                        else
                                            {
                                                if (Carbon::parse($sub->expiry_date)->endOfDay()->gte(Carbon::now()))
                                                    {
                                                        $actionbtn .= '<button  class="btn btn-link text-dark">
                                                                                <span class="fas fa-times"></span> Full
                                                                            </button>';
                                                    }
                                                else
                                                    {
                                                        $actionbtn .= '<button  class="btn btn-link text-dark">
                                                                        <span class="fas fa-times"></span> Expired
                                                                    </button>';
                                                    }
                                            }

                                    }

                                $nestedData['pos']        = $pos;
                                $nestedData['name']       = $post->user->name;
                                $nestedData['email']      = $post->user->email;
                                $nestedData['status']     = $this->check($post->user->status);
                                $nestedData['registered'] = Carbon::parse($post->user->created_at)->toDayDateTimeString();
                                $nestedData['action']     = '<div class="d-flex justify-content-between flex-wrap w-100">' . $actionbtn . '</div>';
                                $data[]                   = $nestedData;
                                $pos++;

                            }
                    }

                $json_data = ['draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

                return response()->json($json_data);
            }

        public function assign_update(Request $request, int $organizationId, int $id)
            {

                //Log::error($request);
                try
                    {
                        if ($request->status == 0)
                            {

                                $sub = B2bSubscriptionUser::where('b2b_subscription_id', $id)
                                                          ->where('user_id', $request->user)
                                                          ->delete();
                                if ($sub)
                                    {
                                        $ss = B2bSubscription::find($id);
                                        $ss->decrement('records');
                                        $ss->save();

                                        return self::success('Corporate Assignment', 'User removed from subscription', route('organization.subscription.assign', [$organizationId, $id]));
                                    }


                                return self::failed('Corporate Assignment', 'User not removed from subscription', route('organization.subscription.assign', [$organizationId, $id]));
                            }
                        elseif ($request->status == 1)
                            {
                                $sub                      = new B2bSubscriptionUser();
                                $sub->b2b_subscription_id = $id;
                                $sub->user_id             = $request->user;
                                $sub->save();
                                if ($sub)
                                    {
                                        $ss = B2bSubscription::find($id);
                                        $ss->increment('records');
                                        $ss->save();

                                        return self::success('Corporate Assignment', 'User assigned to subscription', route('organization.subscription.assign', [$organizationId, $id]));
                                    }

                                return self::failed('Corporate Assignment', 'User not assigned to subscription', route('organization.subscription.assign', [$organizationId, $id]));
                            }
                        else
                            {
                                return self::failed('Corporate Assignment', 'Status is inavlid', route('organization.subscription.assign', [$organizationId, $id]));
                            }

                    }
                catch (Exception    $e)
                    {
                        return self::failed('Corporate Assignment', $e->getMessage(), route('organization.subscription.assign', [$organizationId, $id]));
                    }

            }

        public function assign_form($organizationId, $id)
            {
                $this->data['subscription'] = B2bSubscription::find($id);
                $this->data['organization'] = Organization::find($organizationId);
                return view('modules.b2b.admin.subscription.user_assign', $this->data);
            }

        public function assign_from_form(AssignCorporateRequest $request, $organizationId, $id)
            {
                $validateddata = $request->validated();

                if ($validateddata)
                    {
                        $sub = B2bSubscription::find($id);
                        if ($sub->records >= $sub->accounts)
                            {
                                return self::failed('Corporate Subscription Assignment', 'Maximum number of users exceeded', route('organization.subscription.index', $organizationId));
                            }

                        if (Carbon::parse($sub->expiry_date)->lt(Carbon::now()))
                            {
                                return self::failed('Corporate Subscription Assignment', 'Cannot Assign to expired subscription', route('organization.subscription.index', $organizationId));
                            }

                        $token = Str::ulid();
                        $user  = User::where('email', $request->email)
                                     ->first();
                        if (is_null($user))
                            {
                                $user = User::create([
                                                         'email'              => $request->email,
                                                         'name'               => $request->name,
                                                         'password'           => bcrypt($request->password),
                                                         'status'             => 1,
                                                         'remember_token'     => $token,
                                                         'verification_token' => Str::ulid(),
                                                         'type'               => 'organization',
                                                         'organization_id'    => $organizationId
                                                     ]);


                            }
                        else
                            {
                                $user->organization_id = $organizationId;
                                $user->status          = 1;
                                $user->save();
                            }
                        $check = B2bSubscriptionUser::where('user_id', $user->id)
                                                    ->where('b2b_subscription_id', $id)
                                                    ->first();
                        if (is_null($check))
                            {
                                $subscription                      = new B2bSubscriptionUser();
                                $subscription->user_id             = $user->id;
                                $subscription->b2b_subscription_id = $id;
                                $subscription->save();

                                $sub->increment('records');
                                $sub->save();
                                try
                                    {
                                        $user->notify(new NewSubscriptionNotification($user, $sub->product));
                                    }
                                catch (Exception $e)
                                    {
                                        Log::error($e->getMessage());
                                    }
                                return self::success('Corporate Subscription Assignment', 'User Assigned sucessfully', route('organization.subscription.index', $organizationId));
                            }
                        else
                            {
                                return self::failed('Corporate Subscription Assignment', 'User already exists on this subscription', route('organization.subscription.index', $organizationId));
                            }

                    }
                else
                    {
                        return self::failed('Corporate Subscription Assignment', $validateddata, route('organization.subscription.index', $organizationId));
                    }

            }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function create($organization)
            {

                $this->data['organizationId'] = $organization;
                $this->data['organizations']  = Organization::when($organization != 0, function ($query) use ($organization)
                    {

                        return $query->where('id', $organization);
                    })
                                                            ->get();
                $this->data['products']       = Product::whereStatus(1)
                                                       ->get();
                $this->data['ratetypes']      = RateType::get();

                return view('modules.b2b.admin.subscription.add', $this->data);
            }
    }
