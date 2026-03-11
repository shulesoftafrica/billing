<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\ApiKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationApiKeyController extends Controller
{
    protected ApiKeyService $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    /**
     * Display a listing of API keys for the authenticated organization.
     */
    public function index(Request $request)
    {
        // Organization is already injected by middleware
        $organization = $request->attributes->get('organization');

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found in request',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'environment' => 'nullable|in:test,live',
            'status' => 'nullable|in:active,revoked',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $organization->apiKeys();

        if ($request->has('environment')) {
            $query->where('environment', $request->environment);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $apiKeys = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'API keys retrieved successfully',
            'data' => $apiKeys->map(function ($key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'key_prefix' => $key->key_prefix,
                    'environment' => $key->environment,
                    'status' => $key->status,
                    'last_used_at' => $key->last_used_at?->toISOString(),
                    'expires_at' => $key->expires_at?->toISOString(),
                    'created_at' => $key->created_at->toISOString(),
                ];
            })
        ], 200);
    }

    /**
     * Store a newly created API key.
     * 
     * IMPORTANT: The plain text API key is only returned in this response once.
     * Make sure to save it securely - you won't be able to see it again!
     */
    public function store(Request $request)
    {
        // Organization is already injected by middleware
        $organization = $request->attributes->get('organization');

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found in request',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'environment' => 'required|in:test,live',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate the API key
        $result = $this->apiKeyService->generateKey(
            $organization,
            $request->environment,
            $request->name
        );

        // Set optional expiration if provided
        if ($request->has('expires_at')) {
            $result['model']->expires_at = $request->expires_at;
            $result['model']->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'API key created successfully',
            'warning' => 'Save this key securely - you will not be able to see it again!',
            'data' => [
                'id' => $result['model']->id,
                'name' => $result['model']->name,
                'api_key' => $result['key'], // Only shown once!
                'key_prefix' => $result['model']->key_prefix,
                'environment' => $result['model']->environment,
                'status' => $result['model']->status,
                'expires_at' => $result['model']->expires_at?->toISOString(),
                'created_at' => $result['model']->created_at->toISOString(),
            ]
        ], 201);
    }

    /**
     * Display the specified API key.
     * Note: The plain text key is NEVER returned after creation.
     */
    public function show(Request $request, string $id)
    {
        // Organization is already injected by middleware
        $organization = $request->attributes->get('organization');

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found in request',
            ], 400);
        }

        $apiKey = $organization->apiKeys()->find($id);

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'API key retrieved successfully',
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key_prefix' => $apiKey->key_prefix,
                'environment' => $apiKey->environment,
                'status' => $apiKey->status,
                'last_used_at' => $apiKey->last_used_at?->toISOString(),
                'expires_at' => $apiKey->expires_at?->toISOString(),
                'created_at' => $apiKey->created_at->toISOString(),
                'updated_at' => $apiKey->updated_at->toISOString(),
            ]
        ], 200);
    }

    /**
     * Update the specified API key (name only).
     */
    public function update(Request $request, string $id)
    {
        // Organization is already injected by middleware
        $organization = $request->attributes->get('organization');

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found in request',
            ], 400);
        }

        $apiKey = $organization->apiKeys()->find($id);

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $apiKey->name = $request->name;
        $apiKey->save();

        return response()->json([
            'success' => true,
            'message' => 'API key updated successfully',
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key_prefix' => $apiKey->key_prefix,
                'environment' => $apiKey->environment,
                'status' => $apiKey->status,
            ]
        ], 200);
    }

    /**
     * Revoke the specified API key.
     */
    public function destroy(Request $request, string $id)
    {
        // Organization is already injected by middleware
        $organization = $request->attributes->get('organization');

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found in request',
            ], 400);
        }

        $apiKey = $organization->apiKeys()->find($id);

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key not found',
            ], 404);
        }

        if ($apiKey->status === 'revoked') {
            return response()->json([
                'success' => false,
                'message' => 'API key is already revoked',
            ], 400);
        }

        $apiKey->revoke();

        return response()->json([
            'success' => true,
            'message' => 'API key revoked successfully',
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key_prefix' => $apiKey->key_prefix,
                'status' => $apiKey->status,
            ]
        ], 200);
    }
}
