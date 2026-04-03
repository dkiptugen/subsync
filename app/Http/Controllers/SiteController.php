<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeSiteRequest;
use App\Http\Requests\updateSiteRequest;
use App\Models\Product;
use App\Models\Region;
use App\Models\Site;
use App\Traits\Meta;
use Exception;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view('modules.site.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $this->data['regions'] = Region::get();

        return view('modules.site.add', $this->data);
    }

    public function get(Request $request)
    {
        $columns = [
            0 => 'id',
            1 => 'site_name',
            3 => 'site_url',
            4 => 'callback_url',
        ];
        $site = Site::query();
        $site->withCount('products');

        $totalFiltered = $totalData = $site->count();
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $site->offset($start)->limit($limit)->orderBy($order, $dir);

            $posts = $site->get();
        } else {

            $search = $request->input('search.value');

            $site->where('site_name', 'like', "%{$search}%")->orWhere('site_url', 'like', "%{$search}")->offset($start)->limit($limit)->orderBy($order, $dir);

            $posts = $site->get();

            $totalFiltered = $site->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {
                $btn = self::button_generate('site', $post->id, [], ['destroy']);
                $nestedData['pos'] = $pos;
                $nestedData['site_name'] = $post->site_name;
                $nestedData['products'] = $post->products_count;
                $nestedData['site_url'] = $post->site_url;
                $nestedData['region'] = $post->region->name;
                $nestedData['callback_url'] = $post->callback_url;
                $nestedData['action'] = $btn;
                $data[] = $nestedData;
                $pos++;
            }
        }

        $json_data = [
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];

        return response()->json($json_data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(storeSiteRequest $request)
    {
        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $site = new Site;
                $site->site_name = $request->site_name;
                $site->site_url = $request->site_url;
                $site->region_id = $request->region_id;
                $site->callback_url = $request->webhook_url;
                $res = $site->save();
                if ($res) {
                    return self::success('Site', 'saved successfully', route('site.index'));
                }

                return self::failed('Site', 'failed to save', route('site.index'));
            } catch (Exception $e) {
                return self::failed('Site', $e->getMessage(), route('site.index'));
            }

        } else {
            return self::failed('Site', $validateddata, route('site.index'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {

        $this->data['site'] = Product::find($id);

        return view('modules.site.view', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {

        $this->data['regions'] = Region::get();
        $this->data['site'] = Site::find($id);

        return view('modules.site.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(updateSiteRequest $request, int $id)
    {
        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $site = Site::find($id);
                $site->site_name = $request->site_name;
                $site->site_url = $request->site_url;
                $site->region_id = $request->region_id;
                $site->callback_url = $request->webhook_url;
                $res = $site->save();
                if ($res) {
                    return self::success('Site', 'saved successfully', route('site.index'));
                }

                return self::failed('Site', 'failed to save', route('site.index'));
            } catch (Exception $e) {
                return self::failed('Site', $e->getMessage(), route('site.index'));
            }

        } else {
            return self::failed('Site', $validateddata, route('site.index'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        //
    }
}
