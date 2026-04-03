<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRate;
use App\Http\Requests\UpdateRate;
use App\Models\Product;
use App\Models\Rate;
use App\Models\RateType;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RateController extends Controller
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
    public function index($productID)
    {

        $this->data['productid'] = $productID;

        return view('modules.rate.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
    public function create($productID)
    {

        $product = Product::query();
        // $product->whereNotNull('product_name');
        $product->when($productID != 0, function ($query) use ($productID) {

            return $query->where('id', $productID);
        });
        $this->data['product'] = $product->get();
        $this->data['productid'] = $productID;
        $this->data['rate_type'] = RateType::get();
        $this->data['categories'] = config('constants.rate_categories');

        return view('modules.rate.add', $this->data);
    }

    public function get(Request $request, int $productID)
    {
        $columns = ['id', 'name', 'swahili_name', 'period', 'cost', 'product_id', 'startdate', 'enddate', 'author', 'status', 'best_value', 'listorder'];
        $rate = Rate::query();
        $rate->with(['product', 'rate_type']);
        $rate->where('type', 'individual');
        $rate->when($productID !== 0, function ($query) use ($productID) {

            return $query->where('product_id', $productID);
        });
        $totalFiltered = $totalData = $rate->count();
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $rate->offset($start)
                ->limit($limit);
            if ($order == ('name' || 'period')) {
                $rate->with([
                    'rate_type' => function ($q) {},
                ])->orderBy('status', 'desc')
                    ->orderBy('listorder', 'asc');
            } else {
                $rate->orderBy('status', 'desc')
                    ->orderBy('listorder', 'asc');
            }

            $posts = $rate->get();
        } else {

            $search = $request->input('search.value');
            $rate = Rate::query();
            $rate->with(['product', 'rate_type']);
            $rate->where('type', 'individual');
            $rate->when($productID !== 0, function ($query) use ($productID) {

                return $query->where('product_id', $productID);
            });
            $rate->whereHas('rate_type', function ($q) use ($search) {

                return $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('period', 'LIKE', "%{$search}%");
            })
                ->orWhere('start_date', 'LIKE', "%{$search}%")
                ->orWhere('end_date', 'LIKE', "%{$search}%")
                ->orWhereHas('product', function ($ql) use ($search) {

                    return $ql->where('product_name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('user', function ($ql) use ($search) {

                    return $ql->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });

            $pst = $rate->offset($start)
                ->limit($limit);
            if ($order == ('name' || 'period')) {
                $pst->with([
                    'rate_type' => function ($q) {

                        $q->orderBy('status', 'desc')
                            ->orderBy('period', 'desc');
                    },
                ]);
            } else {
                $pst->orderBy('status', 'desc')
                    ->orderBy('listorder', 'asc');
            }
            $posts = $pst->get();

            $totalFiltered = $rate->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {
                $btn = self::button_generate('product.rate', [$productID, $post->id], [], ['destroy']);
                $nestedData['pos'] = $pos;
                $nestedData['product'] = optional($post->product)->product_name;
                $nestedData['period'] = $post->rate_type->period;
                $nestedData['editions'] = $post->editions;
                $nestedData['name'] = $post->rate_type->name;
                $nestedData['swahili_name'] = $post->rate_type->swahili_name;
                $nestedData['cost'] = $post->currency.' '.number_format($post->cost, 2).'/=';
                $nestedData['status'] = $this->check($post->status);
                $nestedData['author'] = $post->user->name;
                $nestedData['startdate'] = Carbon::parse($post->start_date)
                    ->format('Y-m-d H:i:s');
                $nestedData['enddate'] = Carbon::parse($post->end_date)
                    ->format('Y-m-d H:i:s');
                $nestedData['listorder'] = $post->listorder;
                $nestedData['best_value'] = ($post->best_value) ? 'yes' : 'no';
                $nestedData['compensation_days'] = $post->compensation_days;
                $nestedData['action'] = $btn;
                $data[] = $nestedData;
                $pos++;
            }
        }

        $json_data = ['draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

        return response()->json($json_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return array|RedirectResponse|Response
     */
    public function store(StoreRate $request, $productID)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $rt = RateType::find($request->rate_type_id);

                $rate = new Rate;
                $rate->name = $rt->name ?? 'undefined';
                $rate->swahili_name = $rt->swahili_name ?? 'undefined';
                $rate->period = $rt->period;
                $rate->editions = $request->editions;
                $rate->rate_type_id = $request->rate_type_id;
                $rate->product_id = $request->product_id;
                $rate->cost = $request->cost;
                $rate->strike_price = $request->slash_price;
                $rate->currency = $request->currency;
                $rate->region_id = $request->region ?? 118;
                $rate->status = $request->status ?? 1;
                $rate->description = $request->description;
                $rate->start_date = Carbon::now();
                $rate->free_rate_id = $request->free_product;
                $rate->free_rate_end_date = $request->free_product_lifetime;
                $rate->apple_product_id = $request->apple_product_id;
                $rate->user_id = Auth::user()->id;
                $rate->listorder = $request->listorder;
                $rate->best_value = $request->best_value ?? 0;
                $rate->compensation_days = $request->compensation_days;
                $rate->category = $request->category;
                $res = $rate->save();
                if ($res) {
                    return self::success('Rate', 'added successfuly', route('product.rate.index', $productID));
                }

                return self::failed('Rate', 'Failed to save', route('product.rate.index', $productID));
            } catch (Exception $e) {
                return self::failed('Rate', $e->getMessage(), route('product.rate.index', $productID));
            }
        }

        return self::failed('Rate', $validateddata, route('product.rate.index', $productID));
    }

    /**
     * Display the specified resource.
     *
     *
     * @return Response
     */
    public function show(Rate $rate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Rate  $rate
     * @return Application|Factory|\Illuminate\Contracts\View\View|View
     */
    public function edit($productID, $id)
    {

        $product = Product::query();
        $product->when($productID != 0, function ($query) use ($productID) {

            return $query->where('id', $productID);
        });
        $this->data['product'] = $product->get();
        $this->data['productid'] = $productID;
        $this->data['rate_type'] = RateType::get();
        $this->data['rate'] = Rate::find($id);
        $this->data['categories'] = config('constants.rate_categories');

        return view('modules.rate.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Rate  $rate
     * @return array|RedirectResponse
     */
    public function update(UpdateRate $request, $productID, $id)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $rt = RateType::find($request->rate_type_id);
                $rate = Rate::find($id);
                $rate->name = $rt->name ?? 'undefined';
                $rate->swahili_name = $rt->swahili_name ?? 'undefined';
                $rate->period = $rt->period;
                $rate->editions = $request->editions;
                $rate->rate_type_id = $request->rate_type_id;
                $rate->product_id = $request->product_id;
                $rate->cost = $request->cost;
                $rate->strike_price = $request->slash_price;
                $rate->currency = $request->currency;
                $rate->region_id = $request->region ?? 118;
                $rate->status = $request->status ?? 0;
                $rate->description = $request->description;
                $rate->free_rate_id = $request->free_product;
                $rate->free_rate_end_date = $request->free_product_lifetime;
                $rate->start_date = Carbon::now()
                    ->format('Y-m-d H:i:s');
                $rate->listorder = $request->listorder;
                $rate->best_value = $request->best_value ?? 0;
                /*$rate->end_date     = Carbon::now()
                                            ->addYears(30)
                                            ->format('Y-m-d H:i:s');*/
                $rate->apple_product_id = $request->apple_product_id;
                $rate->compensation_days = $request->compensation_days;
                $rate->category = $request->category;
                $res = $rate->save();
                if ($res) {
                    return self::success('Rate', 'added successfuly', route('product.rate.index', $productID));
                }

                return self::failed('Rate', 'Failed to save', route('product.rate.index', $productID));
            } catch (Exception $e) {
                return self::failed('Rate', $e->getMessage(), route('product.rate.index', $productID));
            }
        }

        return self::failed('Rate', $validateddata, route('product.rate.index', $productID));
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @return Response
     */
    public function destroy(Rate $rate)
    {
        //
    }

    public function getSelect2Data(Request $request)
    {
        // dd($request);

        $searchTerm = $request->input('term');
        $rates = Rate::with(['product'])
            ->where('name', 'like', '%'.$searchTerm.'%')
            ->orWhereHas('product', function ($query) use ($searchTerm) {
                return $query->where('product_name', 'like', '%'.$searchTerm.'%');
            })
            ->get();
        // dd($users->toArray());
        $data = [];
        $i = 0;
        foreach ($rates as $rate) {
            $data[$i]['id'] = $rate->id;
            $data[$i]['text'] = $rate->product->product_name.' - '.$rate->name;
            $i++;
        }

        return collect($data);
    }
}
