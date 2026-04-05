<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In – Safari API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563EB;
            --navy:    #0A1628;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0; min-height: 100vh;
            display: flex;
        }

        /* Left panel */
        .auth-left {
            width: 420px;
            flex-shrink: 0;
            background: var(--navy);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 2.75rem;
            color: #fff;
        }
        .auth-left .brand { font-size: 1.2rem; font-weight: 700; color: #fff; text-decoration: none; }
        .auth-left .brand i { color: #60a5fa; }
        .auth-left .tagline { font-size: 1.7rem; font-weight: 700; line-height: 1.35; margin-top: 3rem; }
        .auth-left .tagline span { color: #60a5fa; }
        .auth-left .sub { font-size: .9rem; color: rgba(255,255,255,.55); margin-top: .75rem; }
        .auth-left .feature-list { list-style: none; padding: 0; margin: 2rem 0 0; }
        .auth-left .feature-list li {
            display: flex; align-items: flex-start; gap: .6rem;
            font-size: .83rem; color: rgba(255,255,255,.6);
            margin-bottom: .6rem;
        }
        .auth-left .feature-list li i { color: #34d399; flex-shrink: 0; margin-top: .1rem; }
        .auth-left .copyright { font-size: .73rem; color: rgba(255,255,255,.3); }

        /* Right panel */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2.25rem 2.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
        }
        .auth-card h4 { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: .35rem; }
        .auth-card p.lead { font-size: .875rem; color: #64748b; margin-bottom: 1.75rem; }

        .form-label { font-size: .83rem; font-weight: 500; color: #374151; margin-bottom: .3rem; }
        .form-control {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .85rem;
            border-color: #e2e8f0;
            border-radius: 8px;
            padding: .55rem .85rem;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .btn-primary-full {
            width: 100%;
            padding: .65rem 1rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-primary-full:hover { background: #1d4ed8; }
        .divider { display: flex; align-items: center; gap: .75rem; margin: 1.25rem 0; color: #94a3b8; font-size: .78rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
        .hint-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .78rem;
            color: #0c4a6e;
            font-family: 'IBM Plex Mono', monospace;
        }
        .hint-box strong { font-size: .73rem; display: block; margin-bottom: .25rem; color: #0369a1; }

        @media (max-width: 767.98px) {
            .auth-left { display: none; }
        }
    </style>
</head>
<body>
    <!-- Left branding panel -->
    <div class="auth-left">
        <div>
            <a href="{{ route('landing') }}" class="brand">
                <i class="bi bi-lightning-charge-fill"></i> Safari API
            </a>
            <div class="tagline">The developer-first<br><span>payments platform</span><br>for East Africa.</div>
            <div class="sub">One integration. All banks. All mobile money. And we <strong>pay you</strong> 1% on float deposits.</div>
            <ul class="feature-list">
                <li><i class="bi bi-check-circle-fill"></i> Live in under 10 minutes</li>
                <li><i class="bi bi-check-circle-fill"></i> Earn 1% commission on every deposit</li>
                <li><i class="bi bi-check-circle-fill"></i> CRDB, NMB, Vodacom M-Pesa & more</li>
                <li><i class="bi bi-check-circle-fill"></i> Real-time webhooks & comprehensive logs</li>
            </ul>
        </div>
        <div class="copyright">© {{ date('Y') }} ShuleSoft Ltd. All rights reserved.</div>
    </div>

    <!-- Right form panel -->
    <div class="auth-right">
        <div class="auth-card">
            <h4>Welcome back</h4>
            <p class="lead">Sign in to your developer dashboard.</p>

            @if(session('success'))
            <div class="mb-3 d-flex align-items-center gap-2 p-2 rounded" style="background:#ecfdf5;color:#065f46;font-size:.83rem;border:1px solid #a7f3d0;">
                <i class="bi bi-check-circle-fill shrink-0"></i> {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-3 d-flex align-items-center gap-2 p-2 rounded" style="background:#fef2f2;color:#991b1b;font-size:.83rem;border:1px solid #fecaca;">
                <i class="bi bi-exclamation-circle-fill shrink-0"></i> {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-3">
                @foreach($errors->all() as $error)
                    <div class="d-flex align-items-center gap-2 p-2 mb-1 rounded" style="background:#fef2f2;color:#991b1b;font-size:.83rem;border:1px solid #fecaca;">
                        <i class="bi bi-exclamation-circle-fill shrink-0"></i> {{ $error }}
                    </div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('dashboard.login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@company.com"
                           value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-1">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="btn btn-outline-secondary" style="border-color:#e2e8f0;border-radius:0 8px 8px 0;"
                                onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:.83rem;color:#64748b;">Remember me</label>
                </div>
                <button type="submit" class="btn-primary-full">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Dashboard
                </button>
            </form>

            <div class="divider">or</div>

            <div class="hint-box">
                <strong>🔑 API-only access?</strong>
                Use <code>POST /api/v1/auth/login</code> or OAuth2 client credentials.<br>
                <a href="{{ route('api.docs') }}" target="_blank" style="color:#0369a1;">View API Docs →</a>
            </div>

            <div class="mt-3 text-center" style="font-size:.83rem;color:#64748b;">
                Don't have an account?
                <a href="{{ route('dashboard.register') }}" style="color:var(--primary);font-weight:500;">Create one →</a>
            </div>
            <div class="mt-2 text-center" style="font-size:.8rem;color:#94a3b8;">
                <a href="{{ route('landing') }}" style="color:#64748b;text-decoration:none;">
                    <i class="bi bi-arrow-left me-1"></i> Back to Landing
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
    </script>
</body>
</html>
