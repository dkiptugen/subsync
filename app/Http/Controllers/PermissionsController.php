<?php

    namespace App\Http\Controllers;

    use App\Models\User;
    use App\Utils\Sdata;
    use Caydeesoft\Permission\Models\Permission;
    use Illuminate\Http\Request;

    class PermissionsController extends Controller
        {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View|string
         */
            public function index($userid)
                {
                    $this->data['user'] = User::find($userid);
                    return view('modules.permissions.index' ,$this->data);
                }

        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View|string
         */
            public function create($userid)
                {
                    $this->data['user'] = User::find($userid);
                    return view('modules.permissions.add' ,$this->data);
                }

        /**
         * Store a newly created resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return \Illuminate\Http\Response
         */
            public function store(Request $request ,$userid)
                {
                    $user = User::find($userid);
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
                    $this->data['user'] = User::find($userid);
                    $this->data['perm'] = Permission::find($id);
                    return view('modules.permissions.view' ,$this->data);
                }

        /**
         * Show the form for editing the specified resource.
         *
         * @param int $id
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View|string
         */
            public function edit($userid ,$id)
                {
                    return view('modules.permissions.edit' ,$this->data);
                }

        /**
         * Update the specified resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         * @param int                      $id
         *
         * @return \Illuminate\Http\Response
         */
            public function update(Request $request ,$userid ,$id)
                {
                    $this->data['user'] = User::find($userid);
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
                    $this->data['user'] = User::find($userid);
                }

            public function get(Request $request ,$userid)
                {

                    $columns = [0 => 'id' ,1 => 'name' ,2 => 'action'];
                    $totalData = Permission::whereNotNull("name")
                                           ->when($userid != '0' ,function ($q) use ($userid)
                                               {
                                                   return $q->whereHas('permission' ,function ($query) use ($userid)
                                                       {
                                                           $query->where('user_id' ,$userid);
                                                       });
                                               })
                                           ->count();
                    $totalFiltered = $totalData;
                    $limit = $request->input('length');
                    $start = $request->input('start');
                    $order = $columns[$request->input('order.0.column')];
                    $dir = $request->input('order.0.dir');
                    if (empty($request->input('search.value')))
                        {
                            $posts = Permission::whereNotNull("name")
                                               ->when($userid != '0' ,function ($q) use ($userid)
                                                   {
                                                       return $q->whereHas('permission' ,function ($query) use ($userid)
                                                           {
                                                               $query->where('user_id' ,$userid);
                                                           });
                                                   })
                                               ->offset($start)
                                               ->limit($limit)
                                               ->orderBy($order ,$dir)
                                               ->get();
                        }
                    else
                        {
                            $search = $request->input('search.value');


                            $posts = Permission::whereNotNull("name")
                                               ->when($userid != '0' ,function ($q) use ($userid)
                                                   {
                                                       return $q->whereHas('permission' ,function ($query) use ($userid)
                                                           {
                                                               $query->where('user_id' ,$userid);
                                                           });
                                                   })
                                               ->where('name' ,'like' ,"%{$search}%")
                                               ->offset($start)
                                               ->limit($limit)
                                               ->orderBy($order ,$dir)
                                               ->get();

                            $totalFiltered = Permission::whereNotNull("name")
                                                       ->when($userid != '0' ,function ($q) use ($userid)
                                                           {
                                                               return $q->whereHas('permission' ,function ($query) use ($userid)
                                                                   {
                                                                       $query->where('user_id' ,$userid);
                                                                   });
                                                           })
                                                       ->where('name' ,'like' ,"%{$search}%")
                                                       ->count();
                        }

                    $data = [];
                    if (!empty($posts))
                        {
                            $x = $start + 1;
                            foreach ($posts as $post)
                                {
                                    $nestedData['pos'] = $x;
                                    $nestedData['name'] = $post->name;
                                    $nestedData['access'] = $post->action;
                                    $nestedData['roles'] = Sdata::getaccess($post->id);
                                    $nestedData['action'] = '<a href="javascript:;"  class="text-dark mr-3 edit-permission" data-user="' . $post->id . '"><i class="fas fa-edit  "></i></a>
                                                                                <a href="javascript:;"  class="text-dark mr-3 assign-role" data-user="' . $post->id . '"><i class="fas fa-plus-circle  "></i></a>';
                                    $data[] = $nestedData;
                                    $x++;
                                }
                        }

                    $json_data = ["draw" => (int)$request->input('draw') ,"recordsTotal" => $totalData ,"recordsFiltered" => $totalFiltered ,"data" => $data];

                    echo json_encode($json_data);
                }
        }
