<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OAuthClient;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClientCredentialsController extends Controller
{
    /**
     * Create a new OAuth client for an organization.
     * This endpoint is protected and requires user authentication.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createClient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'environment' => 'required|in:test,live',
            'allowed_scopes' => 'nullable|array',
            'allowed_scopes.*' => 'string',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // SECURITY: Always use the authenticated user's organization
        // Users can ONLY create OAuth clients for their own organization
        $user = $request->user();
   
        $organization = $user->organization;

        if (!$organization) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'User is not associated with any organization.',
            ], 400);
        }

        // Generate credentials
        $credentials = OAuthClient::generateCredentials($validated['environment']);

        // Create the client
        $client = OAuthClient::create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'client_id' => $credentials['client_id'],
            'client_secret_hash' => $credentials['client_secret_hash'],
            'client_secret_prefix' => $credentials['client_secret_prefix'],
            'environment' => $validated['environment'],
            'status' => 'active',
            'allowed_scopes' => $validated['allowed_scopes'] ?? ['*'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'OAuth client created successfully',
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'client_id' => $credentials['client_id'],
                'client_secret' => $credentials['client_secret'], // SHOW ONLY ONCE
                'environment' => $client->environment,
                'allowed_scopes' => $client->allowed_scopes,
                'expires_at' => $client->expires_at,
                'created_at' => $client->created_at,
            ],
            'warning' => 'Store the client_secret securely. It will not be shown again.',
        ], 201);
    }

    /**
     * Get an access token using client_id and client_secret (OAuth2 Client Credentials Grant).
     * This is the primary authentication method for API integrations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grant_type' => 'required|in:client_credentials',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'scope' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'The request is missing required parameters or contains invalid values',
                'errors' => $validator->errors(),
            ], 400);
        }

        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');
        $scope = $request->input('scope', '*');

        // Find client by client_id
        $client = OAuthClient::where('client_id', $clientId)->first();

        if (!$client) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed',
            ], 401);
        }

        // Verify client secret
        if (!$client->verifySecret($clientSecret)) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed',
            ], 401);
        }

        // Check if client is active
        if (!$client->isActive()) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Client is inactive, revoked, or expired',
            ], 401);
        }

        // Verify requested scope
        $requestedScopes = explode(' ', $scope);
        foreach ($requestedScopes as $requestedScope) {
            if (!$client->hasScope($requestedScope)) {
                return response()->json([
                    'error' => 'invalid_scope',
                    'error_description' => "The requested scope '{$requestedScope}' is not authorized for this client",
                ], 400);
            }
        }

        // Get or create a service user for this organization (for Sanctum token binding)
        $serviceUser = $this->getOrCreateServiceUser($client->organization);

        // Get token expiration from config
        $expirationMinutes = config('sanctum.client_token_expiration', 129600); // 90 days default
        $expiresAt = now()->addMinutes($expirationMinutes);

        // Create access token using Sanctum
        $token = $serviceUser->createToken(
            "client:{$client->name}",
            [$scope],
            $expiresAt
        )->plainTextToken;

        // Update client last used
        $client->updateLastUsed($request->ip());

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expirationMinutes * 60, // Convert to seconds
            'scope' => $scope,
            'organization_id' => $client->organization_id,
        ], 200);
    }

    /**
     * List all OAuth clients for the authenticated user's organization.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listClients(Request $request)
    {
        $user = $request->user();
        
        $clients = OAuthClient::where('organization_id', $user->organization_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'client_id' => $client->client_id,
                    'environment' => $client->environment,
                    'status' => $client->status,
                    'allowed_scopes' => $client->allowed_scopes,
                    'last_used_at' => $client->last_used_at,
                    'last_used_ip' => $client->last_used_ip,
                    'expires_at' => $client->expires_at,
                    'created_at' => $client->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $clients,
        ], 200);
    }

    /**
     * Revoke an OAuth client.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeClient(Request $request, int $id)
    {
        $user = $request->user();
        
        $client = OAuthClient::where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        $client->revoke();

        return response()->json([
            'message' => 'Client revoked successfully',
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'status' => $client->status,
            ],
        ], 200);
    }

    /**
     * Get or create a service user for OAuth client tokens.
     * This is needed because Sanctum tokens must be tied to a user model.
     *
     * @param Organization $organization
     * @return User
     */
    protected function getOrCreateServiceUser(Organization $organization): User
    {
        $serviceEmail = "service.api@org{$organization->id}.internal";

        $user = User::where('email', $serviceEmail)
            ->where('organization_id', $organization->id)
            ->first();

        if (!$user) {
            $user = User::create([
                'organization_id' => $organization->id,
                'name' => 'API Service Account',
                'email' => $serviceEmail,
                'password' => bcrypt(bin2hex(random_bytes(32))), // Random password (not used)
                'role' => 'admin', // Service account with full access (allowed roles: admin, finance, support)
                'sex' => 'O', // Service account (O = Other)
            ]);
        }

        return $user;
    }
}
