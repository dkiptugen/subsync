<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Http\Requests\AddUser;
use App\Http\Requests\EditUser;
use App\Http\Requests\StoreUser;
use App\Http\Requests\UpdateProfile;
use App\Models\Role;
use App\Models\User;
use App\Traits\Meta;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Application|View
     */
    public function index()
    {
        return view('modules.users.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Response|View|string
     */
    public function create()
    {

        $this->data['role'] = Role::get();

        return view('modules.users.add', $this->data);
    }

    /**
     * @return void
     */
    public function get(Request $request)
    {

        $columns = [0 => 'id', 1 => 'name', 2 => 'email', 3 => 'status', 4 => 'role'];
        $totalData = User::whereType('owner')->count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = User::whereType('owner')->with('roles')->offset($start)->limit($limit)->orderBy($order,
                $dir)->get();
        } else {

            $search = $request->input('search.value');
            $posts = User::whereType('owner')->with('roles')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "{$search}%")
                        ->orWhere('email', 'LIKE', "{$search}%")
                        ->orWhere('status', 'LIKE', "{$search}%");
                })
                ->offset($start)->limit($limit)->orderBy($order, $dir)->get();

            $totalFiltered = User::whereType('owner')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "{$search}%")
                        ->orWhere('email', 'LIKE', "{$search}%")
                        ->orWhere('status', 'LIKE', "{$search}%");
                })
                ->count();
        }

        $data = [];
        if (! empty($posts)) {
            $pos = $start + 1;
            foreach ($posts as $post) {

                $btn = self::button_generate('user', $post->id, []);
                $nestedData['id'] = $pos;
                $nestedData['name'] = trim($post->name.' '.$post->surname);
                $nestedData['email'] = $post->email;
                $nestedData['status'] = ($post->status == 1)
                    ? 'Active'
                    : 'inactive';
                $nestedData['role'] = $post->roles->first()?->name;
                $nestedData['action'] = $btn;
                $nestedData['notify'] = ($post->can_notify)
                    ? 'Yes'
                    : 'No';
                $data[] = $nestedData;
                $pos++;

            }
        }

        $json_data = [
            'draw' => (int) $request->input('draw'), 'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered, 'data' => $data,
        ];

        echo json_encode($json_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreUser  $request
     * @return array|\Illuminate\Contracts\Foundation\Application|RedirectResponse|Response|Redirector|void
     */
    // public function store (StoreUser $request)
    public function store(AddUser $request)
    {
        $validateddata = $request->validated();
        if ($validateddata) {
            $user = User::where('email', strtolower($request->email))
                ->first();
            if (is_null($user)) {
                $user = new User;
                $user->email = strtolower(trim($request->email));
                $user->name = $request->name;
                if ((isset($request->password) && ! empty($request->password)) || (isset($request->con_password) && ! empty($request->con_password))) {
                    $valid = $request->validate([
                        'password' => ['required',
                            'string',
                            'min:'.config('custom.AUTHENTICATION.PASSWORD_MINIMUM_LENGTH'),
                            'regex:'.config('custom.AUTHENTICATION.PASSWORD_COMPLEXITY_REGEX'),
                            'same:con_password'],
                        'con_password' => ['required'],
                    ]);
                    if ($valid) {
                        $user->password = bcrypt(trim($request->password));
                    } else {
                        return self::failed('User', $valid, route('user.index'));
                    }
                }

                $user->status = $request->status ?? 0;
                $user->type = $request->user()->type;
                $usr = $user->save();
                if ($usr) {
                    $this->syncUserRole($user, $request->role_id);

                    return self::success('User', 'Added user successfully', route('user.index'));
                }
            } else {
                if ((isset($request->password) && ! empty($request->password)) || (isset($request->con_password) && ! empty($request->con_password))) {
                    $valid = $request->validate([
                        'password' => ['required',
                            'string',
                            'min:'.config('custom.AUTHENTICATION.PASSWORD_MINIMUM_LENGTH'),
                            'regex:'.config('custom.AUTHENTICATION.PASSWORD_COMPLEXITY_REGEX'),
                            'same:con_password'],
                        'con_password' => ['required'],
                    ]);
                    if ($valid) {
                        $usr = $user->update(['password' => bcrypt(trim($request->password)), 'status' => 1, 'type' => $request->user()->type]);
                    } else {
                        return self::failed('User', $valid, route('user.index'));
                    }

                } else {
                    $usr = $user->update(['status' => 1, 'type' => $request->user()->type]);
                }
                if ($usr) {
                    $this->syncUserRole($user, $request->role);

                    return self::success('User', 'Updated user successfully', route('user.index'));
                }
            }

            return self::failed('User', 'Failed to add user', route('user.index'));
        }

        return self::failed('User', $validateddata, route('user.index'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return array|Response
     */
    public function update(EditUser $request, $id)
    {

        $validateddata = $request->validated();
        if ($validateddata) {

            $user = User::find($id);
            $user->email = strtolower($request->email);
            $user->name = $request->name;
            // $user->can_notify = $request->notify;
            if ((isset($request->password) && ! empty($request->password)) || (isset($request->con_password) && ! empty($request->con_password))) {
                $valid = $request->validate([
                    'password' => [
                        'required', 'string', 'min:'.config('custom.AUTHENTICATION.PASSWORD_MINIMUM_LENGTH'),
                        'regex:'.config('custom.AUTHENTICATION.PASSWORD_COMPLEXITY_REGEX'), 'same:con_password',
                    ], 'con_password' => ['required'],
                ]);
                if ($valid) {
                    // Auth::logoutOtherDevices($request->password);
                    $user->password = bcrypt(trim($request->password));
                } else {
                    return self::failed('User', $valid, route('user.index'));
                }
            }
            $user->{'type'} = $request->user()->type;
            $user->status = $request->status ?? 0;
            $usr = $user->save();
            if ($usr) {
                $this->syncUserRole($user, $request->role);

                return self::success('User', 'Updated user successfully', route('user.index'));
            }

            return self::failed('User', 'Failed to update user', route('user.index'));
        }

        return self::failed('User', $validateddata, route('user.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Response|View
     */
    public function edit($id)
    {

        $this->data['user'] = User::query()->with('roles')->find($id);
        $this->data['role'] = Role::get();

        return view('modules.users.edit', $this->data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return array|RedirectResponse|Response
     */
    public function destroy($id)
    {
        try {
            $user = User::query()->findOrFail($id);
            $user->type = 'customer';
            $user->save();
            $user->syncRoles([]);

            return self::success('User', 'User removed from organization',
                route('user.index'));

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::failed('User', 'encountered an error when removing the user',
                route('user.index'));
        }

    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export_view(): BinaryFileResponse
    {
        Log::info('method reached');

        return Excel::download(new UserExport, 'users.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Application|View
     */
    public function profile()
    {

        $this->data['user'] = Auth::user();

        return view('modules.users.profile', $this->data);
    }

    /**
     * @return array|RedirectResponse
     */
    public function profile_update(UpdateProfile $request, $id)
    {

        $validateddata = $request->validated();
        if ($validateddata) {
            $user = User::find($id);
            $user->email = strtolower($request->email);
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->phone = $request->phone_number;

            if ($request->hasAny(['password', 'password_confirmation'])) {
                $request->validate([
                    'password' => [
                        'required', 'same:password_confirmation', 'string',
                        'min:'.config('custom.AUTHENTICATION.PASSWORD_MINIMUM_LENGTH'),
                        'regex:'.config('custom.AUTHENTICATION.PASSWORD_COMPLEXITY_REGEX'),
                    ], 'password_confirmation' => ['required'],
                ]);
                $user->password = bcrypt(trim($request->password));
            }

            $usr = $user->save();
            if ($usr) {
                return self::success('User', 'Updated user successfully', route('user.index'));
            }

            return self::failed('User', 'Failed to update user', route('user.index'));
        } else {
            return self::failed('profile', $validateddata, route('profile'));
        }
    }

    public function activate(Request $request, $id): array|RedirectResponse
    {

        $user = User::find($id);
        $user->status = $request->status;
        $user->save();
        if ($user) {
            return self::success('User', 'Updated user status successfully', $request->location);
        }

        return self::failed('User', 'Failed to update user status', $request->location);

    }

    private function syncUserRole(User $user, ?string $roleId): void
    {
        if ($roleId === null || $roleId === '') {
            $user->syncRoles([]);

            return;
        }

        $role = Role::query()->find($roleId);

        if ($role !== null) {
            $user->syncRoles([$role]);
        }
    }
}
