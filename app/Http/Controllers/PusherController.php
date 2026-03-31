<?php
	
	namespace App\Http\Controllers;
	
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	
	class PusherController extends Controller
		{
			public function authenticate(Request $request)
				{
					return Auth::user();
				}
		}
