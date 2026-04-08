<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'organization_link' => 'nullable|string|max:500',
            'organization_email' => 'nullable|email|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:admin,user,manager',
            'sex' => 'nullable|string|in:male,female,other,m,f,o,M,F,O',
        ]);

        if (empty($validated['organization_id']) && empty($validated['organization_link']) && empty($validated['organization_email'])) {
            return response()->json([
                'message' => 'organization_id, organization_email or organization_link is required.',
            ], 422);
        }

        $organization = $this->resolveOrganizationFromInput($validated);

        if (!$organization) {
            return response()->json([
                'message' => 'Organization not found from provided organization_id/email/link.',
            ], 404);
        }

        // Ensure organization is active
        if ($organization->status !== 'active') {
            return response()->json([
                'message' => 'Organization is not active. Current status: ' . $organization->status . '. Please contact support to activate your organization.',
            ], 403);
        }

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
            'status' => 'pending',
        ]);

        $this->sendDeveloperApprovalEmail($organization, $user);

        return response()->json([
            'message' => 'Registration submitted. The organization has been emailed to verify this developer request.',
            'status' => 'pending_organization_approval',
            'user' => [
                'id' => $user->id,
                'organization_id' => $user->organization_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ], 202);
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

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'message' => 'Account pending approval. Please ask your organization to verify your developer request.',
            ], 403);
        }

        $user->loadMissing('organization');
        if (!$user->organization || $user->organization->status !== 'active') {
            return response()->json([
                'message' => 'Organization is not active. Access denied.',
            ], 403);
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
                'status' => $user->status,
            ],
        ], 200);
    }

    private function resolveOrganizationFromInput(array $validated): ?Organization
    {
        if (!empty($validated['organization_id'])) {
            return Organization::where('id', (int) $validated['organization_id'])->first();
        }

        $candidate = $validated['organization_email'] ?? $validated['organization_link'] ?? null;
        if (!$candidate) {
            return null;
        }

        $candidate = trim($candidate);

        if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
            return Organization::where('email', $candidate)->first();
        }

        if (is_numeric($candidate)) {
            return Organization::where('id', (int) $candidate)->first();
        }

        if (filter_var($candidate, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($candidate);

            $query = [];
            parse_str($parsed['query'] ?? '', $query);

            foreach (['organization_email', 'org_email', 'email', 'organization', 'org'] as $key) {
                if (!empty($query[$key])) {
                    $value = trim((string) $query[$key]);

                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return Organization::where('email', $value)->first();
                    }

                    if (is_numeric($value)) {
                        return Organization::where('id', (int) $value)->first();
                    }
                }
            }

            $path = trim($parsed['path'] ?? '', '/');
            $segments = $path !== '' ? explode('/', $path) : [];

            foreach (array_reverse($segments) as $segment) {
                $segment = trim($segment);
                if ($segment === '') {
                    continue;
                }

                if (is_numeric($segment)) {
                    $org = Organization::where('id', (int) $segment)->first();
                    if ($org) {
                        return $org;
                    }
                }

                if (str_contains($segment, '%40')) {
                    $decoded = urldecode($segment);
                    if (filter_var($decoded, FILTER_VALIDATE_EMAIL)) {
                        $org = Organization::where('email', $decoded)->first();
                        if ($org) {
                            return $org;
                        }
                    }
                }
            }
        }

        return null;
    }

    private function sendDeveloperApprovalEmail(Organization $organization, User $developer): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'dashboard.developer.verify',
            now()->addHours(48),
            ['user' => $developer->id]
        );

        Mail::send('emails.developer-approval-request', [
            'organizationName' => $organization->name,
            'developerName' => $developer->name,
            'developerEmail' => $developer->email,
            'requestedAt' => now()->format('d M Y H:i'),
            'verificationUrl' => $verificationUrl,
        ], function ($message) use ($organization, $developer) {
            $message->to($organization->email)
                ->subject('Developer Approval Request: ' . $developer->email);
        });
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

        $activeCheck = $this->validateActiveContext($request->user());
        if ($activeCheck !== true) {
            return $activeCheck;
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

        $activeCheck = $this->validateActiveContext($request->user());
        if ($activeCheck !== true) {
            return $activeCheck;
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

        $activeCheck = $this->validateActiveContext($user);
        if ($activeCheck !== true) {
            return $activeCheck;
        }
        
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

        $activeCheck = $this->validateActiveContext($user);
        if ($activeCheck !== true) {
            return $activeCheck;
        }
        
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

        $activeCheck = $this->validateActiveContext($user);
        if ($activeCheck !== true) {
            return $activeCheck;
        }
        
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

    private function validateActiveContext(?User $user)
    {
        if (!$user) {
            return response()->json([
                'message' => 'No authenticated user context available.',
            ], 401);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'message' => 'Account pending approval or inactive.',
            ], 403);
        }

        $user->loadMissing('organization');
        if (!$user->organization || $user->organization->status !== 'active') {
            return response()->json([
                'message' => 'Organization is not active.',
            ], 403);
        }

        return true;
    }
}
