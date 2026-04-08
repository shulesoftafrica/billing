<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OAuthClient;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $org  = $user->organization;

        // Fetch OAuth credentials for this organization
        $oauthClient = OAuthClient::where('organization_id', $org->id)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->first();

        // Developer view: show all organizations they can manage
        // (For now, a "developer" account_type user sees all orgs)
        $managedOrgs = null;
        if ($org->account_type === 'developer') {
            $managedOrgs = Organization::withCount([
                'users',
            ])->orderBy('name')->paginate(20);
        }

        $countries  = Country::orderBy('name')->get();
        $currencies = Currency::orderBy('name')->get();

        return view('dashboard.organization', compact(
            'org', 'oauthClient', 'managedOrgs', 'countries', 'currencies'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $org  = $user->organization;

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
            'email'   => 'required|email|max:255|unique:organizations,email,' . $org->id,
        ]);

        $org->update($validated);

        return back()->with('success', 'Organization details updated successfully.');
    }

    /**
     * Store a new organization (developer account type only).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $org  = $user->organization;

        if ($org->account_type !== 'developer') {
            abort(403, 'Only developer accounts can add organizations.');
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'email'      => 'required|email|max:255|unique:organizations,email',
            'country_id' => 'required|exists:countries,id',
            'currency'   => 'required|array|min:1',
        ]);

        $validated['status']       = 'pending';
        $validated['account_type'] = 'organization';

        Organization::create($validated);

        return back()->with('success', 'Organization created successfully. It is pending review.');
    }

    /**
     * Generate a new set of OAuth credentials for the current organization.
     */
    public function generateCredentials(Request $request)
    {
        $user = Auth::user();
        $org  = $user->organization;

        // Revoke all existing active credentials
        OAuthClient::where('organization_id', $org->id)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // Generate new credentials
        $credentials = OAuthClient::generateCredentials('live');

        $client = OAuthClient::create([
            'organization_id'     => $org->id,
            'name'                => $org->name . ' – Dashboard',
            'client_id'           => $credentials['client_id'],
            'client_secret_hash'  => $credentials['client_secret_hash'],
            'client_secret_prefix'=> $credentials['client_secret_prefix'],
            'environment'         => 'live',
            'status'              => 'active',
            'allowed_scopes'      => ['*'],
        ]);

        // Flash plain-text secret ONCE via session
        session()->flash('new_client_secret', $credentials['client_secret']);
        session()->flash('new_client_id', $credentials['client_id']);

        return back()->with('success', 'New API credentials generated. Copy your secret now — it will not be shown again.');
    }
}
