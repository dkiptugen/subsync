<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAppKey
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
         * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
         */
        /**
         * Handle an incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @return mixed
         */
        public function handle($request, Closure $next)
            {
                $appkey = $request->header('appkey');

                if(is_null($appkey))
                    {
                        return response()->json(['status' => false,'error' => "Unauthorized action"], 401);
                    }
                else
                    {
                        $key = env('API_KEY');
                        if($key !== $appkey)
                            return response()->json(['status' => false,'error' => "Invalid token"], 401);
                    }

                return $next($request);
            }
    }
