<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MultiAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Supports both Laravel Sanctum tokens and APP_ACCESS_TOKEN for backward compatibility.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'no_token_provided',
            ], 401);
        }

        // First, try APP_ACCESS_TOKEN (backward compatibility)
        $appAccessToken = env('APP_ACCESS_TOKEN');
        if (!empty($appAccessToken) && hash_equals($appAccessToken, $token)) {
            // Valid APP_ACCESS_TOKEN - allow request
            return $next($request);
        }

        // Second, try Sanctum authentication (user personal tokens)
        if ($request->user('sanctum')) {
            // Valid Sanctum token - user is authenticated
            return $next($request);
        }

        // If neither authentication method worked, return unauthorized
        return response()->json([
            'message' => 'Unauthenticated',
            'error' => 'invalid_access_token',
            'hint' => 'Provide either a valid user token (from /auth/login) or APP_ACCESS_TOKEN',
        ], 401);
    }
}
