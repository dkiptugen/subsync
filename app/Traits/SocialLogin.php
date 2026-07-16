<?php


namespace App\Traits;


use App\Mail\LoginNotification;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Grosv\LaravelPasswordlessLogin\LoginUrl;

trait SocialLogin
    {
        /**
         * @var string
         */
        public $access = 'web';

        /**
         * @param $provider
         * @return JsonResponse
         */
        public function redirectToProvider($provider)
            {
                $validated = $this->validateProvider($provider);
                if (!is_null($validated))
                    {
                        return $validated;
                    }

                return Socialite::driver($provider)->stateless()->redirect();
            }

        /**
         * Obtain the user information from Provider.
         *
         * @param $provider
         * @return \Illuminate\Contracts\Foundation\Application|JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
         */
        public function handleProviderCallback($provider)
            {
                $validated = $this->validateProvider($provider);
                if (!is_null($validated))
                    {
                        return $validated;
                    }
                try
                    {
                        //dd(Socialite::driver($provider));
                        $user = Socialite::driver($provider)->stateless()->user();
                    }
                catch (ClientException $exception)
                    {
                        return response()->json(['error' => 'Invalid credentials provided.'], 422);
                    }

                $userCreated = User::firstOrCreate(
                                                        [
                                                            'email' => $user->getEmail()
                                                        ],
                                                        [
                                                            'email_verified_at' => now(),
                                                            'name' => $user->getName(),
                                                            'status' => true,
                                                            'password' => bcrypt(Str::random(12))
                                                        ]
                                                    );
                $userCreated->providers()->updateOrCreate(
                                                            [
                                                                'provider' => $provider,
                                                                'provider_id' => $user->getId(),
                                                            ],
                                                            [
                                                                'avatar' => $user->getAvatar()
                                                            ]
                                                        );
                if($this->sendFailedLogin($userCreated))
                    {
                        if($this->access = 'web')
                            {

                                Auth::setUser($userCreated);
                                Auth::login($userCreated);
                                $token = $userCreated->createToken('token-name')->plainTextToken;
                                return response()->json(['token' => $token,'user' => $userCreated]);

                            }
                        else
                            {
                                $token = $userCreated->createToken('token-name')->plainTextToken;
                                return response()->json(['token' => $token,'user'=>$userCreated]);
                            }
                    }


            }
        protected function sendFailedLogin($user)
            {


                if($user->status != 1)
                    {
                        throw ValidationException::withMessages([
                            'email' => ['Account is inactive , Kindly contact the Administrator'],
                        ]);
                    }
                return true;


            }
        /**
         * @param $provider
         * @return JsonResponse
         */
        protected function validateProvider($provider)
            {
                if (!in_array($provider, ['facebook', 'twitter', 'google']))
                    {
                        return response()->json(['error' => 'Please login using facebook, twitter or google'], 422);
                    }
            }

        /**
         * @param $provider
         * @return JsonResponse|void
         */
        public function deleteProviderCallback($provider)
            {
                $validated = $this->validateProvider($provider);
                if (!is_null($validated))
                    {
                        return $validated;
                    }
            }
        function sendLoginLink(Request $request,$redirect)
            {
                $user = User::where("email",$request->email)
                            ->orWhere('username',$request->username)
                            ->first();
                if(!is_null($user))
                    {
                        $generator = new LoginUrl($user);
                        $generator->setRedirectUrl($redirect); // Override the default url to redirect to after login
                        $url = $generator->generate();
                        $mail               =   new \StdClass();
                        $mail->login_link   =   $url;
                        $mail->name         =   $user->name;
                        Mail::to($user->email)->send(new LoginNotification($mail));
                    }




                // Send $url in an email or text message to your user
            }
    }
