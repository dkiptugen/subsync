<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeWhitelist;
use App\Http\Requests\updateWhitelist;
use App\Jobs\UserWhitelistJob;
use App\Models\IpWhitelist;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Models\UserWhitelist;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;


class UserWhitelistController extends Controller
        {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
         */
            public function index($type)
                {
                    return view('modules.user_whitelist.' . $type . '.index', $this->data);
                }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
        public function create($type)
            {

                $this->data['products'] = Product::where('status', 1)->get();
                if ($type == 'organization' || $type == 'ipaddress')
                    {
                        $this->data['organizations'] = Organization::where('status', 1)->get();
                    }

                return view('modules.user_whitelist.' . $type . '.add', $this->data);
            }


        public function get($type, Request $request)
            {
                if ($type == 'ipaddress')
                    {
                        $columns = ['id', 'organization_id', 'ipaddress', 'product_id', 'reason', 'user_id', 'startdate', 'enddate'];
                        //$whitelist->where('whitelistable_type','like','%' .$type.'%');
                        $totalData     = IpWhitelist::count();
                        $totalFiltered = $totalData;
                        $limit         = $request->input('length');
                        $start         = $request->input('start');
                        $order         = $columns[$request->input('order.0.column')];
                        $dir           = $request->input('order.0.dir');

                        if (empty($request->input('search.value')))
                            {
                                $posts = IpWhitelist::offset($start)
                                                    ->limit($limit)
                                                    ->orderBy($order, $dir)
                                                    ->get();
                            }
                        else
                            {


                                $search = $request->input('search.value');


                                $posts = IpWhitelist::where('startdate', 'LIKE', "%{$search}%")
                                                    ->orWhere('enddate', 'LIKE', "%{$search}%")
                                                    ->offset($start)
                                                    ->limit($limit)
                                                    ->orderBy($order, $dir)
                                                    ->get();

                                $totalFiltered = IpWhitelist::where('startdate', 'LIKE', "%{$search}%")
                                                            ->orWhere('enddate', 'LIKE', "%{$search}%")
                                                            ->offset($start)
                                                            ->limit($limit)
                                                            ->when($order == 'name', function ($query) use ($order, $dir)
                                                                {

                                                                    return $query->whereHas('whitelistable', function ($query) use ($order, $dir)
                                                                        {

                                                                            return $query->orderBy($order, $dir);
                                                                        });
                                                                }, function ($query) use ($order, $dir)
                                                                {

                                                                    return $query->orderBy($order, $dir);
                                                                })
                                                            ->count();
                            }


                        $data = [];
                        if (!empty($posts))
                            {
                                $pos = $start + 1;


                                foreach ($posts as $post)
                                    {
                                        $additional              = [];
                                        $actionbtn               = self::button_generate('whitelist.type', [$type, $post->id], $additional);
                                        $nestedData['pos']       = $pos;
                                        $nestedData['name']      = Organization::find($post->organization_id)->name;
                                        $nestedData['ipaddress'] = $post->ip_address;
                                        $nestedData['product']   = Product::find($post->product_id)->product_name;
                                        $nestedData['startdate'] = Carbon::parse($post->startdate)
                                                                         ->format('d-m-Y');
                                        $nestedData['reason']    = $post->reason;
                                        $nestedData['enddate']   = Carbon::parse($post->enddate)
                                                                         ->format('d-m-Y');
                                        $nestedData['author']    = User::find($post->user_id)->name;
                                        $nestedData['status']    = $this->check($post->status);
                                        $nestedData['action']    = $actionbtn;
                                        $data[]                  = $nestedData;
                                        $pos++;

                                    }
                            }
                    }
                else
                    {
                        $columns = ['id', 'name', 'email', 'whitelistable_type', 'product','reason', 'user_id', 'startdate', 'enddate'];
                        //$whitelist->where('whitelistable_type','like','%' .$type.'%');
                        $totalData     = UserWhitelist::where('whitelistable_type', 'like', '%' . $type . '%')->count();
                        $totalFiltered = $totalData;
                        $limit         = $request->input('length');
                        $start         = $request->input('start');
                        $order         = $columns[$request->input('order.0.column')];
                        $dir           = $request->input('order.0.dir');

                        if (empty($request->input('search.value')))
                            {
                                $posts = UserWhitelist::with(['user', 'product'])
                                                      ->where('whitelistable_type', 'like', '%' . $type . '%')
                                                      ->offset($start)
                                                      ->limit($limit)
                                                      ->when($order == 'name', function ($query) use ($order, $dir)
                                                          {

                                                              return $query->whereHas('whitelistable', function ($query) use ($order, $dir)
                                                                  {

                                                                      return $query->orderBy($order, $dir);
                                                                  });
                                                          }, function ($query) use ($order, $dir)
                                                          {

                                                              return $query->orderBy($order, $dir);
                                                          })
                                                      ->get();
                            }
                        else
                            {

                                $search = $request->input('search.value');


                                $posts = UserWhitelist::with(['user', 'product'])
                                                      ->where('whitelistable_type', 'like', '%' . $type . '%')
                                                      ->where(function ($query) use ($search,$type){
                                                          $query->whereHas('customer', function ($query) use ($search)
                                                          {
                                                              return $query->where(function ($query) use ($search){
                                                                  return $query
                                                                      ->where('email', 'LIKE', "%{$search}%");
                                                              });
                                                          });
                                                          $query->orWhere('tag', 'LIKE', "%{$search}%");
                                                      })

//                                                      ->orWhere('whitelistable_type', 'LIKE', "%{$search}%")
//                                                      ->orWhere('startdate', 'LIKE', "%{$search}%")
//                                                      ->orWhere('enddate', 'LIKE', "%{$search}%")

                                                      ->offset($start)
                                                      ->limit($limit)
                                                      ->when($order == 'name', function ($query) use ($order, $dir)
                                                          {

                                                              return $query->whereHas('whitelistable', function ($query) use ($order, $dir)
                                                                  {

                                                                      return $query->orderBy($order, $dir);
                                                                  });
                                                          }, function ($query) use ($order, $dir)
                                                          {

                                                              return $query->orderBy($order, $dir);
                                                          })
                                                      ->get();

                                $totalFiltered = UserWhitelist::with(['user', 'product'])
                                                              ->where('whitelistable_type', 'like', '%' . $type . '%')
                                                              ->where(function ($query) use ($search,$type){
                                                                    $query->whereHas('customer', function ($query) use ($search)
                                                                    {
                                                                        return $query->where(function ($query) use ($search){
                                                                            return $query
                                                                                ->where('email', 'LIKE', "%{$search}%");
                                                                        });
                                                                    });
                                                                    $query->orWhere('tag', 'LIKE', "%{$search}%");
                                                                })
                                                              ->offset($start)
                                                              ->limit($limit)
                                                              ->when($order == 'name', function ($query) use ($order, $dir)
                                                                  {

                                                                      return $query->whereHas('whitelistable', function ($query) use ($order, $dir)
                                                                          {

                                                                              return $query->orderBy($order, $dir);
                                                                          });
                                                                  }, function ($query) use ($order, $dir)
                                                                  {

                                                                      return $query->orderBy($order, $dir);
                                                                  })
                                                              ->count();
                            }

                        $data = [];
                        if (!empty($posts))
                            {
                                $pos = $start + 1;

                                foreach ($posts as $post)
                                    {
                                        $additional              = [];
                                        $actionbtn               = self::button_generate('whitelist.type', [$type, $post->id], $additional);
                                        $nestedData['pos']       = $pos;
                                        $nestedData['name']      = $post->whitelistable->name;
                                        $nestedData['email']      = $post->whitelistable->email;
                                        $nestedData['type']      = $post->whitelistable_type;
                                        $nestedData['product']   = $post->product->product_name;
                                        $nestedData['startdate'] = Carbon::parse($post->startdate)
                                                                         ->format('d-m-Y');
                                        $nestedData['reason']    = $post->reason;
                                        $nestedData['enddate']   = Carbon::parse($post->enddate)
                                                                         ->format('d-m-Y');
                                        $nestedData['tag']       = $post->tag;
                                        $nestedData['author']    = $post->user->name;
                                        $nestedData['status']    = $this->check($post->status);
                                        $nestedData['action']    = $actionbtn;
                                        $data[]                  = $nestedData;
                                        $pos++;

                                    }
                            }
                    }


                $json_data = ['draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

                return response()->json($json_data);
            }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
        public function store($type, storeWhitelist $request)
            {

                try
                    {
                        $validateddata = $request->validated();
                        if ($validateddata)
                            {
                                if ($type == 'organization')
                                    {
                                        if (!$request->has('organization'))
                                            {
                                                return self::fail('Whitelist', 'Organization not selected', route('whitelist.type.index', $type));
                                            }
                                        $org = Organization::find($request->organization);
                                        $wl  = $org->whitelist()->updateOrCreate([
                                                                                     'product_id' => $request->product,
                                                                                     'user_id'    => Auth::user()->id,
                                                                                     'reason'     => $request->reason,
                                                                                     'status'     => 1,
                                                                                     'startdate'  => $request->startdate,
                                                                                     'enddate'    => $request->enddate,
                                                                                     'tag' => $request->tag,
                                                                                 ]);
                                        //attach_products($wl);

                                        if ($wl)
                                            {
                                                return self::success('Whitelist', $org->name . ' added to the whitelist', route('whitelist.type.index', $type));
                                            }
                                    }
                                elseif ($type == 'user')
                                    {
                                        try{
                                            $request->validate([
                                                'email' => 'nullable|email|required_without:excel_file',
                                                'excel_file' => 'nullable|file|mimes:xlsx,xls,csv|required_without:email',
                                            ]);
                                        }catch (ValidationException $e)
                                        {
                                            return self::fail('Whitelist', $e->validator->errors()->first(), route('whitelist.type.index', $type));
                                        }

                                        $emails = [];

                                        if ($request->filled('email')) {
                                            $emails[] = trim($request->input('email'));
                                        }

                                        if ($request->hasFile('excel_file')) {
                                            $data = Excel::toCollection(null, $request->file('excel_file'));

                                            if ($data->isNotEmpty()) {
                                                foreach ($data[0]->skip(1) as $row) {
                                                    $email = $row[0] ?? null;
                                                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                                        $emails[] = trim($email);
                                                    }
                                                }
                                            }
                                        }

//                                        if (!$request->has('email'))
//                                            {
//                                                return self::fail('Whitelist', 'Email not set', route('whitelist.type.index', $type));
//                                            }

                                        $count = count($emails);
                                        $max = 10;
                                        
                                        if($count > 0)
                                        {
                                            $slice = array_slice($emails, 0, $max);
                                        }
                                        else{
                                            return self::fail('Whitelist', 'No email addresses supplied', route('whitelist.type.index', $type));
                                        }

                                        $users = User::whereIn('email', $slice)
                                                    ->get();

                                        if ($users->isEmpty())
                                            {
                                                return self::fail('Whitelist', 'User/ users not found in the database, ask the user/users to register first', route('whitelist.type.index', $type));
                                            }
                                        else
                                            {
                                                if($count > 10)
                                                {
                                                    //$newRequest = new Request($request->except(['excel_file']));
                                                    //UserWhitelistJob::dispatch($emails,\auth()->user()->id,$newRequest);
                                                    $wl = true;
                                                }
                                                else{

                                                    foreach ($users as $user) {
                                                      $sub =  $user->whitelist()->updateOrCreate([
                                                            'product_id' => $request->product,
                                                            'user_id' => Auth::user()->id,
                                                            'reason' => $request->reason,
                                                            'status' => 1,
                                                            'startdate' => Carbon::parse($request->startdate)->startOfDay()->toDateTimeString(),
                                                            'enddate' => Carbon::parse($request->enddate)->endOfDay()->toDateTimeString(),
                                                            'tag' => $request->tag
                                                        ]);
                                                    }

                                                    //attach_products($sub);
                                                }
                                                $newRequest = new Request($request->except(['excel_file']));
                                                UserWhitelistJob::dispatch($emails,\auth()->user()->id,$newRequest);

                                                $wl = true;

                                                if ($wl)
                                                    {
                                                        return self::success('Whitelist', 'Users added to the whitelist', route('whitelist.type.index', $type));
                                                    }
                                            }
                                    }
                                elseif ($type == 'ipaddress')
                                    {
                                        $ipaddress = IpWhitelist::updateOrCreate([
                                                                                     'ip_address'             => $request->ipaddress,
                                                                                     'organization_id'        => $request->organization,
                                                                                     'product_id'             => $request->product,
                                                                                     'concurrent_connections' => $request->users,
                                                                                     'user_id'                => Auth::user()->id,
                                                                                     'reason'                 => $request->reason,
                                                                                     'status'                 => 1,
                                                                                     'startdate'              => $request->startdate,
                                                                                     'enddate'                => $request->enddate
                                                                                 ]);
                                        if ($ipaddress)
                                            {
                                                return self::success('Whitelist', ' added to the whitelist', route('whitelist.type.index', $type));
                                            }
                                    }


                                return self::fail('WhiteList', 'non existent route', route('whitelist.type.index', $type));
                            }
                        else
                            {
                                return self::fail('Whitelist', $validateddata, route('whitelist.type.index', $type));
                            }

                    }
                catch (Exception $e)
                    {
                        return self::fail('Whitelist', $e->getMessage(), route('whitelist.type.index', $type));
                    }

            }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\UserWhitelist $userWhitlist
     *
     * @return \Illuminate\Http\Response
     */
        public function show($type, UserWhitelist $userWhitlist)
            {
                //
            }

        /**
         * Show the form for editing the specified resource.
         *
         * @param \App\Models\UserWhitelist $userWhitlist
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
         */
            public function edit($type, $id)
                {

                    $this->data['products']  = Product::where('status', 1)->get();
                    $this->data['whitelist'] = UserWhitelist::find($id);
                    if ($type == 'organization')
                        {
                            $this->data['organizations'] = Organization::where('status', 1)->get();
                        }


                return view('modules.user_whitelist.' . $type . '.edit', $this->data);
            }

        /**
         * Update the specified resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         * @param \App\Models\UserWhitelist $userWhitlist
         *
         * @return array
         */
            public function update($type, updateWhitelist $request, $id)
                {

                    try
                        {
                            $validateddata = $request->validated();
                            if ($validateddata)
                                {
                                    $userWhitlist = UserWhitelist::find($id);

                                    $wl = $userWhitlist->updateOrFail([
                                        'product_id' => $request->product,
                                        'user_id'    => Auth::user()->id,
                                        'reason'     => $request->reason,
                                        'status'     => $request->status,
                                        'startdate'  => Carbon::parse($request->startdate)->startOfDay()->toDateTimeString(),
                                        'enddate'    => Carbon::parse($request->enddate)->endOfDay()->toDateTimeString(),
                                        'tag' => $request->tag,
                                    ]);
                                    if ($wl)
                                        {
                                            return self::success('Whitelist', ' Updated successfully', route('whitelist.type.index', $type));
                                        }
                                }
                            else
                                {
                                    return self::fail('Whitelist', $validateddata, route('whitelist.type.index', $type));
                                }


                    }
                catch (Exception $e)
                    {
                        return self::fail('Whitelist', $e->getMessage(), route('whitelist.type.index', $type));
                    }
            }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\UserWhitelist $userWhitlist
     *
     * @return \Illuminate\Http\Response
     */
        public function destroy($type, UserWhitelist $userWhitlist)
            {
                //
            }
    }
