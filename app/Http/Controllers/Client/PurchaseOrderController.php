<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePORequest;
use App\Http\Requests\EditPORequest;
use App\Models\B2bPurchase;
use App\Models\Product;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PurchaseOrderController extends Controller
    {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function index()
            {
                return view('modules.b2b.client.purchase.index', $this->data);
            }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function create()
            {
                $this->data['products'] =   Product::where('status',1)
                                                    ->get();
                $this->data['rates']    =   Rate::where('organization_id',Auth::user()->organization_id)

                                                ->get();
                if($this->data['rates']->count() < 1)
                    {
                        $this->data['rates'] = Rate::whereHas('organization',function($query){
                            $query->where('name','Default');
                        })
                            ->get();
                    }
                return view('modules.b2b.client.purchase.add', $this->data);
            }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
        public function store(CreatePORequest $request)
            {
                $validateddata = $request->validated();
                if($validateddata)
                    {
                        try
                            {

                            }
                        catch (\Exception $e)
                            {
                                return self::fail('Purchase order',$e->getMessage(),route('client_purchase_order.index'));
                            }
                    }
                return self::fail('Purchase order',$validateddata,route('client_purchase_order.index'));
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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
        public function edit($id)
            {
                $this->data['po'] = B2bPurchase::find($id);
                if($this->data['po'] ==1)
                    {
                        Session::flash('access','Cannot change this Po since it has already been approved');
                        return redirect(route('client_purchase_order.index'));
                    }
                return view('modules.b2b.client.purchase.edit', $this->data);
            }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
        public function update(EditPORequest $request, $id)
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
    }
