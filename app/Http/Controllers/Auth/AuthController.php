<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'organization_email' => 'required|email|exists:organizations,email',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:admin,user,manager',
            'sex' => 'nullable|string|in:male,female,other,m,f,o,M,F,O',
        ]);

        // Find organization by email
        $organization = \App\Models\Organization::where('email', $validated['organization_email'])->firstOrFail();

        // Check for unique email within organization
        $existingUser = User::where('organization_id', $organization->id)
                            ->where('email', $validated['email'])
                            ->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => ['A user with this email already exists in this organization.'],
            ]);
        }

        $user = User::create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'user',
            'sex' => strtoupper(substr($validated['sex'] ?? 'O', 0, 1)), // Convert to single uppercase char (M/F/O)
        ]);

        // Get token expiration from config (30 days default for user tokens)
        $expirationMinutes = config('sanctum.user_token_expiration', 43200);
        $expiresAt = now()->addMinutes($expirationMinutes);

        // Create token with expiration
        $token = $user->createToken(
            'auth_token',
            ['*'],
            $expiresAt
        )->plainTextToken;

        // Log IP and User Agent
        $this->logTokenAudit($user, $request);

        return response()->json([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expirationMinutes * 60, // Convert to seconds
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'organization_id' => $user->organization_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Token Rotation: Revoke all previous tokens for security
        $user->tokens()->delete();

        // Get token expiration from config (30 days default for user tokens)
        $expirationMinutes = config('sanctum.user_token_expiration', 43200);
        $expiresAt = now()->addMinutes($expirationMinutes);

        // Create token with expiration
        $token = $user->createToken(
            'auth_token',
            ['*'],
            $expiresAt
        )->plainTextToken;

        // Log IP and User Agent
        $this->logTokenAudit($user, $request);

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expirationMinutes * 60, // Convert to seconds
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'organization_id' => $user->organization_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 200);
    }

    /**
     * Logout user (revoke current token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if (!$request->user() || !$request->user()->currentAccessToken()) {
            return response()->json([
                'message' => 'Not authenticated.',
            ], 401);
        }

        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Logout from all devices (revoke all tokens)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAll(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'No authenticated user found for logout-all.',
            ], 401);
        }

        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully',
        ], 200);
    }

    /**
     * Get authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'No authenticated user context available.',
                'user' => null,
            ], 401);
        }

        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    /**
     * Log token audit information (IP address and user agent)
     *
     * @param User $user
     * @param Request $request
     * @return void
     */
    protected function logTokenAudit(User $user, Request $request)
    {
        // Update the most recent token with IP and User Agent
        $latestToken = $user->tokens()->latest('id')->first();

        if ($latestToken) {
            $latestToken->update([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }

    /**
     * Generate a new personal access token for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string',
        ]);

        $user = $request->user();
        
        // Calculate expiration
        $expirationDays = $validated['expires_in_days'] ?? 30;
        $expiresAt = now()->addDays($expirationDays);
        
        // Create token
        $tokenResult = $user->createToken(
            $validated['name'],
            $validated['abilities'] ?? ['*'],
            $expiresAt
        );

        // Update with audit info
        $tokenResult->accessToken->update([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Personal access token created successfully',
            'data' => [
                'token' => $tokenResult->plainTextToken,
                'token_id' => $tokenResult->accessToken->id,
                'name' => $tokenResult->accessToken->name,
                'expires_at' => $tokenResult->accessToken->expires_at->toIso8601String(),
                'abilities' => $tokenResult->accessToken->abilities,
            ],
            'warning' => 'Store this token securely. It will not be shown again.',
        ], 201);
    }

    /**
     * List all tokens for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listTokens(Request $request)
    {
        $user = $request->user();
        
        $tokens = $user->tokens()->latest('created_at')->get()->map(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'expires_at' => $token->expires_at?->toIso8601String(),
                'created_at' => $token->created_at->toIso8601String(),
                'is_expired' => $token->expires_at?->isPast() ?? false,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ], 200);
    }

    /**
     * Revoke a specific token by ID.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeToken(Request $request, int $id)
    {
        $user = $request->user();
        
        $token = $user->tokens()->where('id', $id)->first();

        if (!$token) {
            return response()->json([
                'error' => 'Token not found',
                'message' => 'The specified token does not exist or does not belong to you',
            ], 404);
        }

        $tokenName = $token->name;
        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully',
            'token_name' => $tokenName,
        ], 200);
    }
}
