<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SocialLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
    {
        use SocialLogin;
        public function login_form()
            {
                return view('modules.front.login',$this->data);
            }
        public function login(Request $request)
            {
                $email = $request->email;
                $password = $request->password;
                $user = User::where('email', '=', $email)->first();
                if (!$user) {
                    return response()->json(['success'=>false, 'message' => 'Login Fail, please check email id']);
                }
                if(Hash::check($password, $user->password) || Hash::check('userimport', $user->password))
                    {
                        Session::flash('firegtm', "Successfully Logged In");
                        $token = $user->createToken('Subsync Password Grant Client')->plainTextToken;
                        Auth::setUser($user);
                        Auth::login($user);

                        Cookie::queue('authtoken', $token,43800);
                        if(Hash::check('userimport', $user->password))
                            {
                                Session::put('changepass',$user->id);
                                return redirect()->route('front.changepass',$user->id);
                            }
                        return redirect()->route('front.rates');
                    }
                if (!Hash::check($password, $user->password))
                    {
                        return response()->json(['success'=>false, 'message' => 'Login Fail, pls check password']);
                    }
            }
        public function pass_form($user)
            {
                if(Auth::user()->id == $user)
                    return view('modules.front.passform',$this->data);
            }
        public function register_form()
            {
                return view('modules.front.register',$this->data);
            }
        public function register(Request $request)
            {

            }
    }
