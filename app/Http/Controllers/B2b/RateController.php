<?php

namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRates;
use App\Http\Requests\UpdateOrganizationRates;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Rate;
use App\Models\RateType;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RateController extends Controller
    {
    /**
     * Display a listing of the resource.
     *
     * @param $organizationId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
     */
        public function index($organizationId)
            {
                $this->data['organizationId'] = $organizationId;
                return view('modules.b2b.admin.rate.index', $this->data);
            }

    /**
     * Show the form for creating a new resource.
     *
     * @param $organizationId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function create($organizationId)
            {
                $this->data['organizationId'] = $organizationId;
                $organization                 = Organization::query();
                $organization->when($organizationId != 0, function ($q) use ($organizationId)
                    {
                        return $q->where('id', $organizationId);
                    });
                $this->data['organization'] = $organization->get();
                $this->data['product']      = Product::where('status', '<>', 0)
                                                     ->get();
                $this->data['rate_type']    = RateType::get();
                return view('modules.b2b.admin.rate.add', $this->data);
            }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
        public function get(Request $request, int $organizationId)
            {
                $columns = array('id', 'name', 'period', 'cost', 'product_id', 'startdate', 'enddate', 'author', 'status');
                $rate    = Rate::query();

                $rate->with(['product']);
                $rate->where('type', 'corporate');
                /*$rate->when($productID !== 0,function($query)use($productID){
                    return $query->where('product_id',$productID);
                });*/
                $rate->when($organizationId != 0, function ($query) use ($organizationId)
                    {
                        return $query->where('organization_id', $organizationId);
                    });
                $totalFiltered = $totalData = $rate->count();
                $limit         = $request->input('length');
                $start         = $request->input('start');
                $order         = $columns[$request->input('order.0.column')];
                $dir           = $request->input('order.0.dir');

                if (empty($request->input('search.value')))
                    {
                        $rate->offset($start)
                             ->limit($limit);
                        if ($order == ('name' || 'period'))
                            {
                                $rate->with(['rate_type' => function ($q) use ($order, $dir)
                                    {
                                        $q->orderBy($order, $dir);
                                    }]);
                            }
                        else
                            {
                                $rate->orderBy($order, $dir);
                            }
                        $posts = $rate->get();
                    }
                else
                    {

                        $search = $request->input('search.value');
                        $rate   = Rate::query();
                        $rate->with(['product']);
                        $rate->where('type', 'corporate');
                        /*$rate->when($productID !== 0,function($query)use($productID){
                            return $query->where('product_id',$productID);
                        });*/
                        $rate->when($organizationId != 0, function ($query) use ($organizationId)
                            {
                                return $query->where('organization_id', $organizationId);
                            }, function ($query) use ($search)
                            {
                                return $query->wherehas('organization', function (Builder $query) use ($search)
                                    {
                                        $query->where('name', 'LIKE', "%{$search}%");
                                    });
                            });
                        $rate->where('name', 'LIKE', "%{$search}%")
                             ->orWhere('period', 'LIKE', "%{$search}%")
                             ->orWhere('start_date', 'LIKE', "%{$search}%")
                             ->orWhere('end_date', 'LIKE', "%{$search}%")
                             ->orWhereHas('product', function ($ql) use ($search)
                                 {
                                     return $ql->where('product_name', 'LIKE', "%{$search}%");
                                 })
                             ->orWhereHas('user', function ($ql) use ($search)
                                 {
                                     return $ql->where('name', 'LIKE', "%{$search}%")
                                               ->orWhere('email', 'LIKE', "%{$search}%");
                                 });

                        $p = $rate->offset($start)
                                  ->limit($limit);
                        if ($order == ('name' || 'period'))
                            {
                                $p->with(['rate_type' => function ($q) use ($order, $dir)
                                    {
                                        $q->orderBy($order, $dir);
                                    }]);
                            }
                        else
                            {
                                $p->orderBy($order, $dir);
                            }
                        $posts = $p->get();

                        $totalFiltered = $rate->count();
                    }

                $data = array();
                if (!empty($posts))
                    {
                        $pos = $start + 1;
                        foreach ($posts as $post)
                            {
                                $btn                        = self::button_generate('organization.rate', [$organizationId, $post->id]);
                                $nestedData['pos']          = $pos;
                                $nestedData['product']      = optional($post->product)->product_name;
                                $nestedData['organization'] = $post->organization->name;
                                $nestedData['period']       = $post->rate_type->period;
                                $nestedData['name']         = $post->rate_type->name;
                                $nestedData['cost']         = $post->cost;
                                $nestedData['status']       = $this->check($post->status);
                                $nestedData['author']       = $post->user->name;
                                $nestedData['startdate']    = Carbon::parse($post->start_date)->format('Y-m-d H:i:s');
                                $nestedData['enddate']      = Carbon::parse($post->end_date)->format('Y-m-d H:i:s');
                                $nestedData['action']       = $btn;
                                $data[]                     = $nestedData;
                                $pos++;
                            }
                    }

                $json_data = array('draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data);

                return response()->json($json_data);
            }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreOrganizationRates $request
     * @param $organizationId
     * @return array
     */
        public function store(StoreOrganizationRates $request, int $organizationId)
            {
                $validateddata = $request->validated();
                if ($validateddata)
                    {
                        try
                            {
                                $rt                    = RateType::find($request->rate_type_id);
                                $rate                  = new Rate();
                                $rate->name            = $rt->name;
                                $rate->period          = $rt->period;
                                $rate->rate_type_id    = $rt->id;
                                $rate->product_id      = $request->product_id;
                                $rate->cost            = $request->cost;
                                $rate->status          = 1;
                                $rate->organization_id = $request->organization_id;
                                $rate->type            = 'corporate';
                                $rate->currency        = $request->currency;
                                $rate->region_id       = 118;
                                $rate->description     = $request->description;
                                $rate->start_date      = Carbon::now();
                                //$rate->end_date       =   Carbon::now()->addYears(20);
                                $rate->user_id = Auth::user()->id;
                                $res           = $rate->save();
                                if ($res)
                                    {
                                        return self::success('Rate', 'added successfuly', route('organization.rate.index', $organizationId));
                                    }
                                return self::fail('Rate', 'Failed to save', route('organization.rate.index', $organizationId));
                            }
                        catch (Exception $e)
                            {
                                return self::fail('Rate', $e->getMessage(), route('organization.rate.index', $organizationId));
                            }
                    }
                return self::fail('Rate', $validateddata, route('organization.rate.index', $organizationId));
            }

    /**
     * Display the specified resource.
     *
     * @param $organizationId
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function show($organizationId, $id)
            {
                $this->data['organizationId'] = $organizationId;
                $this->data['rate']           = Rate::find($id);
                return view('modules.b2b.admin.rate.view', $this->data);
            }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $organizationId
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function edit(int $organizationId, int $id): \Illuminate\Contracts\View\View|Factory|View|Application
            {
                $this->data['organizationId'] = $organizationId;
                $this->data['rate']           = Rate::find($id);
                $organization                 = Organization::query();
                $organization->when($organizationId != 0, function ($q) use ($organizationId)
                    {
                        return $q->where('id', $organizationId);
                    });
                $this->data['organization'] = $organization->get();
                $this->data['product']      = Product::where('status', '<>', 0)
                                                     ->get();
                $this->data['rate_type']    = RateType::get();
                return view('modules.b2b.admin.rate.edit', $this->data);
            }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Rate $Rate
     * @return array
     */
        public function update(UpdateOrganizationRates $request, int $organizationId, int $id)
            {
                $validateddata = $request->validated();
                if ($validateddata)
                    {
                        try
                            {
                                $rt                    = RateType::find($request->rate_type_id);
                                $rate                  = Rate::find($id);
                                $rate->name            = $rt->name;
                                $rate->period          = $rt->period;
                                $rate->rate_type_id    = $rt->id;
                                $rate->product_id      = $request->product_id;
                                $rate->cost            = $request->cost;
                                $rate->status          = $request->status ?? 0;
                                $rate->organization_id = $request->organization_id;
                                $rate->type            = 'corporate';
                                $rate->currency        = $request->currency;
                                $rate->region_id       = 118;
                                $rate->description     = $request->description;
                                $rate->start_date      = Carbon::now();
                                $rate->user_id         = Auth::user()->id;
                                $res                   = $rate->save();
                                if ($res)
                                    {
                                        return self::success('Rate', 'added successfuly', route('organization.rate.index', $organizationId));
                                    }
                                return self::fail('Rate', 'Failed to save', route('organization.rate.index', $organizationId));
                            }
                        catch (Exception $e)
                            {
                                return self::fail('Rate', $e->getMessage(), route('organization.rate.index', $organizationId));
                            }
                    }
                return self::fail('Rate', $validateddata, route('organization.rate.index', $organizationId));
            }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Rate $Rate
     * @return \Illuminate\Http\Response
     */
        public function destroy($organizationId, $id)
            {
                //
            }
    }
