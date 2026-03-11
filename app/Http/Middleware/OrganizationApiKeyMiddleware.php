<?php

namespace App\Http\Middleware;

use App\Services\ApiKeyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizationApiKeyMiddleware
{
    protected ApiKeyService $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    /**
     * Handle an incoming request.
     *
     * This middleware validates the API key and injects the organization into the request.
     * After this middleware runs, controllers can access the organization via $request->organization
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the bearer token from the request
        $apiKey = $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'message' => 'API key is required',
                'error' => 'missing_api_key',
                'hint' => 'Provide your API key in the Authorization header as: Bearer org_live_...'
            ], 401);
        }

        // Validate the key format first (quick check before database lookup)
        if (!$this->apiKeyService->isValidKeyFormat($apiKey)) {
            return response()->json([
                'message' => 'Invalid API key format',
                'error' => 'invalid_api_key_format',
                'hint' => 'API key must be in format: org_test_... or org_live_...'
            ], 401);
        }

        // Validate the key and get the organization
        $organization = $this->apiKeyService->validateKey($apiKey);

        if (!$organization) {
            return response()->json([
                'message' => 'Invalid or expired API key',
                'error' => 'invalid_api_key',
            ], 401);
        }

        // Check organization status
        if ($organization->status !== 'active') {
            return response()->json([
                'message' => 'Organization is not active',
                'error' => 'organization_inactive',
            ], 403);
        }

        // Inject organization into request for controller access
        $request->merge(['organization' => $organization]);
        
        // Also set as attribute for better type hinting
        $request->attributes->set('organization', $organization);

        return $next($request);
    }
}
