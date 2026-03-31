<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\AddRole;
    use App\Http\Requests\EditRole;
    use App\Http\Requests\StoreRole;
    use App\Http\Requests\UpdateRole;
    use App\Utils\Sdata;
    use Caydeesoft\Permission\Models\Permission;
    use Caydeesoft\Permission\Models\PermissionGroup;
    use Caydeesoft\Permission\Models\PermissionRole;
    use Caydeesoft\Permission\Models\Role;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    class RolesController extends Controller
        {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View|string
         */
            public function index($userid)
                {
                    return view('modules.roles.index' ,$this->data);
                }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View|string
         */
            public function create($userid)
                {
                    $this->data['perm'] = PermissionGroup::with(['permissions'])
                                                    ->orderBy('name' ,'asc')
                                                    ->get();
                    return view('modules.roles.add' ,$this->data);
                }

        /**
         * Store a newly created resource in storage.
         *
         * @param \App\Http\Requests\AddRole $request
         * @param $userid
         * @return array|\Illuminate\Http\Response
         */
            public function store(AddRole $request , $userid)
                {
                    $validateddata = $request->validated();
                    if ($validateddata)
                        {
                            $role = new Role();
                            $role->name = $request->role;
                            $req = $role->save();
                            if ($req)
                                {
                                    if (isset($request->perm))
                                        {

                                            foreach ($request->perm as $value)
                                                {
                                                    $pr = new PermissionRole();
                                                    $pr->role_id = $role->id;
                                                    $pr->permission_id = $value;
                                                    $pr->save();
                                                }
                                        }

                                    return self::success('Role' ,'Success' ,route('user.roles.index' ,0));
                                }

                            return self::fail('Role' ,'Fail' ,route('user.roles.index' ,0));

                        }

                    return self::fail('Role' ,$validateddata ,route('user.roles.index' ,0));

                }

        /**
         * Display the specified resource.
         *
         * @param int $id
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View|string
         */
            public function show($userid ,$id)
                {
                    $this->data['role'] = Role::find($id);
                    return view('modules.roles.view' ,$this->data);
                }

        /**
         * Show the form for editing the specified resource.
         *
         * @param int $id
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
         */
            public function edit($userid ,$id)
                {
                    $this->data['role'] = Role::find($id);
                    $this->data['rp'] = PermissionRole::where('role_id' ,$id)
                                                      ->get();
                    $this->data['perm'] = Permission::whereNotNull("name")
                                                    ->orderBy('name' ,'asc')
                                                    ->get();
                    return view('modules.roles.edit' ,$this->data);
                }

        /**
         * Update the specified resource in storage.
         *
         * @param \App\Http\Requests\EditRole $request
         * @param $userid
         * @param int $id
         *
         * @return array|\Illuminate\Http\Response
         * @throws \Exception
         */
            public function update(EditRole $request , $userid , $id)
                {

                    $validateddata = $request->validated();
                    if ($validateddata)
                        {
                            $role = Role::find($id);
                            $role->name = $request->role;
                            $req = $role->save();
                            if ($req)
                                {
                                    if (isset($request->perm))
                                        {
                                            PermissionRole::where('role_id' ,$id)
                                                          ->delete();
                                            foreach ($request->perm as $value)
                                                {
                                                    $pr = new PermissionRole();
                                                    $pr->role_id = $id;
                                                    $pr->permission_id = $value;
                                                    $pr->save();
                                                }
                                        }

                                    return self::success('Role' ,'Success' ,route('user.roles.index' ,0));
                                }
                            return self::fail('Role' ,'Failed' ,route('user.roles.index' ,0));
                        }
                    return self::fail('Role' ,$validateddata ,route('user.roles.index' ,0));
                }

        /**
         * Remove the specified resource from storage.
         *
         * @param int $id
         *
         * @return \Illuminate\Http\Response
         */
            public function destroy($userid ,$id)
                {
                    //
                }

            public function get(Request $request ,$userid)
                {
                    $columns = [0 => 'id' ,1 => 'name'];
                    $totalData = Role::count();
                    $totalFiltered = $totalData;
                    $limit = $request->input('length');
                    $start = $request->input('start');
                    $order = $columns[$request->input('order.0.column')];
                    $dir = $request->input('order.0.dir');
                    if (empty($request->input('search.value')))
                        {
                            $posts = Role::offset($start)
                                         ->limit($limit)
                                         ->orderBy($order ,$dir)
                                         ->get();
                        }
                    else
                        {
                            $search = $request->input('search.value');


                            $posts = Role::where('name' ,'like' ,"%{$search}%")
                                         ->offset($start)
                                         ->limit($limit)
                                         ->orderBy($order ,$dir)
                                         ->get();

                            $totalFiltered = Role::where('name' ,'like' ,"%{$search}%")
                                                 ->count();
                        }

                    $data = [];
                    if (!empty($posts))
                        {
                            $x = $start + 1;
                            foreach ($posts as $post)
                                {
                                    $btn = "";
                                    if (Auth::user()->permission->contains('name' ,'user.roles.edit'))
                                        {
                                            $btn .= '<a href="' . route('user.roles.edit' ,[0 ,$post->id]) . '" class="text text-dark mr-2"><i class="fas fa-edit"></i></a>';
                                        }
                                    if (Auth::user()->permission->contains('name' ,'user.roles.show'))
                                        {
                                            $btn .= '<a href="' . route('user.roles.show' ,[0 ,$post->id]) . '" class="text text-dark mr-2"><i class="fas fa-eye"></i></a>';
                                        }
                                    if (Auth::user()->permission->contains('name' ,'user.roles.destroy'))
                                        {
                                            $btn .= '<a href="' . route('user.roles.destroy' ,[0 ,$post->id]) . '" class="text text-dark delete"><i class="fas fa-trash"></i></a>';
                                        }
                                    $nestedData['pos'] = $x;
                                    $nestedData['name'] = $post->name;
                                    $nestedData['access'] = Sdata::getperm($post->id);
                                    $nestedData['action'] = '<div class="d-flex justify-content-between">' . $btn . '</div>';
                                    $data[] = $nestedData;
                                    $x++;
                                }
                        }

                    $json_data = ["draw" => (int)$request->input('draw') ,"recordsTotal" => $totalData ,"recordsFiltered" => $totalFiltered ,"data" => $data];

                    return response()->json($json_data);
                }
        }
