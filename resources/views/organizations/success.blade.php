<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registration Successful – Safari API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #2563EB; --navy: #0A1628; }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin: 0; min-height: 100vh; display: flex; }

        .auth-left {
            width: 390px;
            flex-shrink: 0;
            background: var(--navy);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 2.5rem;
        }
        .auth-left .brand { font-size: 1.2rem; font-weight: 700; color: #fff; text-decoration: none; }
        .auth-left .brand i { color: #60a5fa; }
        .auth-left .tagline { font-size: 1.55rem; line-height: 1.35; font-weight: 700; margin-top: 2.8rem; }
        .auth-left .tagline span { color: #34d399; }
        .auth-left .sub { margin-top: .8rem; font-size: .86rem; color: rgba(255,255,255,.55); line-height: 1.6; }
        .auth-left .feature-list { list-style: none; padding: 0; margin: 1.5rem 0 0; }
        .auth-left .feature-list li {
            font-size: .82rem;
            color: rgba(255,255,255,.62);
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            margin-bottom: .55rem;
        }
        .auth-left .feature-list i { color: #34d399; margin-top: .1rem; }
        .auth-left .copyright { font-size: .73rem; color: rgba(255,255,255,.3); }

        .auth-right {
            flex: 1;
            background: #f8fafc;
            padding: 2rem 1.25rem;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .auth-card {
            width: 100%;
            max-width: 760px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
            padding: 2rem;
        }

        .success-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #059669;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .org-details {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: .95rem 1rem;
        }
        .org-details dt {
            font-size: .72rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-top: .6rem;
        }
        .org-details dt:first-child { margin-top: 0; }
        .org-details dd {
            margin: .2rem 0 0;
            color: #1e293b;
            font-size: .83rem;
            font-family: 'IBM Plex Mono', monospace;
        }

        .notice {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            border-radius: 8px;
            padding: .75rem .9rem;
            font-size: .79rem;
        }

        .btn-primary-soft {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .55rem 1rem;
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-primary-soft:hover { background: #1d4ed8; color: #fff; }

        .btn-outline-soft {
            background: #fff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: .55rem 1rem;
            font-size: .85rem;
            font-weight: 500;
            text-decoration: none;
        }
        .btn-outline-soft:hover { background: #f8fafc; color: #1e293b; }

        @media (max-width: 991.98px) {
            .auth-left { display: none; }
            .auth-right { padding: 1rem; }
            .auth-card { padding: 1.25rem; }
        }
    </style>
</head>
<body>

<div class="auth-left">
    <div>
        <a href="{{ route('landing') }}" class="brand">
            <i class="bi bi-lightning-charge-fill"></i> Safari API
        </a>
        <div class="tagline">Organization setup<br><span>submitted</span><br>successfully.</div>
        <div class="sub">Your application is now in review. Once approved, your team can activate integrations and start onboarding developers.</div>

        <ul class="feature-list">
            <li><i class="bi bi-check-circle-fill"></i> KYC documents received</li>
            <li><i class="bi bi-check-circle-fill"></i> Pending compliance review</li>
            <li><i class="bi bi-check-circle-fill"></i> Developer/API onboarding ready</li>
        </ul>
    </div>
    <div class="copyright">© {{ date('Y') }} ShuleSoft Ltd.</div>
</div>

<div class="auth-right">
    <div class="auth-card">
        <div class="text-center mb-3">
            <div class="success-icon mb-3"><i class="bi bi-check-lg"></i></div>
            <h4 style="font-size:1.25rem;font-weight:700;color:#0f172a;">Registration Successful</h4>
            <p style="font-size:.86rem;color:#64748b;margin-bottom:0;">
                @if(session('organization'))
                    <strong style="color:#0f172a;">{{ session('organization')->name }}</strong> has been registered and is pending activation.
                @else
                    Your organization has been registered successfully.
                @endif
            </p>
        </div>

        @if(session('organization'))
            @php $org = session('organization'); @endphp
            <div class="org-details mb-3">
                <dl class="mb-0">
                    <dt>Organization ID</dt>
                    <dd>#{{ $org->id }}</dd>

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
                    <dd style="color:#d97706;font-weight:700;">{{ ucfirst($org->status) }}</dd>
                </dl>
            </div>

            <div class="notice mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Save <strong>Organization ID: {{ $org->id }}</strong> and <strong>Organization Email: {{ $org->email }}</strong>. You’ll need these when creating users via API after activation.
            </div>
        @endif

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="{{ route('organizations.register') }}" class="btn-outline-soft">
                <i class="bi bi-arrow-repeat me-1"></i> Register Another
            </a>
            <a href="{{ route('api.docs') }}" class="btn-primary-soft">
                <i class="bi bi-book me-1"></i> API Documentation
            </a>
            <a href="{{ route('dashboard.login') }}" class="btn-outline-soft">
                <i class="bi bi-box-arrow-in-right me-1"></i> Dashboard Login
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
