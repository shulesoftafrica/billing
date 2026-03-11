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
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:admin,user,manager',
            'sex' => 'nullable|string|in:male,female,other,m,f,M,F',
        ]);

        // Check for unique email within organization
        $existingUser = User::where('organization_id', $validated['organization_id'])
                            ->where('email', $validated['email'])
                            ->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => ['A user with this email already exists in this organization.'],
            ]);
        }

        $user = User::create([
            'organization_id' => $validated['organization_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'user',
            'sex' => $validated['sex'] ?? null,
        ]);

        // Create token with 30-day expiration
        $token = $user->createToken(
            'auth_token',
            ['*'],
            now()->addDays(30)
        )->plainTextToken;

        // Log IP and User Agent
        $this->logTokenAudit($user, $request);

        return response()->json([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 43200, // 30 days in minutes
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

        // Create token with 30-day expiration
        $token = $user->createToken(
            'auth_token',
            ['*'],
            now()->addDays(30)
        )->plainTextToken;

        // Log IP and User Agent
        $this->logTokenAudit($user, $request);

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 43200, // 30 days in minutes
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
}
