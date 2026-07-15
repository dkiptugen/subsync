<?php

    namespace App\Http\Controllers\B2b;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\StoreOrganization;
    use App\Http\Requests\UpdateOrganization;
    use App\Mail\OrgLoginNotification;
    use App\Models\Organization;
    use App\Models\User;
    use App\Traits\Meta;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Str;
    use StdClass;

    class OrganizationController extends Controller
        {
            use Meta;

            public function __construct(protected array $data = [])
                {
                    $this->data = self::site_def();
                }

        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
         */
            public function index()
                {

                    return view('modules.b2b.admin.organization.index', $this->data);
                }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
         */
            public function create()
                {

                    return view('modules.b2b.admin.organization.add', $this->data);
                }

        /**
         * Store a newly created resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
            public function store(StoreOrganization $request)
                {

                    // dd($request->getHost());
                    $validateddata = $request->validated();


                    if ($validateddata)
                        {
                            $password = Str::random(8);
                            $user     = User::firstOrCreate(
                                [
                                    'email' => $request->admin_email
                                ],
                                [
                                    'name'     => $request->admin_name,
                                    'status'   => 1,
                                    'type'     => 'organization',
                                    'password' => bcrypt($password)
                                ]
                            );
                            $org      = Organization::updateOrCreate([
                                'name' => $request->name
                            ],
                                [
                                    'kra_pin'         => $request->kra_pin,
                                    'registration_no' => $request->registration_no,
                                    'address'         => $request->address,
                                    'phone_number'    => $request->phone_number,
                                    'user_id'         => $user->id,
                                    'status'          => 1,
                                ]);


                            if ($org)
                                {
                                    $user->organization_id = $org->id;
                                    $user->save();
                                    $mail               = new \StdClass();
                                    $mail->password     = $password;
                                    $mail->organization = $org->name;
                                    $mail->name         = $user->name;
                                    //Mail::to($user->email)->send(new OrgLoginNotification($mail));

                                    return self::success('Organization', 'organization added successfully', route('organization.index'));

                                }

                            return self::failed('Organization', 'organization registration failed', route('organization.index'));


                        }

                    return self::failed('Organization', $validateddata, route('organization.index'));

                }

        /**
         * Display the specified resource.
         *
         * @param \App\Models\Company $company
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
         */
            public function show(Request $id)
                {

                    $this->data['org'] = Organization::find($id);

                    return view('modules.b2b.admin.organization.view', $this->data);
                }

        /**
         * Show the form for editing the specified resource.
         *
         * @param \App\Models\Company $company
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
         */
            public function edit($id)
                {

                    $this->data['organization'] = Organization::find($id);

                    return view('modules.b2b.admin.organization.edit', $this->data);
                }

        /**
         * Update the specified resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         * @param \App\Models\Company $company
         *
         * @return array
         */
            public function update(UpdateOrganization $request, $id)
                {

                    // dd($request->getHost());
                    $validateddata = $request->validated();


                    if ($validateddata)
                        {
                            $password = Str::random(8);
                            $user     = User::firstOrCreate(
                                [
                                    'email' => $request->admin_email
                                ],
                                [
                                    'name'            => $request->admin_name,
                                    'status'          => 1,
                                    'type'            => 'organization',
                                    'password'        => bcrypt($password),
                                    'organization_id' => $id
                                ]
                            );
                            $org      = Organization::find($id)
                                                    ->update([
                                                        'name'            => $request->name,
                                                        'address'         => $request->address,
                                                        'phone_number'    => $request->phone_number,
                                                        'kra_pin'         => $request->kra_pin,
                                                        'registration_no' => $request->registration_no,
                                                        'user_id'         => $user->id
                                                    ]);


                            if ($org)
                                {
                                    return self::success('Organization', 'organization added successfully', route('organization.index'));

                                }

                            return self::failed('Organization', 'organization registration failed', route('organization.index'));


                        }

                    return self::failed('Organization', $validateddata, route('organization.index'));
                }

        /**
         * Remove the specified resource from storage.
         *
         * @param \App\Models\Company $company
         *
         * @return array
         */
            public function destroy($id, $val)
                {

                    $org         = Organization::find($id);
                    $org->status = $val;
                    $res         = $org->save();
                    if ($res)
                        {
                            return self::success('Organization', 'organization updated successfully', url('dashboard/organization'));
                        }

                    return self::failed('Organization', 'organization update failed', url('dashboard/organization'));

                }

            public function get(Request $request)
                {

                    $columns = ['id', 'name', 'address', 'phone_number', 'user.name', 'user.email'];

                    $totalData     = Organization::count();
                    $totalFiltered = $totalData;
                    $limit         = $request->input('length');
                    $start         = $request->input('start');
                    $order         = $columns[$request->input('order.0.column')];
                    $dir           = $request->input('order.0.dir');


                    if (empty($request->input('search.value')))
                        {
                            $posts = Organization::with(['user'])
                                                 ->offset($start)
                                                 ->limit($limit)
                                                 ->orderBy($order, $dir)
                                                 ->get();
                        }
                    else
                        {

                            $search = $request->input('search.value');

                            $posts = Organization::with(['user'])
                                                 ->where(function ($query) use ($search)
                                                     {

                                                         return $query->where('name', 'LIKE', "%{$search}%")
                                                                      ->orWhere('phone_number', 'LIKE', "%{$search}%")
                                                                      ->orWhere('address', 'LIKE', "%{$search}%")
                                                                      ->orWhereHas('user', function ($subquery) use ($search)
                                                                          {

                                                                              return $subquery->where('email', 'LIKE', "%{$search}%")
                                                                                              ->orWhere('name', 'LIKE', "%{$search}%");
                                                                          });
                                                     })
                                                 ->offset($start)
                                                 ->limit($limit)
                                                 ->orderBy($order, $dir)
                                                 ->get();

                            $totalFiltered = Organization::where(function ($query) use ($search)
                                {

                                    return $query->where('name', 'LIKE', "%{$search}%")
                                                 ->orWhere('phone_number', 'LIKE', "%{$search}%")
                                                 ->orWhere('address', 'LIKE', "%{$search}%")
                                                 ->orWhereHas('user', function ($subquery) use ($search)
                                                     {

                                                         return $subquery->where('email', 'LIKE', "%{$search}%")
                                                                         ->orWhere('name', 'LIKE', "%{$search}%");
                                                     });
                                })
                                                         ->count();
                        }

                    $data = [];
                    if (!empty($posts))
                        {
                            $x = $start + 1;
                            foreach ($posts as $post)
                                {
                                    $addition                      = ['user' => 'fas fa-users', 'password' => 'fas fa-lock'];
                                    $nestedData['pos']             = $x;
                                    $nestedData['name']            = $post->name ?? '';
                                    $nestedData['address']         = $post->address;
                                    $nestedData['phone_number']    = $post->phone_number;
                                    $nestedData['kra_pin']         = $post->kra_pin;
                                    $nestedData['registration_no'] = $post->registration_no;
                                    $nestedData['admin_name']      = $post->user->name ?? '';
                                    $nestedData['admin_email']     = $post->user->email ?? '';
                                    $nestedData['status']          = $this->check($post->status);
                                    $nestedData['action']          = self::button_generate('organization', $post->id, $addition);
                                    $data[]                        = $nestedData;
                                    $x++;
                                }
                        }

                    $json_data = [
                        'draw'            => (int)$request->input('draw'),
                        'recordsTotal'    => $totalData,
                        'recordsFiltered' => $totalFiltered,
                        'data'            => $data,

                    ];

                    return response()->json($json_data);
                }

            public function password($orgid)
                {
                    $this->data['organization'] = Organization::find($orgid);

                    return view('modules.b2b.admin.organization.password', $this->data);
                }

            public function set_default_password(Request $request, $orgid)
                {

                    $data = User::where('organization_id', $orgid)
                                ->update(['password' => bcrypt($request->password), 'password_changed_at' => \Illuminate\Support\Carbon::now()->toDateTimeString()]);
                    if ($data)
                        {
                            return self::success('Organization', 'organizations default password added successfully. ' . $data . ' records updated', route('organization.index'));

                        }
                    return self::failed('Organization', 'organizations default password failed', route('organization.index'));
                }

        }

