<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Traits\Meta;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LeadsController extends Controller
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
        return view('modules.leads.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {
        return view('modules.leads.add', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function show($id)
    {
        return view('modules.leads.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function edit($id)
    {
        return view('modules.leads.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
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

    /**
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        $columns = ['id', 'product_id', 'link', 'clicks', 'amount', 'type', 'created_at'];
        $totalData = Lead::count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = Lead::with(['product'])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {

            $search = $request->input('search.value');
            $posts = Lead::with(['product'])
                ->where('link', 'ILIKE', "%{$search}%")
                ->orWhereHas('product', function ($ql) use ($search) {
                    return $ql->where('status', 'LIKE', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Lead::with(['product'])
                ->where('link', 'ILIKE', "%{$search}%")
                ->orWhereHas('product', function ($ql) use ($search) {
                    return $ql->where('status', 'LIKE', "%{$search}%");
                })
                ->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {

                $nestedData['pos'] = $pos;
                $nestedData['product'] = optional($post->product)->product_name;
                $nestedData['title'] = $post->title;
                $nestedData['link'] = urldecode($post->link);
                $nestedData['clicks'] = $post->clicks;
                $nestedData['amount'] = 'Kes '.number_format($post->amount).'/=';
                $nestedData['package'] = $post->type;
                $nestedData['date'] = Carbon::parse($post->created_at)
                    ->format('d-m-Y');
                $data[] = $nestedData;
                $pos++;
            }
        }

        $json_data = ['draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

        return response()->json($json_data);
    }
}
