<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account – Safari API</title>
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
            width: 380px; flex-shrink: 0; background: var(--navy);
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 3rem 2.5rem; color: #fff;
        }
        .auth-left .brand { font-size: 1.2rem; font-weight: 700; color: #fff; text-decoration: none; }
        .auth-left .brand i { color: #60a5fa; }
        .auth-left .tagline { font-size: 1.5rem; font-weight: 700; line-height: 1.35; margin-top: 3rem; }
        .auth-left .tagline span { color: #60a5fa; }
        .auth-left .sub { font-size: .855rem; color: rgba(255,255,255,.5); margin-top: .6rem; }
        .commission-chip {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(52,211,153,.12); color: #6ee7b7;
            border: 1px solid rgba(52,211,153,.25);
            border-radius: 20px; padding: .35rem .85rem;
            font-size: .78rem; font-weight: 600; margin-top: 1.5rem;
        }
        .auth-left .copyright { font-size: .73rem; color: rgba(255,255,255,.3); }
        .auth-right {
            flex: 1; display: flex; align-items: center; justify-content: center;
            background: #f8fafc; padding: 2rem 1rem; overflow-y: auto;
        }
        .auth-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 2rem 2.25rem; width: 100%; max-width: 480px;
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
        }
        .auth-card h4 { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin-bottom: .3rem; }
        .auth-card p.lead { font-size: .855rem; color: #64748b; margin-bottom: 1.5rem; }
        .form-label { font-size: .83rem; font-weight: 500; color: #374151; margin-bottom: .3rem; }
        .form-control {
            font-family: 'IBM Plex Mono', monospace; font-size: .85rem;
            border-color: #e2e8f0; border-radius: 8px; padding: .55rem .85rem;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .form-text { font-size: .75rem; color: #94a3b8; }
        .btn-primary-full {
            width: 100%; padding: .65rem 1rem; background: var(--primary);
            color: #fff; border: none; border-radius: 8px;
            font-weight: 600; font-size: .9rem; cursor: pointer; transition: background .15s;
        }
        .btn-primary-full:hover { background: #1d4ed8; }
        .section-divider { font-size: .72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .07em; margin: 1.25rem 0 .75rem; }
        @media (max-width: 767.98px) { .auth-left { display: none; } }
    </style>
</head>
<body>
    <!-- Left panel -->
    <div class="auth-left">
        <div>
            <a href="{{ route('landing') }}" class="brand">
                <i class="bi bi-lightning-charge-fill"></i> Safari API
            </a>
            <div class="tagline">Start building<br>payments in<br><span>minutes.</span></div>
            <div class="sub">Register your account, get API credentials immediately, and start accepting payments from every bank and mobile money in Tanzania.</div>
            <div class="commission-chip">
                <i class="bi bi-currency-dollar"></i> Earn 1% commission on float deposits
            </div>
        </div>
        <div class="copyright">© {{ date('Y') }} SafariBank Africa Ltd.</div>
    </div>

    <!-- Right panel -->
    <div class="auth-right">
        <div class="auth-card">
            <h4>Create your account</h4>
            <p class="lead">Get access to the developer dashboard and API keys.</p>

            @if($errors->any())
            <div class="mb-3">
                @foreach($errors->all() as $error)
                    <div class="d-flex align-items-center gap-2 p-2 mb-1 rounded" style="background:#fef2f2;color:#991b1b;font-size:.82rem;border:1px solid #fecaca;">
                        <i class="bi bi-exclamation-circle-fill shrink-0"></i> {{ $error }}
                    </div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('dashboard.register.post') }}">
                @csrf

                <div class="section-divider">Account details</div>

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="John Doe"
                           value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Your Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="john@company.com"
                           value="{{ old('email') }}" required>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Min 8 chars" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat" required>
                    </div>
                </div>

                <div class="section-divider">Organization</div>

                <div class="mb-3">
                    <label class="form-label">Organization Email</label>
                    <input type="email" name="organization_email" id="organizationEmailInput" class="form-control"
                           placeholder="registered@organization.com"
                           value="{{ old('organization_email') }}">
                    <div class="form-text">Type organization email, or select an organization below. Organization must exist and be active.</div>
                </div>

                @if($organizations->count() > 0)
                <div class="mb-3">
                    <label class="form-label">Or pick active organization</label>
                    <select class="form-select" id="orgPicker" name="organization_id" style="font-family:'IBM Plex Mono',monospace;font-size:.83rem;border-color:#e2e8f0;border-radius:8px;">
                        <option value="">— Select to auto-fill —</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Organization email is retrieved securely from database using selected organization.</div>
                </div>
                @endif

                <button type="submit" class="btn-primary-full mt-1">
                    <i class="bi bi-person-plus me-1"></i> Request Developer Access
                </button>
            </form>

            <div class="mt-2 p-2 rounded" style="font-size:.76rem;color:#92400e;background:#fffbeb;border:1px solid #fde68a;">
                <i class="bi bi-envelope-check me-1"></i>
                After registration, your account remains <strong>pending</strong> until your organization approves via email.
            </div>

            <div class="mt-3 text-center" style="font-size:.83rem;color:#64748b;">
                Already have an account?
                <a href="{{ route('dashboard.login') }}" style="color:var(--primary);font-weight:500;">Sign in →</a>
            </div>
            <div class="mt-2 text-center" style="font-size:.78rem;color:#94a3b8;">
                Need to register a new organization?
                <a href="{{ route('organizations.register') }}" style="color:#64748b;">Register organization →</a>
            </div>
            <div class="mt-2 text-center" style="font-size:.8rem;color:#94a3b8;">
                <a href="{{ route('landing') }}" style="color:#64748b;text-decoration:none;">
                    <i class="bi bi-arrow-left me-1"></i> Back to Landing
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
