<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationScope
{
    /**
     * Handle an incoming request.
     *
     * Ensures that any organization_id in the request matches the authenticated user's organization.
     * This prevents users from accessing data belonging to other organizations.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if no authenticated user
        if (!$request->user()) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'You must be authenticated to access this resource',
            ], 401);
        }

        $user = $request->user();
        $userOrgId = $user->organization_id;

        // Check if organization_id exists in route parameters
        if ($request->route('organization_id')) {
            if ((int) $request->route('organization_id') !== $userOrgId) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You do not have access to this organization\'s resources',
                ], 403);
            }
        }

        // Check if organization_id exists in request body (POST/PUT/PATCH)
        if ($request->has('organization_id')) {
            if ((int) $request->input('organization_id') !== $userOrgId) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You cannot create or modify resources for other organizations',
                ], 403);
            }
        }

        // Check if organization_id exists in query parameters (GET)
        if ($request->query('organization_id')) {
            if ((int) $request->query('organization_id') !== $userOrgId) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You cannot query resources from other organizations',
                ], 403);
            }
        }

        // Automatically inject organization_id for create/update operations if not present
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH']) && !$request->has('organization_id')) {
            $request->merge(['organization_id' => $userOrgId]);
        }

        return $next($request);
    }
}
