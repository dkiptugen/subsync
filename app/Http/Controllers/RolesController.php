<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddRole;
use App\Http\Requests\EditRole;
use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionHelper;
use App\Traits\Meta;
use App\Utils\Sdata;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RolesController extends Controller
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
        return view('modules.roles.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View|string
     */
    public function create($userid)
    {
        $this->data['perm'] = Permission::query()
            ->orderBy('permission_group')
            ->orderBy('name')
            ->get()
            ->groupBy('permission_group')
            ->map(function ($permissions, string $group) {
                return (object) [
                    'name' => $group,
                    'permissions' => $permissions->map(fn (Permission $permission) => (object) [
                        'id' => $permission->id,
                        'actual_name' => $permission->display_name ?? Str::replace('_', ' ', Str::title($permission->name)),
                    ]),
                ];
            })
            ->values();

        return view('modules.roles.add', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return array|Response
     */
    public function store(AddRole $request, $userid)
    {
        $validateddata = $request->validated();
        if ($validateddata) {
            $role = Role::query()->create([
                'name' => $request->role,
                'guard_name' => 'web',
            ]);

            if ($role) {
                if (isset($request->perm)) {
                    $role->syncPermissions($request->perm);
                }

                return self::success('Role', 'Success', route('user.roles.index', 0));
            }

            return self::failed('Role', 'Fail', route('user.roles.index', 0));

        }

        return self::failed('Role', $validateddata, route('user.roles.index', 0));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View|string
     */
    public function show($userid, $id)
    {
        $this->data['role'] = Role::query()->find($id);

        return view('modules.roles.view', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
    public function edit($userid, $id)
    {
        $role = Role::query()->with('permissions')->findOrFail($id);

        $this->data['role'] = $role;
        $this->data['rp'] = $role->permissions;
        $this->data['perm'] = Permission::query()
            ->whereNotNull('name')
            ->orderBy('name', 'asc')
            ->get();

        return view('modules.roles.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return array|Response
     *
     * @throws \Exception
     */
    public function update(EditRole $request, $userid, $id)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            $role = Role::query()->findOrFail($id);
            $role->name = $request->role;
            $req = $role->save();
            if ($req) {
                if (isset($request->perm)) {
                    $role->syncPermissions($request->perm);
                }

                return self::success('Role', 'Success', route('user.roles.index', 0));
            }

            return self::failed('Role', 'Failed', route('user.roles.index', 0));
        }

        return self::failed('Role', $validateddata, route('user.roles.index', 0));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($userid, $id)
    {
        //
    }

    public function get(Request $request, $userid)
    {
        $columns = [0 => 'id', 1 => 'name'];
        $totalData = Role::count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if (empty($request->input('search.value'))) {
            $posts = Role::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            $posts = Role::where('name', 'like', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Role::where('name', 'like', "%{$search}%")
                ->count();
        }

        $data = [];
        if (! empty($posts)) {
            $x = $start + 1;
            foreach ($posts as $post) {
                $btn = '';
                if (PermissionHelper::canAccess('user.roles.edit')) {
                    $btn .= '<a href="'.route('user.roles.edit', [0, $post->id]).'" class="text text-dark mr-2"><i class="fas fa-edit"></i></a>';
                }
                if (PermissionHelper::canAccess('user.roles.show')) {
                    $btn .= '<a href="'.route('user.roles.show', [0, $post->id]).'" class="text text-dark mr-2"><i class="fas fa-eye"></i></a>';
                }
                if (PermissionHelper::canAccess('user.roles.destroy')) {
                    $btn .= '<a href="'.route('user.roles.destroy', [0, $post->id]).'" class="text text-dark delete"><i class="fas fa-trash"></i></a>';
                }
                $nestedData['pos'] = $x;
                $nestedData['name'] = $post->name;
                $nestedData['access'] = Sdata::getperm($post->id);
                $nestedData['action'] = '<div class="d-flex justify-content-between">'.$btn.'</div>';
                $data[] = $nestedData;
                $x++;
            }
        }

        $json_data = ['draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalFiltered, 'data' => $data];

        return response()->json($json_data);
    }
}
