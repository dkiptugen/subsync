<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePassword;
use App\Http\Requests\StoreSubscriber;
use App\Http\Requests\UpdateSubscriber;
use App\Jobs\ImportSubscribers;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewUserNotification;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SubscriberController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
    public function index($productId = 0)
    {

        return view('modules.subscriber.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
    public function create($product_id = 0)
    {

        return view('modules.subscriber.add', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return array|RedirectResponse|Response
     */
    public function store(StoreSubscriber $request, $product)
    {

        $validator = $request->validated();
        if ($validator) {
            $user = User::updateOrCreate(['email' => $request->email], [
                'name' => $request->firstname,
                'surname' => $request->surname,
                'password' => bcrypt($request->password),
                'status' => $request->status ?? 1,
                'can_notify' => $request->notify ?? 0,
                'daily_notifications' => $request->daily_notifications ?? 0,
            ]);

            $res = $user;
            if ($res) {
                try {
                    $user->notify(new NewUserNotification($user, $request->password));
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }

                return self::success('subscriber', 'changed successfully', route('product.subscriber.index', $product));
            }
        } else {
            return self::failed('Subscriber Update', ' failed', route('product.subscriber.index', $product));
        }
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
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Application|View
     */
    public function edit($product_id, $id)
    {

        $this->data['user'] = User::find($id);

        return view('modules.subscriber.edit', $this->data);
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
     * @return array|RedirectResponse
     */
    public function deactivate($productId, $userId)
    {

        $user = User::where('id', $userId)
            ->update(['status', 0]);
        if ($user) {
            return self::success('user Deactivation', 'deactivated successfully', route('product.subscriber.index', $productId));
        }

        return self::failed('user Deactivation', 'deactivation failed', route('product.subscriber.index', $productId));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateSubscriber $request, $product, $id)
    {

        $validator = $request->validated();
        if ($validator) {

            $user = User::find($id);
            $user->email = $request->email;
            $user->name = $request->firstname;
            $user->surname = $request->surname;
            $user->status = $request->status ?? 0;
            $user->can_notify = $request->notify ?? 0;
            $user->daily_notifications = $request->daily_notifications ?? 0;
            if ($request->has('password') && $request->has('password_comfirmation')) {
                $user->password = bcrypt($request->password);
                $user->password_changed_at = Carbon::now()->toDateTimeString();
            }

            $res = $user->save();
            if ($res) {
                // revoke user tokens
                $user->tokens()->delete();

                return self::success('subscriber', 'changed successfully', route('product.subscriber.index', $product));
            }
        } else {
            return self::failed('Subscriber Update', ' failed', route('product.subscriber.index', $product));
        }
    }

    public function change($productId, $userId)
    {

        $this->data['product'] = Product::find($productId);
        $this->data['user'] = User::find($userId);

        return view('modules.subscriber.change_password', $this->data);
    }

    public function change_password(ChangePassword $request, $productId, $userId)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            $user = User::find($userId);
            $user->password = bcrypt($request->password);
            $user->password_changed_at = Carbon::now()->toDateTimeString();
            $res = $user->save();
            if ($res) {
                return self::success('Password Change', 'changed successfully', route('product.subscriber.index', $productId));
            }
        } else {
            return self::failed('user Deactivation', 'deactivation failed', route('product.subscriber.index', $productId));
        }
    }

    public function get(Request $request, $productId)
    {
        $columns = ['id', 'name', 'email', 'organization_id', 'subscriptions', 'last_login', 'status', 'created_at'];
        $subscriber = User::query();
        $subscriber->with(['subscription']);
        $subscriber->whereIn('type', ['customer', 'organization', 'owner']);

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $page = ($start / $limit) + 1; // Calculate Page Number from DataTable's start and length parameters

        if (empty($request->input('search.value'))) {
            $posts = $subscriber->orderBy($order, $dir)->paginate($limit, ['*'], 'page', $page);
        } else {
            $search = $request->input('search.value');
            $sub = $subscriber->where('email', 'LIKE', "{$search}%");
            $posts = $sub->orderBy($order, $dir)->paginate($limit, ['*'], 'page', $page);
        }

        $data = [];
        if (! empty($posts)) {
            foreach ($posts as $key => $post) {
                $additional = ['reset' => 'fas fa-recycle', 'change' => 'fas fa-key'];
                $actionbtn = self::button_generate('product.subscriber', [$productId, $post->id], $additional, ['destroy']);
                $nestedData['pos'] = ($start++) + 1; // Here we increment the $start for each post
                $nestedData['name'] = trim($post->name.' '.$post->surname);
                $nestedData['email'] = $post->email;
                $nestedData['company'] = $post->organization->name;
                $nestedData['subscriptions'] = '<a href="'.route('user.subscription.index', $post->id).'">'.count($post->subscription->toArray()).'</a>';
                $nestedData['last_login'] = Carbon::parse($post->last_login)->format('dS M Y h:iA');
                $nestedData['registration'] = Carbon::parse($post->created_at)->format('dS M Y');
                $nestedData['status'] = $this->check($post->status);
                $nestedData['notify'] = ($post->daily_notifications) ? 'Yes' : 'No';
                $nestedData['action'] = $actionbtn;
                $data[] = $nestedData;
            }
        }

        $json_data = [
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $posts->total(),
            'recordsFiltered' => $posts->total(),
            'data' => $data,
        ];

        return response()->json($json_data);
    }

    public function bulkform($productId = 0)
    {
        return view('modules.subscriber.bulk', $this->data);
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls,csv',
            ]);
        } catch (ValidationException $e) {
            return self::failed('Subscribers', $e->validator->errors()->first(), route('subscribers.bulk'));
        }

        $data = Excel::toCollection(null, $request->file('excel_file'));

        ImportSubscribers::dispatch($data)->onQueue('low');

        //                if ($data->isNotEmpty()) {
        //                    foreach ($data[0]->skip(1) as $row) {
        //                        $name = $row[0];
        //                        $email = $row[1] ?? null;
        //                        $password = $row[2] ?? null;
        //                        $phone = $row[3] ?? null;
        //                        $change_password = $row[4] ?? "FALSE";
        //                        $real_email = $row[5] ?? "FALSE";
        //
        //                        if ($email /*&& filter_var($email, FILTER_VALIDATE_EMAIL)*/) {
        //                            $details = [
        //                                'name' => $name,
        //                                'surname' => null,
        //                                'phone' => $phone,
        //                                'status' => 1,
        //                                'can_notify' => 1,
        //                                'daily_notifications' => 1,
        //                                'last_login' => now(),
        //                            ];
        //                            if($change_password == "TRUE")
        //                                $details['password'] = bcrypt(trim($password));
        //
        //                            $user = User::updateOrCreate(['email' => trim($email)],$details );
        //
        //                            if($real_email == "TRUE")
        //                            {
        //                                //$user->notify(new NewUserNotification($user, $password));
        //                                if($change_password == "TRUE")
        //                                {
        //                                    //
        //                                }
        //                            }
        //
        //                        }
        //                    }
        //                }

        return self::success('subscriber', 'changed successfully', route('product.subscriber.index', 0));
    }
}
