@extends('dashboard.layout')

@section('title', 'Organization')
@section('page-title', 'Organization Settings')

@section('content')

@php $isDeveloper = $org->account_type === 'developer'; @endphp

{{-- ── New credential flash ──────────────────────────────────────────────── --}}
@if(session('new_client_secret'))
<div class="mb-4 p-3 rounded-3" style="background:#0f172a;border:1px solid #1e40af;">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-shield-check" style="color:#34d399;font-size:1.1rem;"></i>
        <span style="font-weight:700;color:#fff;font-size:.9rem;">New API Credentials Generated</span>
        <span class="badge-danger ms-1" style="background:rgba(239,68,68,.2);color:#f87171;">Copy now — won't be shown again</span>
    </div>
    <div class="row g-2">
        <div class="col-md-6">
            <div style="font-size:.7rem;color:#94a3b8;font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.3rem;">Client ID</div>
            <div class="d-flex align-items-center gap-2">
                <code id="newClientId" style="background:#1e293b;color:#93c5fd;padding:.4rem .75rem;border-radius:6px;font-size:.82rem;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ session('new_client_id') }}</code>
                <button class="btn btn-sm" onclick="copyText('newClientId', this)" style="background:#1e3a5f;color:#93c5fd;border:none;border-radius:6px;white-space:nowrap;"><i class="bi bi-clipboard me-1"></i>Copy</button>
            </div>
        </div>
        <div class="col-md-6">
            <div style="font-size:.7rem;color:#94a3b8;font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.3rem;">Client Secret</div>
            <div class="d-flex align-items-center gap-2">
                <code id="newClientSecret" style="background:#1e293b;color:#fca5a5;padding:.4rem .75rem;border-radius:6px;font-size:.82rem;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ session('new_client_secret') }}</code>
                <button class="btn btn-sm" onclick="copyText('newClientSecret', this)" style="background:#3f1d1d;color:#fca5a5;border:none;border-radius:6px;white-space:nowrap;"><i class="bi bi-clipboard me-1"></i>Copy</button>
            </div>
        </div>
    </div>
    <div class="mt-2" style="font-size:.75rem;color:#f59e0b;"><i class="bi bi-exclamation-triangle me-1"></i>The secret will not accessible again after you leave this page.</div>
</div>
@endif

<div class="row g-4">

    {{-- ── Left: Org details ──────────────────────────────────────────────── --}}
    <div class="col-lg-7">

        {{-- Organization info card --}}
        <div class="card mb-4">
            <div class="card-header-flush">
                <i class="bi bi-building text-muted"></i>
                <h6>Organization Details</h6>
                <span class="ms-auto">
                    @if($org->status === 'active')
                        <span class="badge-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                    @elseif($org->status === 'pending')
                        <span class="badge-warning">Pending Review</span>
                    @else
                        <span class="badge-danger">{{ ucfirst($org->status) }}</span>
                    @endif
                </span>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('dashboard.organization.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Organization Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $org->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $org->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $org->phone) }}" required>
                        </div>
                        @if($org->tin_number)
                        <div class="col-md-6">
                            <label class="form-label">TIN Number</label>
                            <input type="text" class="form-control" value="{{ $org->tin_number }}" disabled>
                        </div>
                        @endif
                        @if($org->registration_number)
                        <div class="col-md-6">
                            <label class="form-label">Registration Number</label>
                            <input type="text" class="form-control" value="{{ $org->registration_number }}" disabled>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Account Type</label>
                            <input type="text" class="form-control" value="{{ ucfirst($org->account_type) }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" value="{{ $org->country->name ?? '—' }}" disabled>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-sm px-4"
                                    style="background:#2563eb;color:#fff;border:none;border-radius:7px;font-weight:600;padding:.5rem 1.25rem;">
                                <i class="bi bi-floppy me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- API Credentials card --}}
        <div class="card">
            <div class="card-header-flush">
                <i class="bi bi-key text-muted"></i>
                <h6>API Credentials</h6>
                <form method="POST" action="{{ route('dashboard.organization.credentials') }}" class="ms-auto"
                      onsubmit="return confirm('This will revoke existing credentials. Are you sure?')">
                    @csrf
                    <button type="submit" class="btn btn-sm"
                            style="background:#fff3f3;color:#ef4444;border:1px solid #fecaca;border-radius:6px;font-size:.78rem;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Rotate Credentials
                    </button>
                </form>
            </div>
            <div class="p-4">
                @if($oauthClient)
                <div class="mb-3">
                    <div class="mb-1" style="font-size:.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Client ID</div>
                    <div class="d-flex align-items-center gap-2">
                        <code id="clientIdDisplay" style="background:#f8fafc;border:1px solid #e2e8f0;color:#1e293b;padding:.4rem .75rem;border-radius:7px;font-size:.83rem;flex:1;">{{ $oauthClient->client_id }}</code>
                        <button class="btn btn-sm" onclick="copyText('clientIdDisplay', this)"
                                style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:7px;">
                            <i class="bi bi-clipboard me-1"></i> Copy
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="mb-1" style="font-size:.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Client Secret</div>
                    <div class="d-flex align-items-center gap-2">
                        <code id="clientSecretDisplay" style="background:#f8fafc;border:1px solid #e2e8f0;color:#1e293b;padding:.4rem .75rem;border-radius:7px;font-size:.83rem;flex:1;letter-spacing:.1em;">
                            {{ $oauthClient->client_secret_prefix }}●●●●●●●●●●●●●●●●
                        </code>
                        <span class="badge-warning" style="white-space:nowrap;">Not retrievable</span>
                    </div>
                    <div style="font-size:.73rem;color:#94a3b8;margin-top:.35rem;">
                        <i class="bi bi-info-circle me-1"></i> Use "Rotate Credentials" to generate a new secret.
                    </div>
                </div>
                <div class="mb-3">
                    <div class="mb-1" style="font-size:.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Environment</div>
                    <span class="{{ $oauthClient->environment === 'live' ? 'badge-success' : 'badge-warning' }}">
                        {{ strtoupper($oauthClient->environment) }}
                    </span>
                    @if($oauthClient->last_used_at)
                    <span style="font-size:.75rem;color:#94a3b8;margin-left:.5rem;">Last used {{ $oauthClient->last_used_at->diffForHumans() }}</span>
                    @endif
                </div>
                @else
                <div class="text-center py-3" style="color:#94a3b8;">
                    <i class="bi bi-key d-block mb-2" style="font-size:1.5rem;"></i>
                    <div style="font-size:.875rem;">No API credentials yet.</div>
                    <form method="POST" action="{{ route('dashboard.organization.credentials') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-sm"
                                style="background:#2563eb;color:#fff;border:none;border-radius:7px;font-weight:600;">
                            <i class="bi bi-plus-circle me-1"></i> Generate API Credentials
                        </button>
                    </form>
                </div>
                @endif

                {{-- Token endpoint snippet --}}
                <div class="mt-3">
                    <div class="mb-1" style="font-size:.75rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Token Endpoint</div>
                    <div class="code-block">curl -X POST {{ url('/api/v1/oauth/token') }} \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET"
  }'</div>
                    <button class="btn btn-sm mt-2" onclick="copySnippet(this)"
                            style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:6px;font-size:.78rem;">
                        <i class="bi bi-clipboard me-1"></i> Copy snippet
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Right: Developer view: Managed orgs ───────────────────────────── --}}
    <div class="col-lg-5">

        @if($isDeveloper && $managedOrgs)
        <div class="card mb-4">
            <div class="card-header-flush">
                <i class="bi bi-diagram-3 text-muted"></i>
                <h6>Managed Organizations</h6>
                <button class="btn btn-sm ms-auto"
                        data-bs-toggle="modal" data-bs-target="#addOrgModal"
                        style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:6px;font-size:.78rem;">
                    <i class="bi bi-plus-circle me-1"></i> Add Organization
                </button>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th style="width:80px;">Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($managedOrgs as $mo)
                        <tr>
                            <td>
                                <div style="font-size:.875rem;font-weight:500;">{{ $mo->name }}</div>
                                <div style="font-size:.75rem;color:#94a3b8;">{{ $mo->email }}</div>
                            </td>
                            <td>
                                @if($mo->status === 'active')
                                    <span class="badge-success">Active</span>
                                @elseif($mo->status === 'pending')
                                    <span class="badge-warning">Pending</span>
                                @else
                                    <span class="badge-danger">{{ ucfirst($mo->status) }}</span>
                                @endif
                            </td>
                            <td class="mono" style="font-size:.83rem;">{{ $mo->users_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-3" style="color:#94a3b8;font-size:.83rem;">
                                No organizations yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($managedOrgs->hasPages())
            <div class="p-2 border-top">{{ $managedOrgs->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
        @endif

        {{-- Developer earning summary --}}
        <div class="card">
            <div class="card-header-flush">
                <i class="bi bi-percent text-muted"></i>
                <h6>Commission Structure</h6>
            </div>
            <div class="p-4">
                <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:.83rem;color:#374151;">Float Deposit Commission</span>
                    <span style="font-weight:700;color:#059669;font-size:.95rem;">1.0%</span>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:.83rem;color:#374151;">Payout Frequency</span>
                    <span style="font-weight:600;color:#374151;font-size:.83rem;">Monthly</span>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2">
                    <span style="font-size:.83rem;color:#374151;">Minimum Payout</span>
                    <span style="font-weight:600;color:#374151;font-size:.83rem;">TZS 5,000</span>
                </div>
                <p style="font-size:.75rem;color:#94a3b8;margin-top:.75rem;margin-bottom:0;">
                    Commission is calculated on all float deposits collected through your API integration.
                </p>
            </div>
        </div>

    </div>
</div>

{{-- ── Add Organization Modal (developer only) ─────────────────────────── --}}
@if($isDeveloper)
<div class="modal fade" id="addOrgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:1px solid #e2e8f0;">
            <div class="modal-header" style="border-bottom:1px solid #e2e8f0;">
                <h6 class="modal-title fw-bold">Add New Organization</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dashboard.organization.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Organization Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Acme Corp Ltd" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span style="color:#ef4444;">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="hello@acme.co.tz" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="+255 XXX XXX XXX" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country <span style="color:#ef4444;">*</span></label>
                        <select name="country_id" class="form-select" required style="font-size:.85rem;border-color:#e2e8f0;">
                            <option value="">Select country…</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Currencies <span style="color:#ef4444;">*</span></label>
                        <div class="d-flex gap-3 flex-wrap">
                            @foreach(['TZS','USD','EUR','KES','UGX'] as $cur)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="currency[]" value="{{ $cur }}" id="cur{{ $cur }}">
                                <label class="form-check-label" for="cur{{ $cur }}" style="font-size:.83rem;">{{ $cur }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm"
                            style="background:#2563eb;color:#fff;border:none;border-radius:7px;font-weight:600;padding:.4rem 1rem;">
                        <i class="bi bi-plus-circle me-1"></i> Create Organization
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function copyText(elementId, btn) {
    const el = document.getElementById(elementId);
    const text = el.innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copied!';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}

function copySnippet(btn) {
    const snippet = `curl -X POST {{ url('/api/v1/oauth/token') }} \\
  -H "Content-Type: application/json" \\
  -d '{"grant_type":"client_credentials","client_id":"YOUR_CLIENT_ID","client_secret":"YOUR_CLIENT_SECRET"}'`;
    navigator.clipboard.writeText(snippet).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copied!';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}
</script>
@endpush
