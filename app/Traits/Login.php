<?php


namespace App\Traits;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;


trait Login
    {





        public static function register(Request $request)
            {
                $name = $request->name;
                $email = $request->email;
                $password = $request->password;
                $password_confirmation = $request->password_confirmation;
                $phone = $request->phone;
                $url = $request->url;

                $user = new User();

                $params = [
                    "body" => json_encode([
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'password_confirmation' => $password_confirmation,
                        "app_id" => 11,
                        "app_secret" => "8VmFjN3A"
                    ])
                ];
                $client = new Client([
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'verify' => base_path('/cacert.pem'),
                    'http_errors' => false
                ]);
                try
                    {
                        $response = $client->request('POST', self::$api . 'register', $params);
                        // return $response;
                    }
                catch (\Exception $e)
                    {
                        return $e->getMessage();
                    }
                $headers = $response->getHeaders();
                $body = $response->getBody()->getContents();
                $objbody = json_decode($body);
                if ($response->getStatusCode() >= 500)
                    {
                        return array('status' => FALSE, 'msg' => 'Server Error', 'header' => 'Account Registration');
                    }
                if (property_exists($objbody, 'message') && ((int)$response->getStatusCode()) > 250)
                    {
                        return array('status' => FALSE, 'msg' => $objbody->message . 'Login into your account or reset your password if you have forgotten it.', 'header' => 'Account Registration');
                    }
                $user->id = $objbody->id;
                $user->email = $objbody->email;
                $user->name = $objbody->name;
                $user->phone = $phone;
                $existing = $user->find($objbody->id);

                if (is_null($existing))
                    {
                        $user->save();
                        self::login($request);
                    }
                return redirect('/');
                //return array('status'=>TRUE,'msg'=>"Registration successful",'header'=>'Account Registration');

            }

        public static function resetPassword(Request $request)
            {
                $email = $request->email;
                $url = url('/');
                $params = [
                    "body" => json_encode([
                        'email' => $email,
                        'redirect_url' => $url,
                        "app_id" => 11,
                        "app_secret" => "8VmFjN3A"
                    ])
                ];
                $client = new Client(['headers' => ['Content-Type' => 'application/json'], 'verify' => base_path('/cacert.pem'), 'http_errors' => false]);
                try
                    {
                        $response = $client->request('POST', self::$api . 'email/password', $params);
                    }
                catch (\Exception $e)
                    {

                    }

                $headers = $response->getHeaders();
                $body = $response->getBody()->getContents();
                $objbody = json_decode($body);

                if (property_exists($objbody, 'message'))
                    {
                        $request->session()->flash('resetmsg', $objbody->message);
                        return array('status' => TRUE, 'msg' => "Reset password link has been sent to your email address", 'header' => 'Forgot Password');

                    }
                return redirect('/');
                //return array('status'=>TRUE,'msg'=>"Reset password link has been sent to your email address",'header'=>'Forgot Password');

            }

        public static function logout()
            {
                Auth::logout();
                return redirect(URL::previous());
            }

        public function redirectToGoogle()
            {
                return Socialite::driver('google')->redirect();
            }


        public function handleGoogleCallback()
            {
                $googleuser = Socialite::driver('google')->stateless()->user();
                $id = $googleuser->getId();
                $name = $googleuser->getName();
                $email = $googleuser->getEmail();

                if (is_null($email))
                    {
                        $email = $id;
                    }

                $params = ["body" => json_encode(['username' => $email, 'name' => $name, 'provider' => 'google', "app_id" => 11, "app_secret" => "8VmFjN3A"])];
                $response = null;
                $client = new Client(['headers' => ['Content-Type' => 'application/json'], 'verify' => base_path('/cacert.pem'), 'http_errors' => false]);

                try
                    {
                        $response = $client->request('POST', 'https://vas.standardmedia.co.ke/api/social/login', $params);
                    }
                catch (\Exception $e)
                    {
                        Log::error($e->getMessage());
                    }

                $headers = $response->getHeaders();
                $body = $response->getBody()->getContents();
                $objbody = json_decode($body);

                $user = new User();
                $user->id = $objbody->id;
                $user->email = $objbody->email;
                $user->name = $objbody->name;
                $existing = $user->find($objbody->id);

                if (is_null($existing))
                    {
                        $user->save();
                    }

                Auth::setUser($user);
                Auth::login($user);
                Session::flash('firegtm', "Successfully Logged In");

                return redirect('/');
            }

        public function handleGoogleOneTap(Request $request)
            {

                $token = $request->credential;
                $tokenParts = explode(".", $token);
                $tokenHeader = base64_decode($tokenParts[0]);
                $tokenPayload = base64_decode($tokenParts[1]);
                $tokenSecret = base64_decode($tokenParts[2]);
                $jwtHeader = json_decode($tokenHeader);
                $googleuser = json_decode($tokenPayload);

                //dd($jwtHeader);

                $id = $googleuser->sub;
                $name = $googleuser->name;
                $email = $googleuser->email;

                if (is_null($email))
                    {
                        $email = $id;
                    }

                $params = ["body" => json_encode(['username' => $email, 'name' => $name, 'provider' => 'google-one-tap', "app_id" => 11, "app_secret" => "8VmFjN3A"])];
                $response = null;
                $client = new Client(['headers' => ['Content-Type' => 'application/json'], 'verify' => base_path('/cacert.pem'), 'http_errors' => false]);

                try
                    {
                        $response = $client->request('POST', 'https://vas.standardmedia.co.ke/api/social/login', $params);
                    }
                catch (\Exception $e)
                    {
                        Log::error($e->getMessage());
                    }

                $headers = $response->getHeaders();
                $body = $response->getBody()->getContents();
                $objbody = json_decode($body);

                $user = new User(
                    [
                        "id" => $objbody->id,
                        "name" => $objbody->name,
                        "email" => $objbody->email,
                    ]
                );

                $existing = User::find($objbody->id);
                if (is_null($existing))
                    {
                        $user->save();
                    }

                Session::flash('firegtm', "Successfully Logged In");
                Auth::setUser($user);
                Auth::login($user);

                return redirect()->back();
            }

        public function redirectToFacebook()
            {
                Session::put('formerurl', str_replace('#_=_', '', url()->previous()));

                return Socialite::driver('facebook')->redirect();
            }

        public function handleFacebookCallback()
            {
                $facebookuser = Socialite::driver('facebook')->stateless()->user();
                $id = $facebookuser->getId();
                $name = $facebookuser->getName();
                $email = $facebookuser->getEmail();

                if (is_null($email))
                    {
                        $email = $id;
                    }

                $params = ["body" => json_encode(['username' => $email, 'name' => $name, 'provider' => 'facebook', "app_id" => 3, "app_secret" => "YNHhP7eAxr6eKZm3"])];
                $response = null;
                $client = new Client(['headers' => ['Content-Type' => 'application/json'], 'verify' => base_path('/cacert.pem'), 'http_errors' => false]);

                try
                    {
                        $response = $client->request('POST', 'https://vas.standardmedia.co.ke/api/social/login', $params);
                    }
                catch (\Exception $e)
                    {

                    }

                $headers = $response->getHeaders();
                $body = $response->getBody()->getContents();
                $objbody = json_decode($body);
                $user = new User();
                $user->id = $objbody->id;
                $user->email = $objbody->email;
                $user->name = $objbody->name;

                $existing = $user->find($objbody->id);

                if (is_null($existing))
                    {
                        $user->save();
                    }

                Auth::login($user);

                $url = Session::get('formerurl');
                Session::flash('loginnotify', "Successfully Logged In");
                return redirect($url);
            }


}
