<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordExpiredRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ExpiredPasswordController extends Controller
    {
        public function expired()
            {
                $this->data['title'] = 'Reset Expired Password';
                return view('modules.auth.passwords.expired',$this->data);
            }
        public function postExpired(PasswordExpiredRequest $request)
            {
                // Checking current password
                if (!Hash::check($request->current_password, $request->user()->password))
                    {
                        return redirect()->back()->withErrors(['current_password' => 'Current password is not correct']);
                    }
                $validate = $request->validate([
                    'password' => [
                                        'required',
                                        'string',
                                        'min:'.config('custom.AUTHENTICATION.PASSWORD_MINIMUM_LENGTH'),
                                        'confirmed',
                                        'regex:'.config('custom.AUTHENTICATION.PASSWORD_COMPLEXITY_REGEX')
                                   ],
                ]);
                if($validate)
                    {
                        $request->user()->update([
                                                    'password' => bcrypt($request->password),
                                                    'password_changed_at' => Carbon::now()->toDateTimeString()
                                                ]);
                        return self::success('Password Change', 'password changed successfully',route('login'));
                    }
                else
                    {
                        return self::failed('Password Change',$validate->errors(),route('password.expired'));
                    }

            }
    }
