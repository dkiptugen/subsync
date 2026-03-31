<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class RedirectIfNotInstalled
        {
        /**
         * Handle an incoming request.
         *
         * @param Closure(Request): (Response) $next
         */
            public function handle(Request $request, Closure $next)
            : Response
                {
                    if (!file_exists(storage_path('installed'))
                        && !$request->is('install*'))
                        {

                            return redirect('/install');
                        }

                    return $next($request);

                }
        }
