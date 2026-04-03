<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\Meta;
use App\Traits\SocialLogin;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use Meta;
    use SocialLogin;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/manage';

    protected $data = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->data = self::site_def();
        $this->data['title'] = 'Login';
    }

    public function showLoginForm()
    {
        return view('auth.login', $this->data);
    }

    public function authenticated(Request $request, $user)
    {

        $user->last_login = Carbon::now()->toDateTimeString();
        $user->save();
        $user->meta()->create(['action' => 'login',
            'result' => Carbon::now()->format('Y-m-d H:i:s'),
            'source' => 'dashboard',
            'ip_address' => $request->ip(),
        ]);
        if ($user->type == 'organization') {
            return redirect('/b2b');
        }
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function validateLogin(Request $request)
    {
        $login = $request->input('email');
        $login_type = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $request->merge([$login_type => $login, 'status' => 1]);
        if ($login_type == 'email') {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:5',
            ]);
            $this->username = $login_type;

        } else {
            unset($request->email);
            $request->validate([
                'username' => 'required',
                'password' => 'required|min:5',
            ]);
            $this->username = $login_type;
        }
    }

    protected function attemptLogin(Request $request)
    {

        //                return $this->guard()->attempt(
        //                    $this->credentials($request), $request->boolean('remember')
        //                );
        $login = $request->input('email');
        $login_type = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($login_type, $login)
            ->where('status', 1)
            ->first();

        if (! $user) {
            return false;
        }

        // 🚫 Block customer type
        if ($user->type === 'customer') {
            return false;
        }

        return Hash::check($request->password, $user->password)
            ? $this->guard()->attempt($this->credentials($request), $request->boolean('remember'))
            : false;
    }

    protected function guard()
    {

        return Auth::guard();

    }

    protected function credentials(Request $request)
    {
        $login = $request->input('email');
        $login_type = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return $request->only($login_type, 'password', 'status');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (is_object($user)) {
            if ($user->type === 'customer') {
                throw ValidationException::withMessages([
                    $this->username() => ['You are not authorized to access the admin dashboard.'],
                ]);
            }

            if (Hash::check($request->password, $user->password)) {
                if ($user->status != 1) {
                    throw ValidationException::withMessages([
                        $this->username() => ['Account is inactive , Kindly contact the Administrator'],
                    ]);
                }

            } else {

                throw ValidationException::withMessages([
                    $this->username() => [trans('auth.failed'), $request->email],
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.failed'), $request->email],
            ]);
        }
    }

    public function logout(Request $request)
    {
        User::find(Auth::user()->id)->meta()->create(['action' => 'logout', 'result' => Carbon::now()->format('Y-m-d H:i:s')]);
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect($this->redirectTo);
    }

    public function reset_form($token)
    {
        $this->data['data'] = User::whereRememberToken($token)->first();

        return view('modules.auth.reset', $this->data);
    }
}
