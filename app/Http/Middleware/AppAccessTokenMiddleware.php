<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppAccessTokenMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = env('APP_ACCESS_TOKEN');

        if (empty($expectedToken)) {
            return response()->json([
                'message' => 'Access Token is required.',
                'error' => 'auth_token_not_configured',
            ], 500);
        }

        $providedToken = $request->bearerToken();

        if (!is_string($providedToken) || !hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'invalid_access_token',
            ], 401);
        }

        return $next($request);
    }
}
