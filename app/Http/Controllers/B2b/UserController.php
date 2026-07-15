<?php

    namespace App\Http\Controllers\B2b;

    use App\Exports\OrganizationDependants;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\AddCorporateUserRequest;
    use App\Imports\UploadCorporateUsers;
    use App\Models\B2bSubscription;
    use App\Models\B2bSubscriptionUser;
    use App\Models\Organization;
    use App\Models\PasswordReset;
    use App\Models\User;
    use App\Notifications\NewUserNotification;
    use App\Notifications\PasswordResetRequest;
    use App\Notifications\UserVerificationNotification;
    use App\Traits\Meta;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Maatwebsite\Excel\Facades\Excel;
    use Maatwebsite\Excel\Validators\ValidationException;

    class UserController extends Controller
        {
            use Meta;

            public function __construct(protected array $data = [])
                {
                    $this->data = self::site_def();
                }
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
         */
            public function index($orgid)
                {

                    $this->data['organization'] = Organization::find($orgid);

                    return view('modules.b2b.admin.organization.user_index', $this->data);
                }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
         */
            public function create($orgid)
                {

                    $this->data['organization'] = Organization::find($orgid);
                    $this->data['codes'] = get_country_codes();


                    return view('modules.b2b.admin.organization.user_add', $this->data);
                }

            public function upload($orgid)
                {

                    $this->data['organization'] = Organization::find($orgid);

                    return view('modules.b2b.admin.organization.user_upload', $this->data);
                }

        /**
         * Store a newly created resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
            public function store(AddCorporateUserRequest $request, $orgid)
                {

                    $validateddata = $request->validated();
                    $platform = $request->platform ?? 'epaper';
                    $phone = null;
                    if($request->has('phone'))
                        $phone = $request->code.$request->phone;

                    if ($validateddata)
                        {
                            $token = Str::ulid();
                            $user  = User::updateOrCreate([
                                'email' => $request->email
                            ],
                                [
                                    'name'               => $request->name,
                                    'password'           => bcrypt($request->password),
                                    'status'             => 1,
                                    'phone'              => $phone,
                                    'remember_token'     => $token,
                                    'verification_token' => Str::ulid(),
                                    'type'               => 'organization',
                                    'organization_id'    => $orgid,
                                    //'is_verified'        => $request->genuine,
                                    'daily_notifications' => $request->notify
                                ]);
                            if ($user)
                                {
                                    try
                                        {
                                            $user->notify(new NewUserNotification($user, $request->password));
                                        }
                                    catch (Exception $e)
                                        {
                                            Log::error($e->getMessage());
                                        }
                                    if ((bool)$request->genuine)
                                        {
                                            //$user->notify(new UserVerificationNotification($user,null));
                                            if ((bool)$request->changepass)
                                                {
                                                    PasswordReset::updateOrCreate(['email' => $user->email], ['token' => $token, 'expires_in' => config('custom.CUSTOMER.TOKEN_EXPIRY') * 24 * 60 * 60, 'created_at' => Carbon::now()->toDateTimeString()]);
                                                    $endpoint = email_link($platform);
                                                    $redirect_url = extract_base_url($endpoint);
                                                    $user->notify(new PasswordResetRequest($user, $endpoint, 'Nation Org', $redirect_url, $token,$user->created_at));
                                                }
                                        }

                                    return self::success('Organization User', 'user added successfully', route('organization.user', $orgid));
                                }

                            return self::failed('Organization User', 'Failed to create user', route('organization.user', $orgid));
                        }
                    else
                        {
                            return self::failed('Organization User', $validateddata->error, route('organization.user', $orgid));
                        }

                }

            public function upload_users(Request $request, $orgid)
                {

                    if (!$request->hasFile('files') || !is_array($request->file('files')))
                        {
                            return self::failed('user accounts import', 'no files were uploaded.', route('organization.user', $orgid));
                        }
                    $files = $request->file('files');

                    foreach ($files as $file)
                        {
                            if (!in_array($file->getClientOriginalExtension(), ['xlsx', 'xls', 'csv']))
                                {
                                    return self::failed('user accounts import', 'The file must be an Excel file.', route('organization.user', $orgid));
                                }
                            try
                                {
                                    Excel::import(new UploadCorporateUsers($orgid), $file);

                                    return self::success('user accounts import', 'successful import', route('organization.user', $orgid));
                                }
                            catch (ValidationException $e)
                                {

                                    return self::failed('user accounts import', $e->failures(), route('organization.user', $orgid));

                                }
                            catch (Exception $e)
                                {
                                    return self::failed('user accounts import', $e->getMessage(), route('organization.user', $orgid));
                                }
                        }
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

            public function edit($orgid, $userid)
                {
                    $this->data['organization'] = Organization::find($orgid);
                    $this->data['user'] = User::find($userid);
                    $this->data['codes'] = get_country_codes();

                    return view('modules.b2b.admin.organization.user_edit', $this->data);
                }

        /**
         * Update the specified resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         * @param int $id
         *
         * @return \Illuminate\Http\Response
         */
            public function update(Request $request, $orgid, $userid)
                {
                    $token = Str::ulid();

                    $data = [
                        'name'               => $request->name,
                        'email'              => $request->email,
                            'status'             => 1,
                            'phone'              => $request->phone,
                            'remember_token'     => $token,
                            'verification_token' => Str::ulid(),
                            'type'               => 'organization',
                            'organization_id'    => $orgid,
                            //'is_verified'        => $request->genuine,
                            'daily_notifications' => $request->notify == 1 ? 1 : 0,
                        ];
                       if($request->has('password') && $request->password == $request->password_confirmation)
                           $data['password'] = bcrypt($request->password);


                    $user  = User::updateOrCreate([
                        'id' => $userid,
                    ],$data);

                        if ($user)
                        {
                            return self::success('Organization User', 'user updated successfully', route('organization.user', $orgid));
                        }

                    return self::failed('Organization User', 'Failed to Update user', route('organization.user', $orgid));
                }

        /**
         * Remove the specified resource from storage.
         *
         * @param int $id
         *
         * @return array
         */
            public function destroy($orgid,$id)
                {
                    try
                        {
                            $user                  = User::find($id);
                            $user->organization_id = 0;
                            $res                   = $user->save();
                            if ($res)
                                {
                                    $subscription_users = B2bSubscriptionUser::where('user_id', $id)
                                                                             ->get();
                                    foreach ($subscription_users as $sub)
                                        {
                                            $subscription = B2bSubscription::find($sub->b2b_subscription_id);
                                            $subscription->decrement('records');
                                            $subscription->save();
                                            $sub->delete();
                                        }
                                    return self::success('Organization subscription','User removed successfully',route('organization.user', $orgid));
                                }
                            return self::failed('Organization subscription','Failed to remove user',route('organization.user', $orgid));
                        }
                    catch(\Exception $e)
                        {
                            Log::error($e->getMessage());
                            return self::failed('Organization subscription','Error occured when deleteing user',route('organization.user', $orgid));
                        }


                }

            public function get(Request $request, $orgid)
                {

                    $columns = ['id', 'name', 'email', 'phone', 'status', 'last_login'];


                    $totalData     = User::when($orgid != 0, function ($query) use ($orgid)
                                             {

                                                 $query->where('organization_id', $orgid);
                                             }, function ($query)
                                             {

                                                 $query->where('organization_id', Auth::user()->organization_id);
                                             })->count();
                    $totalFiltered = $totalData;
                    $limit         = $request->input('length');
                    $start         = $request->input('start');
                    $order         = $columns[$request->input('order.0.column')];
                    $dir           = $request->input('order.0.dir');

                    if (empty($request->input('search.value')))
                        {
                            $posts = User::when($orgid != 0, function ($query) use ($orgid)
                                             {

                                                 $query->where('organization_id', $orgid);
                                             }, function ($query)
                                             {

                                                 $query->where('organization_id', Auth::user()->organization_id);
                                             })->offset($start)
                                         ->limit($limit)
                                         ->orderBy($order, $dir)
                                         ->get();
                        }
                    else
                        {

                            $search = $request->input('search.value');

                            $posts = User::when($orgid != 0, function ($query) use ($orgid)
                                             {

                                                 $query->where('organization_id', $orgid);
                                             }, function ($query)
                                             {

                                                 $query->where('organization_id', Auth::user()->organization_id);
                                             })
                                         ->where(function($query)use($search){
                                             return $query->where('name', 'LIKE', "%{$search}%")
                                                          ->orWhere('email', 'LIKE', "%{$search}%")
                                                          ->orWhere('last_login', 'LIKE', "%{$search}%")
                                                          ->orWhere('status', $this->search($search));
                                         })

                                         ->offset($start)
                                         ->limit($limit)
                                         ->orderBy($order, $dir)
                                         ->get();

                            $totalFiltered = User::when($orgid != 0, function ($query) use ($orgid)
                                                     {

                                                         $query->where('organization_id', $orgid);
                                                     }, function ($query)
                                                     {

                                                         $query->where('organization_id', Auth::user()->organization_id);
                                                     })
                                                    ->where(function($query)use($search){
                                                        return $query->where('name', 'LIKE', "%{$search}%")
                                                                     ->orWhere('email', 'LIKE', "%{$search}%")
                                                                     ->orWhere('last_login', 'LIKE', "%{$search}%")
                                                                     ->orWhere('status', $this->search($search));
                                                    })
                                                 ->count();
                        }

                    $data = [];
                    if (!empty($posts))
                        {
                            $pos = $start + 1;

                            foreach ($posts as $post)
                                {
                                    $additional               = [];
                                    $actionbtn                = "<div class='d-flex align-items-center'><a class='text text-dark mr-2' href='".route('organization.user_edit',[$orgid, $post->id])."'><i class='fa fa-edit'></i> </a>".self::button_generate('organization.user', [$orgid, $post->id], $additional)."</div>";
                                    $nestedData['pos']        = $pos;
                                    $nestedData['name']       = $post->name;
                                    $nestedData['email']      = $post->email;
                                    $nestedData['phone']      = $post->phone;
                                    $nestedData['last_login'] = $post->last_login ?? 'Not logged in';
                                    $nestedData['status']     = $this->check($post->status);
                                    $nestedData['notifying']  = $post->daily_notifications == 1? 'Yes' : 'No';
                                    $nestedData['action']     = $actionbtn;
                                    $data[]                   = $nestedData;
                                    $pos++;

                                }
                        }

                    $json_data = ['draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

                    return response()->json($json_data);
                }
            public function export($orgid)
                {
                    $organization = Organization::find($orgid);
                    return Excel::download(new OrganizationDependants($orgid), $organization->name.'-users-'.date('d-m-Y-H-i-s').'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
                }
        }
