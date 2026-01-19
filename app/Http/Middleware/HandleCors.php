<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get allowed origins from config
        $allowedOrigins = array_filter(config('cors.allowed_origins', []));
        $origin = $request->headers->get('origin');

        // Only proceed if origin is explicitly allowed
        $isAllowedOrigin = !empty($allowedOrigins) && in_array($origin, $allowedOrigins, true);

        // Handle OPTIONS preflight requests
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($origin, $isAllowedOrigin);
        }

        // Process the actual request
        $response = $next($request);

        // Add CORS headers only for allowed origins
        if ($isAllowedOrigin) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-CSRF-Token, X-Requested-With');
            $response->header('Access-Control-Expose-Headers', 'X-Total-Count, X-Page-Count');
            $response->header('Access-Control-Max-Age', '3600');
        }

        return $response;
    }

    /**
     * Handle CORS preflight requests
     */
    private function handlePreflightRequest(?string $origin, bool $isAllowed): Response
    {
        if (!$isAllowed) {
            return response('', 403);
        }

        return response('', 200)
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-CSRF-Token, X-Requested-With')
            ->header('Access-Control-Max-Age', '3600');
    }
}
