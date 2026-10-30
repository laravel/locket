<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateMcpOrigin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('mcp') && $request->headers->has('Origin')) {
            // Use trusted configuration, not Host, to prevent DNS rebinding.
            abort_unless($request->header('Origin') === rtrim(config('app.url'), '/'), 403);
        }

        return $next($request);
    }
}
