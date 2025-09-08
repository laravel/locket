<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get route name or fallback to URI
        $uri = $request->getRequestUri();

        // Get user agent
        $userAgent = $request->userAgent() ?? 'Unknown';

        // Get HTTP method
        $method = $request->getMethod();

        // Log the request details
        Log::debug('HTTP Request', [
            'uri' => $uri,
            'method' => $method,
            'user_agent' => $userAgent,
            'headers' => $request->headers->all(),
        ]);

        return $next($request);
    }
}
