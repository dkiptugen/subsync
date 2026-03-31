<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentMethod;
use App\Http\Requests\UpdatePaymentMethod;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
    {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
     */
        public function index()
            {

                return view('modules.payment_methods.index', $this->data);
            }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
     */
        public function create()
            {

                return view('modules.payment_methods.add', $this->data);
            }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePaymentMethod $request
     *
     * @return array
     */
        public function store(StorePaymentMethod $request)
            {

                $validateddata = $request->validated();
                if ($validateddata)
                    {
                        try
                            {
                                $method                         = new PaymentMethod();
                                $method->name                   = $request->name;
                                $method->identifier             = Str::ulid();
                                $method->provider               = $request->provider;
                                $method->type                   = $request->type;
                                $method->configuration          = $request->configuration;
                                $method->status                 = 1;
                                $method->notifying              = $request->notify;
                                $method->notification_endpoints = explode(',', $request->notification_endpoint);
                                $method->user_id                = Auth::user()->id;
                                $res                            = $method->save();
                                if ($res)
                                    {

                                        return self::success('Payment Method', 'Added successfully', route('payment_method.index'));
                                    }

                                return self::fail('Payment Method', 'failed to create', route('payment_method.index'));
                            }
                        catch (Exception $e)
                            {
                                return self::fail('Payment Method', $e->getMessage(), route('payment_method.index'));
                            }

                    }

                return self::fail('Payment Method', $validateddata, route('payment_method.index'));
            }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\PaymentMethod $mode
     *
     * @return \Illuminate\Http\Response
     */
        public function show(PaymentMethod $mode)
            {
                //
            }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\PaymentMethod $mode
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|Response|\Illuminate\View\View
     */
        public function edit(PaymentMethod $mode, $id)
            {

                $this->data['payment_method'] = $mode->find($id);

                //dd($mode->find($id)->configuration);
                return view('modules.payment_methods.edit', $this->data);
            }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\PaymentMethod $mode
     *
     * @return array
     */
        public function update(UpdatePaymentMethod $request, PaymentMethod $mode, $id)
            {

                $validateddata = $request->validated();
                if ($validateddata)
                    {
                        try
                            {
                                $method       = $mode->find($id);
                                $method->name = $request->name;
                                //$method->identifier             = Str::ulid();
                                $method->provider               = $request->provider;
                                $method->type                   = $request->type;
                                $method->configuration          = $request->configuration;
                                $method->status                 = 1;
                                $method->notifying              = $request->notify;
                                $method->notification_endpoints = explode(',', $request->notification_endpoint);
                                $method->user_id                = Auth::user()->id;
                                $res                            = $method->save();
                                if ($res)
                                    {

                                        return self::success('Payment Method', 'Added successfully', route('payment_method.index'));
                                    }

                                return self::fail('Payment Method', 'failed to create', route('payment_method.index'));
                            }
                        catch (Exception $e)
                            {
                                return self::fail('Payment Method', $e->getMessage(), route('payment_method.index'));
                            }

                    }

                return self::fail('Payment Method', $validateddata, route('payment_method.index'));
            }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\PaymentMethod $mode
     *
     * @return \Illuminate\Http\Response
     */
        public function destroy(PaymentMethod $mode)
            {
                //
            }

        public function get(Request $request)
            {

                $columns   = ['id', 'name', 'period', 'cost', 'product_id', 'startdate', 'enddate', 'author', 'status'];
                $totalData = PaymentMethod::count();

                $totalFiltered = $totalData;
                $limit         = $request->input('length');
                $start         = $request->input('start');
                $order         = $columns[$request->input('order.0.column')];
                $dir           = $request->input('order.0.dir');

                if (empty($request->input('search.value')))
                    {
                        $posts = PaymentMethod::with(['user'])
                                              ->offset($start)
                                              ->limit($limit)
                                              ->orderBy($order, $dir)
                                              ->get();
                    }
                else
                    {

                        $search = $request->input('search.value');
                        $posts  = PaymentMethod::with(['user'])
                                               ->where('name', 'LIKE', "%{$search}%")
                                               ->orWhere('identifier', 'LIKE', "%{$search}%")
                                               ->orWhere('provider', 'LIKE', "%{$search}%")
                                               ->orWhere('type', 'LIKE', "%{$search}%")
                                               ->orWhereHas('user', function ($ql) use ($search)
                                                   {

                                                       return $ql->where('name', 'LIKE', "%{$search}%")
                                                                 ->orWhere('email', 'LIKE', "%{$search}%");
                                                   })
                                               ->offset($start)
                                               ->limit($limit)
                                               ->orderBy($order, $dir)
                                               ->get();

                        $totalFiltered = PaymentMethod::with(['user'])
                                                      ->where('name', 'LIKE', "%{$search}%")
                                                      ->orWhere('identifier', 'LIKE', "%{$search}%")
                                                      ->orWhere('provider', 'LIKE', "%{$search}%")
                                                      ->orWhere('type', 'LIKE', "%{$search}%")
                                                      ->orWhereHas('user', function ($ql) use ($search)
                                                          {

                                                              return $ql->where('name', 'LIKE', "%{$search}%")
                                                                        ->orWhere('email', 'LIKE', "%{$search}%");
                                                          })
                                                      ->count();
                    }

                $data = [];
                if (!empty($posts))
                    {
                        $pos = $start + 1;
                        foreach ($posts as $post)
                            {
                                $btn = self::button_generate('payment_method', $post->id, [], ['destroy', 'show']);

                                $nestedData['pos']        = $pos;
                                $nestedData['provider']   = $post->provider;
                                $nestedData['identifier'] = $post->identifier;
                                $nestedData['status']     = $this->check($post->status);
                                $nestedData['notify']     = ($post->notifying == 1)
                                    ? '<div class="custom-control custom-switch">
										<input type="checkbox" class="custom-control-input" id="customSwitch' . $post->id . '"  checked disabled>
                                        <label class="custom-control-label" for="customSwitch' . $post->id . '"></label>
									</div>'
                                    : '<div class="custom-control custom-switch">
										<input type="checkbox" class="custom-control-input shortcode-notify" id="customSwitch' . $post->id . '" data-shortcode="' . $post->identifier . '">
                                        <label class="custom-control-label" for="customSwitch' . $post->id . '" data-shortcode="' . $post->identifier . '">Activate</label>
									</div>';

                                $nestedData['creator']      = $post->user->name;
                                $nestedData['date_created'] = Carbon::parse($post->created_at)->toIso8601String();
                                $nestedData['action']       = $btn;
                                $data[]                     = $nestedData;
                                $pos++;
                            }
                    }

                $json_data = ['draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

                return response()->json($json_data);
            }
    }
