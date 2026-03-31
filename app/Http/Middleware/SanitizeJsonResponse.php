<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SanitizeJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Only run for Livewire AJAX calls
        if ($request->is('*get') || $request->is('livewire/*')) {

            $content = $response->getContent();
            // Find first '{' and last '}'
            $start = strpos($content, '{');
            $end   = strrpos($content, '}');

            if ($start !== false && $end !== false && $end > $start) {
                $clean = substr($content, $start, $end - $start + 1);

                // Validate JSON before replacing
                json_decode($clean);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response->setContent($clean);
                }
            }
        }

        return $response;
    }
}
