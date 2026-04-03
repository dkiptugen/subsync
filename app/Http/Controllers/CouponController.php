<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoupon;
use App\Http\Requests\UpdateCoupon;
use App\Models\Agent;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\RateType;
use App\Models\Region;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CouponController extends Controller
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

        return view('modules.coupons.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
    public function create()
    {

        $this->data['products'] = Product::whereStatus(1)
            ->get();
        $this->data['regions'] = Region::whereIn('code', explode(',', config('custom.CUSTOMER.COVERED_REGIONS')))
            ->get();
        $this->data['rate_types'] = RateType::get();

        return view('modules.coupons.add', $this->data);
    }

    /**
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        // dd($this->discount_r('percentage'));
        $columns = ['id', 'code', 'type', 'products', 'rate_type', 'discount', 'status', 'startdate', 'enddate', 'usage'];
        $totalData = Coupon::count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = Coupon::with(['agent', 'rateTypes'])->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {

            $search = $request->input('search.value');
            $discount = $this->discount_r($search);
            $posts = Coupon::with(['agent', 'rateTypes'])
                ->where(function ($query) use ($search, $discount) {
                    $query->where('code', 'LIKE', "%{$search}%")
                        ->orWhere('type', 'LIKE', "%{ $discount }%")
                        ->orWhere('discount', 'LIKE', "%{$search}%")
                        ->orWhere('agent_email', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Coupon::where('code', 'LIKE', "%{$search}%")
                ->where(function ($query) use ($search, $discount) {
                    $query->where('code', 'LIKE', "%{$search}%")
                        ->orWhere('type', 'LIKE', "%{ $discount }%")
                        ->orWhere('discount', 'LIKE', "%{$search}%")
                        ->orWhere('agent_email', 'LIKE', "%{$search}%");
                })
                ->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {
                $btn = self::button_generate('coupon', $post->id, [], ['destroy']);
                $nestedData['pos'] = $pos;
                $nestedData['product'] = $post->products->pluck('product_name');
                $nestedData['type'] = $this->discount($post->type);
                $nestedData['code'] = $post->code;
                $nestedData['discount'] = number_format($post->discount, 0);
                $nestedData['status'] = $this->check($post->status);
                $nestedData['usage'] = $post->usage;
                $nestedData['agent'] = ! is_null($post->agent) ? $post->agent->name.' '.$post->agent->email.'' : '';
                $nestedData['region'] = $post->region->name;
                $nestedData['rate_type'] = collect($post->rateTypes->pluck('name')->toArray())->push($post->rateType->name);
                $nestedData['startdate'] = Carbon::parse($post->start_date)
                    ->toDateTimeString();
                $nestedData['expirydate'] = Carbon::parse($post->expiry_date)
                    ->toDateTimeString();
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
     * @return array|RedirectResponse
     */
    public function store(StoreCoupon $request)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $agent = null;
                if ($request->has('agent_email') && ! is_null($request->agent_email)) {
                    $agent = Agent::where('email', $request->agent_email)->limit(1)->first();
                    if (! $agent) {
                        throw new Exception('agent email not registered under agents');
                    }
                }
                $coupon = new Coupon;
                $coupon->code = $request->code;
                $coupon->type = $request->type;
                $coupon->products = array_map('intval', $request->products);
                $coupon->rate_type = $request->rate_type;
                $coupon->status = 1;
                $coupon->discount = $request->discount;
                $coupon->region_id = $request->region;
                $coupon->start_date = $request->start_date;
                $coupon->expiry_date = $request->expiry_date;
                $coupon->agent_id = @$agent->id;
                $coupon->agent_email = @$agent->email;
                $coupon->expires = $request->expires ?? 0;
                $coupon->multi_use = $request->multi_use ?? 0;
                $res = $coupon->save();
                $coupon->rateTypes()->attach($request->ratetypes);
                if ($res) {
                    return self::success('Coupon', 'added successfully', route('coupon.index'));
                }

                return self::failed('coupon', 'Failed to add record', route('coupon.index'));
            } catch (Exception $e) {
                return self::failed('coupon', $e->getMessage(), route('coupon.index'));
            }
        }

        return self::failed('coupon', $validateddata, route('coupon.index'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Coupon  $coupon
     * @return Application|Factory|\Illuminate\Contracts\View\View|View
     */
    public function edit($id)
    {

        $this->data['products'] = Product::whereStatus(1)
            ->get();
        $this->data['promo'] = Coupon::find($id);
        $this->data['regions'] = Region::whereIn('code', explode(',', config('custom.CUSTOMER.COVERED_REGIONS')))
            ->get();
        $this->data['rate_types'] = RateType::get();

        return view('modules.coupons.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Coupon  $coupon
     * @return array|RedirectResponse
     */
    public function update(UpdateCoupon $request, $id)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $agent = null;
                if ($request->has('agent_email') && ! is_null($request->agent_email)) {
                    $agent = Agent::where('email', $request->agent_email)->limit(1)->first();
                    if (! $agent) {
                        throw new Exception('agent email not registered under agents');
                    }
                }
                $coupon = Coupon::find($id);
                $coupon->load(['rateTypes']);
                $coupon->code = $request->code;
                $coupon->type = $request->type;
                $coupon->products = array_map('intval', $request->products);
                $coupon->discount = $request->discount;
                $coupon->rate_type = $request->rate_type;
                $coupon->status = 1;
                $coupon->region_id = $request->region;
                $coupon->start_date = $request->start_date;
                $coupon->expiry_date = $request->expiry_date;
                $coupon->agent_id = @$agent->id;
                $coupon->agent_email = @$agent->email;
                $coupon->expires = $request->expires ?? 0;
                $coupon->multi_use = $request->multi_use ?? 0;
                $res = $coupon->save();
                $coupon->rateTypes()->sync($request->ratetypes);
                if ($res) {
                    return self::success('Coupon', 'added successfully', route('coupon.index'));
                }

                return self::failed('coupon', 'Failed to add record', route('coupon.index'));
            } catch (Exception $e) {
                return self::failed('coupon', $e->getMessage(), route('coupon.index'));
            }
        }

        return self::failed('coupon', $validateddata, route('coupon.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @return Response
     */
    public function destroy(Coupon $coupon)
    {
        //
    }
}
