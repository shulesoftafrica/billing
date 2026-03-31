@extends('layouts.app')

@section('title', 'Registration Successful')

@push('styles')
<style>
    .success-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(49, 196, 141, 0.15);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .org-details {
        background: var(--surface-soft);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        text-align: left;
        font-size: 0.85rem;
    }
    .org-details dt {
        font-weight: 600;
        color: var(--text-soft);
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.75rem;
    }
    .org-details dt:first-child { margin-top: 0; }
    .org-details dd {
        margin: 2px 0 0;
        color: var(--text);
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.88rem;
    }
    .org-details .org-id {
        background: var(--accent-soft);
        color: var(--accent);
        border-radius: 6px;
        padding: 2px 8px;
        font-weight: 600;
    }
    .info-note {
        background: var(--accent-soft);
        border: 1px solid var(--accent);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.82rem;
        color: var(--text);
        text-align: left;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 mt-5">
            <div class="card">
                <div class="card-body text-center py-5 px-4">
                    <div class="success-icon mb-4">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#31c48d" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h4 style="font-weight:700; font-size:1.2rem; margin-bottom:0.5rem;">Registration Successful</h4>
                    <p style="color: var(--text-soft); font-size: 0.88rem;" class="mb-3">
                        @if(session('organization'))
                            <strong style="color: var(--text);">{{ session('organization')->name }}</strong> has been registered and is pending activation.
                        @else
                            Your organization has been registered successfully.
                        @endif
                    </p>

                    @if(session('organization'))
                        @php $org = session('organization'); @endphp
                        <div class="org-details mb-3">
                            <dl class="mb-0">
                                <dt>Organization ID</dt>
                                <dd><span class="org-id">{{ $org->id }}</span></dd>

                                <dt>Name</dt>
                                <dd>{{ $org->name }}</dd>

                                <dt>Email</dt>
                                <dd>{{ $org->email }}</dd>

                                <dt>Phone</dt>
                                <dd>{{ $org->phone }}</dd>

                                @if($org->tin_number)
                                    <dt>TIN Number</dt>
                                    <dd>{{ $org->tin_number }}</dd>
                                @endif

                                @if($org->registration_number)
                                    <dt>Registration Number</dt>
                                    <dd>{{ $org->registration_number }}</dd>
                                @endif

                                <dt>Currency</dt>
                                <dd>{{ is_array($org->currency) ? implode(', ', $org->currency) : $org->currency }}</dd>

                                <dt>Status</dt>
                                <dd><span style="color: var(--warning, #f59e0b); font-weight: 600;">{{ ucfirst($org->status) }}</span></dd>
                            </dl>
                        </div>

                        <div class="info-note mb-4">
                            <strong> IMPORTANT:</strong> Save your <strong>Organization ID: {{ $org->id }}</strong> and <strong>Organization Email: {{ $org->email }}</strong>. You'll need them to register users via the <code>POST /api/v1/auth/register</code> endpoint once your organization is activated.
                        </div>
                    @else
                        <p style="color: var(--text-soft); font-size: 0.8rem;" class="mb-4">
                            You can now integrate payment gateways and use the billing API.
                        </p>
                    @endif

                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('organizations.register') }}" class="btn btn-outline-primary">Register Another</a>
                        <a href="{{ route('api.docs') }}" class="btn btn-primary">API Documentation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
