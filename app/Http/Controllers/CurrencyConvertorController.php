<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrencyRate;
use App\Http\Requests\UpdateCurrencyRate;
use App\Models\CurrencyConvertor;
use App\Models\Region;
use App\Traits\Meta;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CurrencyConvertorController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Application|View
     */
    public function index()
    {
        return view('modules.currency.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Application|View
     */
    public function create()
    {
        $this->data['regions'] = Region::whereIn('code', explode(',', config('custom.CUSTOMER.COVERED_REGIONS')))->get();

        return view('modules.currency.add', $this->data);
    }

    public function get(Request $request)
    {
        $columns = ['id', 'region_id', 'currency', 'amount', 'dollar_amount', 'startdate', 'enddate', 'user_id', 'status'];
        $totalData = CurrencyConvertor::count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $query = CurrencyConvertor::query()->with(['region:id,name', 'user:id,name']);

        if (empty($request->input('search.value'))) {
            $posts = $query->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $query->where(function ($builder) use ($search) {
                $builder->where('currency', 'LIKE', "%{$search}%")
                    ->orWhere('amount', 'LIKE', "%{$search}%")
                    ->orWhere('dollar_amount', 'LIKE', "%{$search}%")
                    ->orWhere('startdate', 'LIKE', "%{$search}%")
                    ->orWhere('enddate', 'LIKE', "%{$search}%")
                    ->orWhereHas('region', function ($regionQuery) use ($search) {
                        $regionQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });

            $totalFiltered = (clone $query)->count();
            $posts = $query->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {
                $btn = self::button_generate('currency', $post->id, [], ['destroy']);
                $nestedData['pos'] = $pos;
                $nestedData['region'] = $post->region->name;
                $nestedData['currency'] = $post->currency;
                $nestedData['currency_amount'] = $post->amount;
                $nestedData['dollar_amount'] = $post->dollar_amount;
                $nestedData['status'] = $this->check($post->status);
                $nestedData['startdate'] = $post->startdate;
                $nestedData['enddate'] = $post->enddate;
                $nestedData['author'] = $post->user->name;
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
     * @return array|Response
     */
    public function store(StoreCurrencyRate $request)
    {
        try {
            $validateddata = $request->validated();
            if ($validateddata) {
                $region = Region::find($request->region);

                if ($region) {
                    $cc = CurrencyConvertor::where('enddate', '>=', $request->startdate)
                        ->where('currency', $region->currency_code)
                        ->where('status', 1)
                        ->first();
                    if (! is_null($cc)) {
                        $cc->update(['enddate' => Carbon::parse($request->startdate)->subDays(1), 'status' => 0]);
                    }
                    $cr = new CurrencyConvertor;
                    $cr->currency = $region->currency_code;
                    $cr->dollar_amount = $request->dollar_amount;
                    $cr->amount = $request->currency_amount;
                    $cr->region_id = $region->id;
                    $cr->startdate = $request->startdate;
                    $cr->enddate = $request->enddate;
                    $cr->status = 1;
                    $cr->user_id = $request->user()->id;
                    $result = $cr->save();
                    if ($result) {
                        return self::success('currency rate', 'Saved successfully', route('currency.index'));
                    }

                    return self::failed('currency rate', 'failed to save', route('currency.index'));
                } else {
                    return self::failed('currency rate', 'region not found', route('currency.index'));
                }

            } else {
                return self::failed('currency rate', $validateddata, route('currency.index'));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::failed('currency rate', $e->getMessage(), route('currency.index'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return array|Response
     */
    public function update(UpdateCurrencyRate $request, $id)
    {
        try {
            $validateddata = $request->validated();
            if ($validateddata) {
                $region = Region::find($request->region);

                if ($region) {
                    $cr = CurrencyConvertor::find($id);
                    $cr->currency = $region->currency_code;
                    $cr->dollar_amount = $request->dollar_amount;
                    $cr->amount = $request->currency_amount;
                    $cr->region_id = $region->id;
                    $cr->startdate = $request->startdate;
                    $cr->enddate = $request->enddate;
                    $cr->user_id = $request->user()->id;
                    $cr->status = $request->status ?? 0;
                    $result = $cr->save();
                    if ($result) {
                        return self::success('currency rate', 'Saved successfully', route('currency.index'));
                    }

                    return self::failed('currency rate', 'failed to save', route('currency.index'));
                } else {
                    return self::failed('currency rate', 'region not found', route('currency.index'));
                }
            } else {
                return self::failed('currency rate', $validateddata, route('currency.index'));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::failed('currency rate', $e->getMessage(), route('currency.index'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Application|View
     */
    public function show($id)
    {
        $this->data['currency'] = CurrencyConvertor::find($id);

        return view('modules.currency.view', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Application|View
     */
    public function edit($id)
    {
        $this->data['currency'] = CurrencyConvertor::find($id);
        $this->data['regions'] = Region::whereIn('code', explode(',', config('custom.CUSTOMER.COVERED_REGIONS')))->get();

        return view('modules.currency.edit', $this->data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function autocomplete($id)
    {
        $region = Region::find($id);

        return response()->json($region);
    }
}
