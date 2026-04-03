<?php

namespace App\Http\Controllers;

use App\Http\Datatables\PaymentMethodDatatable;
use App\Http\Requests\StorePaymentMethod;
use App\Http\Requests\UpdatePaymentMethod;
use App\Models\PaymentMethod;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentMethodController extends Controller
    {
        use Meta;

        public function __construct(protected array $data = [])
            {
                $this->data = self::site_def();
            }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
        public function index()
            {

                return view('modules.payment_methods.index', $this->data);
            }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
        public function create()
            {

                return view('modules.payment_methods.add', $this->data);
            }

    /**
     * Store a newly created resource in storage.
     *
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
                                $method                         = new PaymentMethod;
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

                                return self::failed('Payment Method', 'failed to create', route('payment_method.index'));
                            }
                        catch (Exception $e)
                            {
                                return self::failed('Payment Method', $e->getMessage(), route('payment_method.index'));
                            }

                    }

                return self::failed('Payment Method', $validateddata, route('payment_method.index'));
            }

    /**
     * Display the specified resource.
     *
     *
     * @return Response
     */
        public function show(PaymentMethod $mode)
            {
                //
            }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
        public function edit(PaymentMethod $mode, $id)
            {

                $this->data['payment_method'] = $mode->find($id);

                // dd($mode->find($id)->configuration);
                return view('modules.payment_methods.edit', $this->data);
            }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
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
                                // $method->identifier             = Str::ulid();
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

                                return self::failed('Payment Method', 'failed to create', route('payment_method.index'));
                            }
                        catch (Exception $e)
                            {
                                return self::failed('Payment Method', $e->getMessage(), route('payment_method.index'));
                            }

                    }

                return self::failed('Payment Method', $validateddata, route('payment_method.index'));
            }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @return Response
     */
        public function destroy(PaymentMethod $mode)
            {

            }

        public function get(Request $request, PaymentMethodDatatable $datatable)
            {
                $datatable->columns = ['id', 'name', 'period', 'cost', 'product_id', 'startdate', 'enddate', 'author', 'status'];
                return response()->json($datatable->data($request));
            }
    }
