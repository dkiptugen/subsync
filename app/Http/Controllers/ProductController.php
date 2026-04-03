<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProduct;
use App\Http\Requests\UpdateProduct;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Site;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
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

        return view('modules.product.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {

        $this->data['payment_methods'] = PaymentMethod::where('status', 1)->get();
        $this->data['sites'] = Site::get();
        $this->data['products'] = Product::where('status', 1)->get();

        return view('modules.product.add', $this->data);
    }

    /**
     * @return JsonResponse
     */
    public function get(Request $request)
    {

        $columns = [
            'id', 'product_name', 'payment_prefix', 'type', 'payment_methods', 'product_link', 'user_id', 'created_at',
        ];
        $totalData = Product::count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = @$columns[$request->input('order.0.column')] ?? 'created_at';
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = Product::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {

            $search = $request->input('search.value');
            $posts = Product::where('product_name', 'like', "%{$search}%")
                ->orWhere('identifier', 'LIKE', "%{$search}%")
                ->orWhere('payment_methods', 'LIKE', "%{$search}%")
                ->orWhere('product_link', 'LIKE', "%{$search}%")
                ->orWhere('type', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($query) use ($search) {

                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Product::where('product_name', 'like', "%{$search}%")
                ->orWhere('identifier', 'LIKE', "%{$search}%")
                ->orWhere('payment_methods', 'LIKE', "%{$search}%")
                ->orWhere('product_link', 'LIKE', "%{$search}%")
                ->orWhere('type', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($query) use ($search) {

                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {
                $btn = self::button_generate('product', $post->id);
                $nestedData['pos'] = $pos;
                $nestedData['name'] = $post->product_name;
                $nestedData['prefix'] = $post->identifier;
                $nestedData['type'] = $post->type;

                $nestedData['payment_method'] = $post->payment_methods->pluck('name');
                $nestedData['productlink'] = $post->product_link;
                $nestedData['author'] = $post->user->name;
                $nestedData['status'] = $this->check($post->status);
                $nestedData['date_created'] = Carbon::parse($post->created_at)
                    ->format('d-M-Y');
                $nestedData['premium'] = ((bool) $post->is_premium) ? 'Yes' : 'No';
                $nestedData['bundle'] = ((bool) $post->is_bundled) ? 'Yes' : 'No';
                $nestedData['archive_days'] = $post->archive_days;
                $nestedData['site'] = $post->site->site_name;
                $nestedData['rates'] = '<a href="'.route('product.rate.index', $post->id).'">'.$post->rates->count().'</a>';
                $nestedData['action'] = $btn;
                $data[] = $nestedData;
                $pos++;

            }
        }

        $json_data = [
            'draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data,
        ];

        return response()->json($json_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return array|RedirectResponse|Response
     */
    public function store(StoreProduct $request)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $product = new Product;
                $product->product_name = $request->product_name;
                $product->identifier = $request->payment_prefix;
                $product->payment_methods = $request->payment_methods;
                $product->product_link = $request->product_link;
                $product->payment_notification_link = $request->notification_link;
                $product->type = $request->type;
                $product->user_id = Auth::user()->id;
                $product->site_id = $request->site;
                $product->status = 1;
                $product->is_premium = $request->premium ?? 0;
                $product->is_bundled = $request->bundle ?? 0;
                $product->description = $request->description;
                $product->archive_days = $request->archive_days;
                $product->archive_skip_days = $request->archive_skip_days;
                $product->counterpart_id = $request->counterpart_id;
                $res = $product->save();
                $product->sites()->attach($request->sites);
                $product->children()->attach($request->children);

                if ($res) {
                    return self::success('Products', 'added successfully', route('product.index'));
                }

                return self::failed('Products', 'Failed!!', route('product.index'));
            } catch (Exception $e) {
                return self::failed('Products', $e->getMessage(), route('product.index'));
            }
        }

        return self::failed('Products', $validateddata, route('product.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function show($id)
    {

        return view('modules.product.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function edit($id)
    {

        $this->data['payment_methods'] = PaymentMethod::where('status', 1)->get();
        $this->data['product'] = Product::with(['sites', 'children'])->find($id);
        $this->data['sites'] = Site::get();
        $this->data['products'] = Product::where('status', 1)->get();

        return view('modules.product.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return array|RedirectResponse|Response
     */
    public function update(UpdateProduct $request, $id)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            try {
                $product = Product::find($id);
                $product->product_name = $request->product_name;
                $product->identifier = $request->payment_prefix;
                $product->payment_methods = $request->payment_methods;
                $product->product_link = $request->product_link;
                $product->payment_notification_link = $request->notification_link;
                $product->{'type'} = $request->type;
                $product->site_id = $request->site;
                $product->is_premium = $request->premium ?? 0;
                $product->is_bundled = $request->bundle ?? 0;
                $product->status = $request->status ?? 0;
                $product->description = $request->description;
                $product->archive_days = $request->archive_days;
                $product->archive_skip_days = $request->archive_skip_days;
                $product->counterpart_id = $request->counterpart_id;
                $res = $product->save();
                $product->sites()->sync($request->sites);
                $product->children()->sync($request->children);
                if ($res) {
                    return self::success('Products', 'updated successfully', route('product.index'));
                }

                return self::failed('Products', 'Failed!!', route('product.index'));
            } catch (Exception $e) {
                return self::failed('Products', $e->getMessage(), route('product.index'));
            }
        }

        return self::failed('Products', $validateddata->message, route('product.index'));
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
}
