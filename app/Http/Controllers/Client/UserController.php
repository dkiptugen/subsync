<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
    {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response|\Illuminate\View\View
     */
        public function index()
            {
                return view('modules.b2b.client.users.index', $this->data);
            }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
        public function create()
            {
                //
            }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
        public function store(Request $request)
            {
                //
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
        public function edit($id)
            {
                //
            }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
        public function update(Request $request, $id)
            {
                //
            }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
        public function destroy($id)
            {
                //
            }

        public function upload_form($id)
            {
                $this->data['organization'] = Organization::find($id);
                return view('modules.b2b.client.users.upload', $this->data);
            }

        public function upload(Request $request, $id)
            {

            }

        public function get(Request $request)
            {
                $columns = ['id', 'name', 'email', 'name', 'status', 'last_login'];
                $user    = User::query();
                $user->where('organization_id', Auth::user()->organization_id)
                     ->whereType('organization');
                $totalData     = $user->count();
                $totalFiltered = $totalData;
                $limit         = $request->input('length');
                $start         = $request->input('start');
                $order         = $columns[$request->input('order.0.column')];
                $dir           = $request->input('order.0.dir');

                if (empty($request->input('search.value')))
                    {
                        $posts = $user->offset($start)
                                      ->limit($limit)
                                      ->orderBy($order, $dir)
                                      ->get();
                    }
                else
                    {

                        $search = $request->input('search.value');
                        $sub    = $user->where('name', 'LIKE', "%{$search}%")
                                       ->orWhere('email', 'LIKE', "%{$search}%")
                                       ->orWhere('last_login', 'LIKE', "%{$search}%")
                                       ->orWhere('status', $this->search($search));
                        $posts  = $sub->offset($start)
                                      ->limit($limit)
                                      ->orderBy($order, $dir)
                                      ->get();

                        $totalFiltered = $sub->count();
                    }

                $data = array();
                if (!empty($posts))
                    {
                        $pos = $start + 1;
                        $i   = 0;
                        foreach ($posts as $post)
                            {
                                $additional           = [];
                                $actionbtn            = self::button_generate('client_users', $post->id, $additional);
                                $nestedData['pos']    = $pos;
                                $nestedData['name']   = $post->name;
                                $nestedData['email']  = $post->email;
                                $nestedData['status'] = $this->check($post->status);
                                $nestedData['action'] = $actionbtn;
                                $data[]               = $nestedData;
                                $pos++;

                            }
                    }

                $json_data = array('draw' => (int)$request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data);

                return response()->json($json_data);
            }
    }
