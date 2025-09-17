<?php

declare(strict_types=1);

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
        $startTime = microtime(true);

        // Capture request details
        $requestData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'headers' => $this->getFilteredHeaders($request),
        ];

        // Capture POST body for non-GET requests
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $requestData['body'] = $this->getFilteredBody($request);
        }

        // Process the request
        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

        // Capture response details
        $responseData = [
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
        ];

        // Capture response body (be careful with large responses)
        $responseBody = $response->getContent();
        if (strlen($responseBody) <= 10000) { // Only log if response is under 10KB
            $responseData['body'] = $responseBody;
        } else {
            $responseData['body'] = '[Response too large to log - '.strlen($responseBody).' bytes]';
        }

        // Log the complete request/response
        Log::info('HTTP Request/Response', [
            'request' => $requestData,
            'response' => $responseData,
        ]);

        return $response;
    }

    /**
     * Get filtered headers, excluding sensitive information
     */
    private function getFilteredHeaders(Request $request): array
    {
        $headers = $request->headers->all();

        // Remove sensitive headers
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];

        foreach ($sensitiveHeaders as $header) {
            unset($headers[$header]);
        }

        return $headers;
    }

    /**
     * Get filtered request body, excluding sensitive fields
     */
    private function getFilteredBody(Request $request): array
    {
        $body = $request->all();

        // Remove sensitive fields
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key', 'secret'];

        foreach ($sensitiveFields as $field) {
            unset($body[$field]);
        }

        return $body;
    }
}
