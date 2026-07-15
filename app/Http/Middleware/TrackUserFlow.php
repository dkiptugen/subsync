<?php

namespace App\Http\Middleware;

use App\Models\UserFlowEvent;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackUserFlow
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->track($request, $response);

        return $response;
    }

    private function track(Request $request, Response $response): void
    {
        try {
            UserFlowEvent::create([
                'user_id' => $request->user()?->id,
                'product_id' => $request->integer('product') ?: null,
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'event' => $this->eventName($request, $response),
                'path' => '/'.ltrim($request->path(), '/'),
                'method' => $request->method(),
                'route_name' => $request->route()?->getName(),
                'metadata' => [
                    'status_code' => $response->getStatusCode(),
                    'query' => $request->query(),
                    'referer' => $request->headers->get('referer'),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'occurred_at' => Carbon::now(),
            ]);
        } catch (Throwable $exception) {
            Log::debug('User flow tracking skipped.', [
                'error' => $exception->getMessage(),
                'path' => $request->path(),
            ]);
        }
    }

    private function eventName(Request $request, Response $response): string
    {
        if ($response instanceof RedirectResponse) {
            return 'redirect';
        }

        if ($request->isMethod('post')) {
            return 'submit';
        }

        return 'page_view';
    }
}
