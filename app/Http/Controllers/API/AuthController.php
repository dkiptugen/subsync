<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Livewire\UserWhitelist;
use App\Http\Services\EmailService;
use App\Jobs\Kafka\UserRegistrationEventJob;
use App\Models\PasswordReset;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\PasswordResetRequest;
use App\Notifications\UserVerificationNotification;
use App\Traits\SocialLogin;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use SocialLogin;
    use ResetsPasswords;

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     *
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => implode(',', $validator->messages()->all())
            ]);
        }
        $user = User::where('email', $request->email)->first();
        //dd($user);

        if ($user)
        {
            if($user->status == 0)
            {
                return response()->json([
                    'status' => false,
                    'error'  => 'User Account deactivated.'
                ]);
            }

            if (Hash::check($request->password, $user->password))
            {
                $token            = $user->createToken('Subsync Password Grant Client')->plainTextToken;
                $user->last_login = Carbon::now();
                $user->save();
                //Auth::logoutOtherDevices($request->password);
                $response = [
                    'token'      => $token,
                    'expires_in' => config('custom.CUSTOMER.TOKEN_EXPIRY') * 24 * 60 * 60,
                    'user'       => $user
                ];
                $user->meta()->insert([
                    'user_id'    => $user->id,
                    'action'     => 'Success Login',
                    'result'     => Carbon::now(),
                    'ip_address' => $request->ip(),
                    'source'     => $request->link ?? $request->getHost(),
                    'date_created' => Carbon::now()->format('Y-m-d')
                ]);
                return response()->json([
                    'status' => true,
                    'data'   => $response
                ]);
            }
            else
            {
                if (is_null($user->last_login))

                {
                    $user->meta()->insert([
                        'user_id'    => $user->id,
                        'action'     => 'Failed Login : first login after import',
                        'result'     => Carbon::now(),
                        'ip_address' => $request->ip(),
                        'source'     => $request->link ?? $request->getHost(),
                        'date_created' => Carbon::now()->format('Y-m-d')
                    ]);
                    return response()->json([
                        'status' => false,
                        'error'  => ' Use forgot password link below to create a new password.'
                    ]);
                }
                else
                {
                    $user->meta()->insert([
                        'user_id'    => $user->id,
                        'action'     => 'Failed Login : password mismatch',
                        'result'     => Carbon::now(),
                        'ip_address' => $request->ip(),
                        'source'     => $request->link ?? $request->getHost(),
                        'date_created' => Carbon::now()->format('Y-m-d')
                    ]);
                    return response()->json([
                        'status' => false,
                        'error'  => 'The password you entered was incorrect.Please try again or click Forgot password? to reset it'
                    ]);
                }

            }
        }
        else
        {

            return response()->json([
                'status' => false,
                'error'  => 'Email does not exist, click ‘register here’ below to create an account. '
            ]);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function auth_email(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!is_null($user))
        {
            $token    = $user->createToken('Subsync Password Grant Client')->plainTextToken;
            $response = [
                'token'      => $token,
                'expires_in' => config('custom.CUSTOMER.TOKEN_EXPIRY') * 24 * 60 * 60,
                'user'       => $user
            ];

            return response()->json([
                'status' => true,
                'data'   => $response
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function register(Request $request)
    {

        if (!request()->has('name') || request('name') == '')
        {
            return response()->json([
                'status' => false,
                'error'  => 'Name is required'
            ]);
        }

        if (!request()->has('email') || request('email') == '')
        {
            return response()->json([
                'status' => false,
                'error'  => 'Email is required'
            ]);
        }

        if (!request()->has('password') || request('password') == '')
        {
            return response()->json([
                'status' => false,
                'error'  => 'Password is required'
            ]);
        }

        if (request('password') != request('password_confirmation'))
        {
            return response()->json([
                'status' => false,
                'error'  => 'Passwords must match'
            ]);
        }


        $validator = Validator::make($request->all(),
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|confirmed',
                'allow_marketing' => 'sometimes|boolean',
            ],
            [
                'email.unique' => 'Email already exists, please Sign in.'
            ]
        );
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error'  => implode(',', $validator->messages()->all())
            ]);
        }
        $validated = $validator->validated();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'verification_token' => Str::ulid(),
            'status' => 1,
            'can_notify' => 1,
            'allow_marketing' => (int) ($validated['allow_marketing'] ?? false),
        ]);
        try
        {

            $kafka_data = [
                'user'=>$user,
                'ip_address' => $request->ip(),
                'link'=>$request->link ?? $request->getHost()
            ];
            UserRegistrationEventJob::dispatch($kafka_data);
        }
        catch (\Exception $e)
        {
            Log::error('Kafka User registration', [$e->getMessage()]);
        }
        $token                      = $user->createToken('authToken', ['*']);
        try
        {
            $this->verify_email((string)$user->email);
        }
        catch (Exception $exception)
        {
            Log::error($exception->getMessage());
        }

        try {
            $user->notify(new UserVerificationNotification($user, $request->link));
        }catch (\Exception $exception){
            report($exception);
        }

        $user->meta()->insert([
            'user_id'    => $user->id,
            'action'     => 'Successful registration',
            'result'     => Carbon::now(),
            'ip_address' => $request->ip(),
            'source'     => $request->link ?? $request->getHost(),
            'date_created' => Carbon::now()->format('Y-m-d')
        ]);
        $response = [
            'token'      => $token->plainTextToken,
            'expires_in' => config('custom.CUSTOMER.TOKEN_EXPIRY') * 24 * 60 * 60,
            'user'       => $user
        ];

        //event(new Registered($user));
        return response()->json([
            'status' => true,
            'data'   => $response
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function social_login(Request $request)
    {

        if (!request()->has('name') || request('name') == '')
        {
            return response()->json([
                'status' => false,
                'error'  => 'Name is required'
            ]);
        }

        if (!request()->has('email') || request('email') == '')
        {
            return response()->json([
                'status' => false,
                'error'  => 'Email is required'
            ]);
        }

        if (!request()->has('provider') || request('provider') == '')
        {
            return response()->json([
                'status' => false,
                'error'  => 'Provider is required'
            ]);
        }

        //return response()->json(['status' => false, 'error' => 'This endpoint is depreciated. Please use version 3']);
        $userCreated = User::where('email',$request->email)->first();

        if($userCreated && $userCreated->status == 0)
        {
            return response()->json([
                'status' => false,
                'error'  => 'User Account deactivated.'
            ]);
        }

        $userCreated = User::firstOrCreate(
            [
                'email' => $request->email
            ],
            [
                'email_verified_at' => now(),
                'name'              => $request->name,
                'status'            => true,
            ]
        );
        $userCreated->providers()->updateOrCreate(
            [
                'provider'    => $request->provider,
                'provider_id' => $request->id,
            ],
            [
                'avatar' => $request->avatar
            ]
        );

        $token = $userCreated->createToken('token-name')->plainTextToken;
        $userCreated->meta()->insert([
            'user_id'    => $userCreated->id,
            'action'     => 'Successful social login : ' . $request->provider,
            'result'     => Carbon::now(),
            'ip_address' => $request->ip(),
            'source'     => $request->link ?? $request->getHost(),
            'date_created' => Carbon::now()->format('Y-m-d')
        ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'token'      => $token,
                'expires_in' => config('custom.CUSTOMER.TOKEN_EXPIRY') * 24 * 60 * 60,
                'user'       => $userCreated
            ]
        ]);

    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function social_login_v2(Request $request)
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
            'provider' => ['required', 'in:google,facebook'],
        ]);

        try
        {
            $socialUser = Socialite::driver($validated['provider'])
                ->userFromToken($validated['access_token']);
            $email = $socialUser->getEmail();

            if (! $email) {
                return response()->json([
                    'status' => false,
                    'error' => 'The social account does not provide an email address.',
                ], 422);
            }

            $name = $socialUser->getName() ?: $socialUser->getNickname() ?: Str::before($email, '@');
            $provider = $validated['provider'];
            $id = $socialUser->getId();
            $avatar = $socialUser->getAvatar();

            $userCreated = User::firstOrCreate(
                [
                    'email' => $email
                ],
                [
                    'email_verified_at' => now(),
                    'name'              => $name,
                    'status'            => true,
                ]
            );
            $userCreated->providers()->updateOrCreate(
                [
                    'provider'    => $provider,
                    'provider_id' => $id,
                ],
                [
                    'avatar' => $avatar
                ]
            );

            $token = $userCreated->createToken('token-name')->plainTextToken;
            $userCreated->meta()->insert([
                'user_id'    => $userCreated->id,
                'action'     => 'Successful social login : ' . $request->provider,
                'result'     => Carbon::now(),
                'ip_address' => $request->ip(),
                'source'     => $request->link ?? $request->getHost(),
                'date_created' => Carbon::now()->format('Y-m-d')
            ]);


            return response()->json([
                'status' => true,
                'data'   => [
                    'token'      => $token,
                    'expires_in' => config('custom.CUSTOMER.TOKEN_EXPIRY') * 24 * 60 * 60,
                    'user'       => $userCreated
                ]
            ]);
        }
        catch (Exception $e)
        {
            report($e);

            return response()->json([
                'status' => false,
                'error' => 'The social authentication token could not be verified.',
            ], 401);
        }


    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function resetpassword(Request $request)
    {

        $user = User::query()->where('email', $request->email)->first();
        if (!$user)
        {
            return response()->json([
                'status' => false,
                'error'  => "The email address that you've entered doesn't match any account. Sign up for an account."
            ]);
        }

        if ((int)$user->status != 1)
        {
            return response()->json([
                'status' => false,
                'error'  => 'Sorry, Your account has been deactivated. Please contact Nation for assistance'
            ]);
        }

        //$user->password = Hash::make(Str::random(12));
        $check = PasswordReset::where(['email' => $user->email])
            ->first();
        if (!is_null($check))
        {


            if (Carbon::now()->diffInDays(Carbon::parse($check->created_at)) > 1)
            {

                PasswordReset::where('email', $user->email)
                    ->delete();
                $token = Str::ulid();
                $pr    = PasswordReset::create([
                    'email'      => $user->email,
                    'token'      => $token,
                    'created_at' => Carbon::now()->toDateTimeString()
                ]);
                if (!is_null($pr))
                {
                    $user->setRememberToken($token);
                    $user->save();
                }
            }
            else
            {
                $pr = $check;
            }
            //, 'expires_in' => config('custom.CUSTOMER.TOKEN_EXPIRY') * 24 * 60 * 60
        }
        else
        {
            $token = Str::ulid();
            $pr    = PasswordReset::create([
                'email'      => $user->email,
                'token'      => $token,
                'created_at' => Carbon::now()->toDateTimeString()
            ]);
            if (!is_null($pr))
            {
                $user->setRememberToken($token);
                $user->save();
            }
        }


        if ($request->channel == 'epaper')
        {
            $endpoint = $request->redirect_url . '/account/reset-password';
        }
        else
        {

            /* $parsed           = parse_url($request->redirect_url);
             $pathComponents   = explode('/', trim($parsed['path'], '/'));
             $desiredPathLevel = 1; // Change this to the desired directory level
             $path             = implode('/', array_slice($pathComponents, 0, $desiredPathLevel));

             $extractedUrl = $parsed['scheme'] ?? 'https' . '://' . $parsed['host'] . '/' . $path;
             $endpoint     = $extractedUrl . '/account/reset-password';*/
            $endpoint = $request->link . '/account/reset-password';
        }

        $user->meta()->insert([
            'user_id'    => $user->id,
            'action'     => 'Password reset request',
            'result'     => Carbon::now(),
            'ip_address' => $request->ip(),
            'source'     => $request->link ?? $request->getHost(),
            'date_created' => Carbon::now()->format('Y-m-d')
        ]);
        try{

            if($user->can_notify == 1)
                $user->notify(new PasswordResetRequest($user, $endpoint, $request->channel, $request->redirect_url, $pr->token, $pr->created_at));

        }catch (Exception $e){

            report($e);
        }

        foreach ($user->tokens as $token)
        {
            $token->delete();
        }

        return response()->json([
            'status' => true,
            'data'   => 'A password  email has been sent to ' . $user->email
        ]);
    }


    /**
     * @param Request $request
     *
     * @return false|string
     * @throws ValidationException
     */
    public function getUserByEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        abort_unless(
            $request->user()?->hasRole('Super Admin') || $request->user()?->email === $validated['email'],
            403
        );

        $user = User::where('email', $validated['email'])->first();
        if ($user == null)
        {
            return response()->json([
                'status' => false,
                'error'  => 'This user does not exist!'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $user
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {

        if (!Auth::check())
        {
            return response()->json([
                'status' => false,
                'error'  => 'Kindly login to access'
            ], 406);
        }
        $token = $request->user()->currentAccessToken();
        $request->user()->meta()->insert([
            'user_id'    => $request->user()->id,
            'action'     => 'Logout',
            'result'     => Carbon::now(),
            'ip_address' => $request->ip(),
            'source'     => $request->link ?? $request->getHost(),
            'date_created' => Carbon::now()->format('Y-m-d')
        ]);

        $token?->delete();
        $response = ['message' => 'You have been successfully logged out!'];

        return response()->json([
            'status' => true,
            'data'   => $response
        ]);
    }

    /**
     * @param Request $request
     *
     * @return false|string
     * @throws ValidationException
     */
    public function getUserById(Request $request)
    {

        if (!Auth::check())
        {
            return response()->json([
                'status' => false,
                'error'  => 'Kindly login to access'
            ], 400);
        }
        if (!request()->has('id') || request('id') == '' || !is_numeric(request('id')))
        {
            return response()->json([
                'status' => false,
                'error'  => 'numeric id is required'
            ], 400);
        }
        if ($request->id != Auth::user()->id)
        {
            return response()->json([
                'status' => false,
                'error'  => "You cannot check someone else' id"
            ], 400);
        }

        $user = User::find($request->id);
        if ($user == null)
        {
            return response()->json([
                'status' => false,
                'error'  => 'This user does not exist!'
            ], 404);
        }

        $transaction = Transaction::where('user_id', $user->id)
            ->whereRaw("phone REGEXP '^[0-9]+$'")
            ->orderby('id', 'desc')->limit(1)->first();

        $user->mpesa_number = !is_null($transaction) ? '0' . substr($transaction->phone, 3) : null;

        return response()->json([
            'status' => true,
            'data'   => $user
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getUser()
    {

        if (!Auth::check())
        {
            return response()->json([
                'status' => false,
                'error'  => 'Kindly login to access'
            ], 400);
        }
        if (Auth::check())
        {
            $userCreated = Auth::user();

            return response()->json([
                'status' => true,
                'data'   => [$userCreated]
            ]);
        }

        return response()->json([
            'status' => false,
            'error'  => 'user not logged in'
        ], 400);
    }

    /**
     * @param $productId
     *
     * @return JsonResponse|void
     */
    public function subscription($productId)
    {

        if (!Auth::check())
        {
            return response()->json([
                'status' => false,
                'error'  => 'Kindly login to access'
            ]);
        }
        if (Auth::check())
        {
            $whitelist = UserWhitelist::whereHas('whitelistable', function ($query)
            {

                return $query->whereHas('organization', function ($q)
                {

                    return $q->where('id', Auth::user()->organization_id);
                })
                    ->orWhereHas('user', function ($q)
                    {

                        return $q->where('id', Auth::user()->id);
                    });
            })
                ->where('product_id', $productId)
                ->where('startdate', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where('enddate', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                ->first();
            if ($whitelist)
            {
                return response()->json([
                    'status' => true,
                    'data'   => [
                        'subscription' => 'active',
                        'expirydate'   => $whitelist->enddate
                    ]
                ]);
            }
        }

    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function change_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password'         => 'required|string|min:6|confirmed',
            'current_password' => 'required|string'
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'error'  => implode(',', $validator->messages()->all())
            ]);
        }
        if (!Hash::check($request->current_password, $request->user()->password))
        {
            return response()->json([
                'status' => false,
                'error'  => 'Current password is not correct'
            ]);
        }
        if (Hash::check($request->password, $request->user()->password))
        {
            return response()->json([
                'status' => false,
                'error'  => 'New password should not  match with the old password'
            ]);
        }

        if ($request->password !== $request->password_confirmation)
        {
            return response()->json([
                'status' => false,
                'error'  => 'passwords are not matching'
            ]);
        }

        $request->user()->update([
            'password'            => bcrypt($request->password),
            'password_changed_at' => \Illuminate\Support\Carbon::now()->toDateTimeString()
        ]);

        return response()->json([
            'status' => true,
            'data'   => 'password changed successfully'
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function user_profile(Request $request)
    {

        if (!Auth::check())
        {
            return response()->json([
                'status' => false,
                'error'  => 'Kindly login to access'
            ]);
        }
        else
        {
            /*    $validator = Validator::make($request->all(), array(
                    //'email' => 'sometimes|required|email',
                    'phone' => 'sometimes|numeric',
                    'name' => 'sometimes|string',
                    'surname' => 'sometimes|string',
                ));
                if ($validator->fails())
                    {
                        return response()->json(['status' => false, 'error' => implode(',', $validator->messages()->all())]);
                    }*/
            $user = User::find(Auth::user()->id);
            if (is_null($user))
            {
                return response()->json([
                    'status' => false,
                    'error'  => 'This user does not exist!'
                ], 400);
            }
            else
            {
                /* if ($request->has('email'))
                     {
                         $user->email = $request->email;
                     }*/
                if ($request->has('phone'))
                {
                    $user->phone = $request->phone;
                }
                if ($request->has('name'))
                {
                    $user->name = $request->name;
                }
                if ($request->has('surname'))
                {
                    $user->surname = $request->surname;
                }
                if ($request->has('allow_marketing'))
                {
                    $user->allow_marketing = (int)$request->allow_marketing;
                }
                if ($request->has('can_notify'))
                {
                    $user->can_notify = (int)$request->can_notify;
                }
                $res = $user->save();
                if ($res)
                {
                    return response()->json([
                        'status' => true,
                        'data'   => $user->toArray()
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'data'   => $user->toArray()
            ]);
        }
    }

    /**
     * @param $token
     *
     * @return JsonResponse|void
     */
    public function email_verify($token)
    {

        try
        {
            $user = User::where('verification_token', $token)
                ->update([
                    'is_verified'        => 1,
                    'email_verified_at'  => Carbon::now()->toDateTimeString(),
                    'verification_count' => 2,
                    'verification_token' => null
                ]);
            if ($user)
            {
                return response()->json([
                    'status' => true,
                    'data'   => 'user verified successfully'
                ]);
            }

            return response()->json([
                'status' => false,
                'error'  => 'Invalid token provided'
            ]);
        }
        catch (Exception $e)
        {
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage()
            ]);
        }

    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|void
     */
    public function reset(Request $request)
    {

        $check = PasswordReset::where('token', $request->token)
            ->first();

        if (!is_null($check))
        {

            if (Carbon::now()->diffInDays(Carbon::parse($check->created_at)) < 100)
            {
                $validated = Validator::make($request->all(), [
                    'token'    => 'required',
                    'password' => [
                        'required',
                        'confirmed'
                    ]
                ]);
                if ($validated->fails())
                {
                    return response()->json([
                        'status' => false,
                        'error'  => implode(',', $validated->messages()->all())
                    ]);
                }
                try
                {
                    /*$user                      = User::where('email', $check->email)
                                                     ->first();*/
                    //dd($check->email);
                    $res = User::where('email', $check->email)
                        ->update([
                            'password'            => bcrypt($request->password),
                            'password_changed_at' => \Illuminate\Support\Carbon::now()->toDateTimeString()
                        ]);

                    if ($res)
                    {
                        //$res->meta()->insert(['user_id' => $res->id, 'action' => 'Password reset request', 'result' => Carbon::now(), 'ip_address' => $request->ip(), 'source' => $request->link ?? $request->getHost()]);
                        PasswordReset::where('token', $request->token)
                            ->delete();

                        return response()->json([
                            'status' => true,
                            'data'   => 'Password Changed successfully.'
                        ]);
                    }
                }
                catch (Exception $e)
                {
                    //dd($e->getMessage());
                    return response()->json([
                        'status' => false,
                        'error'  => $e->getMessage()
                    ]);
                }


            }
            else
            {
                return response()->json([
                    'status' => false,
                    'error'  => 'The email token expired.'
                ]);
            }

        }
        else
        {
            return response()->json([
                'status' => false,
                'error'  => 'Invalid tokens provided'
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function email_unsubscribe($id)
    {
        try
        {


            $user         = User::find($id);
            //Log::debug($user);
            $affectedRows = $user->update(['can_notify' => 0]);
            if ($affectedRows > 0)
            {
                $user->meta()->insert([
                    'user_id'    => $user->id,
                    'action'     => 'Email Unsubscribe',
                    'result'     => Carbon::now(),
                    'ip_address' => request()->ip(),
                    'source'     => request()->link ?? request()->getHost(),
                    'date_created' => Carbon::now()->format('Y-m-d')
                ]);
                //Log::info('user not unsubscribed');
            }
            return view('modules.front.success-unsub', $this->data);
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'data'   => 'Unsubscription error.'
            ]);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function email_subscribe()
    {
        try
        {
            $user         = User::find(Auth::user()->id);

            if(!$user){
                return response()->json(['status' => false, 'data' => 'User not found. Ensure bearer token is valid']);
            }

            $affectedRows = $user->update(['can_notify' => 1]);
            if ($affectedRows > 0)
            {
                $user->meta()->insert([
                    'user_id'    => $user->id,
                    'action'     => 'Email subscribe',
                    'result'     => Carbon::now(),
                    'ip_address' => request()->ip(),
                    'source'     => request()->link ?? request()->getHost(),
                    'date_created' => Carbon::now()->format('Y-m-d')
                ]);
                return response()->json([
                    'status' => true,
                    'data'   => 'Subscribed successfully.'
                ]);
            }
            return response()->json([
                'status' => true,
                'data'   => 'User already subscribed.'
            ]);
        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'data'   => 'email subscription failed'
            ]);
        }
    }

    public function email_reverify(Request $request)
    {
        try
        {
            $user = $request->user();

            if(!$user){
                return response()->json(['status' => false, 'data' => 'User not found. Ensure bearer token is valid']);
            }

            if($user->is_verified == 0)
            {
                if(is_null($user->verification_token))
                {
                    $user->verification_token = Str::ulid ();
                    $user->save();
                }
                $user->notify(new UserVerificationNotification($user, $request->link));
                return response()->json([
                    'status' => true,
                    'data'   => 'verification email sent successfully.'
                ]);
            }
            else
            {
                return response()->json([
                    'status' => false,
                    'data'   => 'User is already verified'
                ]);
            }


        }
        catch (\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'data'   => 'verification email not sent.'
            ]);
        }

    }

}
