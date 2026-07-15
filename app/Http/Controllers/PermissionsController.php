<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Traits\Meta;
use App\Utils\Sdata;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PermissionsController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View|string
     */
    public function index($userid)
    {
        $this->data['user'] = User::find($userid);

        return view('modules.permissions.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View|string
     */
    public function create($userid)
    {
        $this->data['user'] = User::find($userid);

        return view('modules.permissions.add', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $userid)
    {
        $user = User::find($userid);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View|string
     */
    public function show($userid, $id)
    {
        $this->data['user'] = User::find($userid);
        $this->data['perm'] = Permission::query()->find($id);

        return view('modules.permissions.view', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View|string
     */
    public function edit($userid, $id)
    {
        return view('modules.permissions.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $userid, $id)
    {
        $this->data['user'] = User::find($userid);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($userid, $id)
    {
        $this->data['user'] = User::find($userid);
    }

    public function get(Request $request, $userid)
    {
        $columns = [0 => 'id', 1 => 'name', 2 => 'action'];
        $query = Permission::query()
            ->whereNotNull('name')
            ->when($userid != '0', function ($builder) use ($userid) {
                return $builder->whereHas('users', function ($userQuery) use ($userid) {
                    $userQuery->where('users.id', $userid);
                });
            });

        $totalData = $query->count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = (clone $query)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            $posts = (clone $query)
                ->where('name', 'like', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = (clone $query)
                ->where('name', 'like', "%{$search}%")
                ->count();
        }

        $data = [];
        if (! empty($posts)) {
            $x = $start + 1;
            foreach ($posts as $post) {
                $nestedData['pos'] = $x;
                $nestedData['name'] = $post->name;
                $nestedData['access'] = $post->display_name ?? $post->name;
                $nestedData['roles'] = Sdata::getaccess($post->id);
                $nestedData['action'] = '<a href="javascript:;"  class="text-dark mr-3 edit-permission" data-user="'.$post->id.'"><i class="fas fa-edit  "></i></a>
                                                                                <a href="javascript:;"  class="text-dark mr-3 assign-role" data-user="'.$post->id.'"><i class="fas fa-plus-circle  "></i></a>';
                $data[] = $nestedData;
                $x++;
            }
        }

        $json_data = ['draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

        return response()->json($json_data);
    }
}
