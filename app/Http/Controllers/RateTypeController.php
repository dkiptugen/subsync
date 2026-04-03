<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRateType;
use App\Http\Requests\UpdateRateType;
use App\Models\RateType;
use App\Traits\Meta;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RateTypeController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|View
     */
    public function index()
    {

        return view('modules.rate_type.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|View
     */
    public function create()
    {

        return view('modules.rate_type.add', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return array
     */
    public function store(StoreRateType $request)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $rate_type = new RateType;
                $rate_type->name = $request->name;
                $rate_type->swahili_name = $request->swahili_name;
                $rate_type->period = $request->period;
                $rate_type->dow = $request->days_of_week;
                $rate_type->status = 1;
                $res = $rate_type->save();
                if ($res) {
                    return self::success('Subscription Type', 'added successfully', route('rate_type.index'));
                } else {
                    return self::failed('Subscription Type', 'failed to create', route('rate_type.index'));
                }
            } catch (\Exception $e) {
                return self::failed('Subscription Type', $e->getMessage(), route('rate_type.index'));
            }
        }

        return self::failed('Subscription Type', $validateddata, route('rate_type.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|\Illuminate\Contracts\View\View|View
     */
    public function edit($id)
    {

        $this->data['ratetype'] = RateType::find($id);

        return view('modules.rate_type.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return array
     */
    public function update(UpdateRateType $request, $id)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $rate_type = RateType::find($id);
                $rate_type->name = $request->name;
                $rate_type->swahili_name = $request->swahili_name;
                $rate_type->period = $request->period;
                $rate_type->dow = $request->days_of_week;
                $rate_type->status = $request->status ?? 0;
                $res = $rate_type->save();
                if ($res) {
                    $rate_type->rate()->update(['status' => $request->status ?? 0,
                        'name' => $request->name,
                        'swahili_name' => $request->swahili_name,
                        'period' => $request->period]);

                    return self::success('Subscription Type', 'added successfully', route('rate_type.index'));
                } else {
                    return self::failed('Subscription Type', 'failed to create', route('rate_type.index'));
                }
            } catch (\Exception $e) {
                return self::failed('Subscription Type', $e->getMessage(), route('rate_type.index'));
            }
        }

        return self::failed('Subscription Type', $validateddata, route('rate_type.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return array
     */
    public function destroy($id)
    {

        try {
            $subscription = RateType::find($id);
            $subscription->status = 0;
            $res = $subscription->save();

            if ($res) {
                $subscription->rate()->update(['status' => 0]);

                return self::success('Subscription Type', 'Subscription type deactivated successfully', route('rate_type.index'));
            } else {
                return self::failed('Subscription Type', 'failed to update', route('rate_type.index'));
            }
        } catch (\Exception $e) {
            return self::failed('Subscription Type', $e->getMessage(), route('rate_type.index'));
        }

    }

    public function get(Request $request)
    {

        $columns = ['id', 'name', 'period', 'created_at'];
        $totalData = RateType::count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = RateType::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {

            $search = $request->input('search.value');
            $posts = RateType::where('name', 'LIKE', "%{$search}%")
                ->orWhere('period', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = RateType::where('name', 'LIKE', "%{$search}%")
                ->orWhere('period', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {
                $btn = self::button_generate('rate_type', $post->id);
                $nestedData['pos'] = $pos;
                $nestedData['name'] = $post->name;
                $nestedData['swahili_name'] = $post->swahili_name;
                $nestedData['period'] = $post->period;
                $nestedData['dow'] = $post->dow;
                $nestedData['status'] = $this->check($post->status);
                $nestedData['date_created'] = Carbon::parse($post->created_at)
                    ->format('d-M-Y');
                $nestedData['action'] = $btn;
                $data[] = $nestedData;
                $pos++;
            }
        }

        $json_data = ['draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

        return response()->json($json_data);
    }
}
