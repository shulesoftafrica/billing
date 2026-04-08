<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class DashboardAuthController extends Controller
{
    // ─── Login ───────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.overview');
        }

        return view('dashboard.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !Hash::check($credentials['password'], $user->password)) {
            $user = null;
        }

        if (!$user) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->withInput($request->only('email'));
        }

        if (($user->status ?? 'active') !== 'active') {
            return back()->withErrors([
                'email' => 'Your account is pending organization approval.',
            ])->withInput($request->only('email'));
        }

        if (!$user->organization || $user->organization->status !== 'active') {
            return back()->withErrors([
                'email' => 'Your organization is not active. Please contact support.',
            ])->withInput($request->only('email'));
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.overview'));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    // ─── Register ────────────────────────────────────────────────────────────

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.overview');
        }

        $organizations = Organization::where('status', 'active')->orderBy('name')->get();

        return view('dashboard.auth.register', compact('organizations'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'organization_id'    => 'nullable|integer|exists:organizations,id',
            'organization_link'  => 'nullable|string|max:500',
            'organization_email' => 'nullable|email|max:255',
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|max:255',
            'password'           => 'required|string|min:8|confirmed',
        ]);

        if (empty($validated['organization_id']) && empty($validated['organization_link']) && empty($validated['organization_email'])) {
            return back()->withErrors([
                'organization_link' => 'Select an organization or provide an organization email/link.',
            ])->withInput();
        }

        $organization = $this->resolveOrganizationFromInput($validated);

        if (!$organization || $organization->status !== 'active') {
            return back()->withErrors([
                'organization_link' => 'Organization not found or not active.',
            ])->withInput();
        }

        $exists = User::where('organization_id', $organization->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['email' => 'A user with this email already exists in that organization.'])
                         ->withInput();
        }

        $user = User::create([
            'organization_id' => $organization->id,
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'role'            => 'admin',
            'status'          => 'pending',
        ]);

        $this->sendDeveloperApprovalEmail($organization, $user);

        return redirect()->route('dashboard.login')->with('success', 'Registration submitted. We have emailed your organization for developer approval.');
    }

    public function verifyDeveloper(Request $request, User $user)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        if (!$user->organization || $user->organization->status !== 'active') {
            return redirect()->route('dashboard.login')
                ->with('error', 'Cannot activate developer because organization is not active.');
        }

        if ($user->status !== 'active') {
            $user->status = 'active';
            $user->activated_at = now();

            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }

            $user->save();
        }

        return redirect()->route('dashboard.login')
            ->with('success', 'Developer verified successfully. Account is now active.');
    }

    // ─── Logout ──────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.login');
    }

    private function resolveOrganizationFromInput(array $validated): ?Organization
    {
        if (!empty($validated['organization_id'])) {
            return Organization::where('id', $validated['organization_id'])->first();
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

                if (Str::contains($segment, '%40')) {
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
}
