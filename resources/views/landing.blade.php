<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safari API – Payments for East Africa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1D4ED8;
            --navy: #0A1628;
            --navy-2: #0f2040;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            color: #1E293B;
        }

        /* ── Navbar ──────────────────────────────── */
        .navbar-main {
            position: sticky;
            top: 0;
            z-index: 1040;
            background: rgba(10, 22, 40, .95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            padding: .85rem 0;
        }

        .navbar-brand-text {
            font-weight: 800;
            color: #fff;
            font-size: 1.15rem;
            text-decoration: none;
        }

        .navbar-brand-text i {
            color: #60a5fa;
        }

        .nav-link-light {
            color: rgba(255, 255, 255, .68);
            font-size: .875rem;
            font-weight: 500;
            text-decoration: none;
            padding: .35rem .7rem;
            border-radius: 6px;
            transition: color .15s;
        }

        .nav-link-light:hover {
            color: #fff;
        }

        .btn-nav-outline {
            color: rgba(255, 255, 255, .8);
            border: 1px solid rgba(255, 255, 255, .2);
            background: transparent;
            border-radius: 7px;
            padding: .4rem 1rem;
            font-size: .875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all .15s;
        }

        .btn-nav-outline:hover {
            background: rgba(255, 255, 255, .07);
            color: #fff;
        }

        .btn-nav-primary {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: .4rem 1.1rem;
            font-size: .875rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }

        .btn-nav-primary:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        /* ── Hero ────────────────────────────────── */
        .hero {
            background: var(--navy);
            padding: 6rem 0 5rem;
            overflow: hidden;
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 60% 40%, rgba(37, 99, 235, .18) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: rgba(37, 99, 235, .15);
            border: 1px solid rgba(37, 99, 235, .3);
            color: #93c5fd;
            font-size: .75rem;
            font-weight: 600;
            padding: .3rem .8rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.18;
            margin-bottom: 1.25rem;
        }

        .hero h1 .accent {
            color: #60a5fa;
        }

        .hero .sub {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, .6);
            max-width: 520px;
            line-height: 1.65;
        }

        .hero-ctas {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-hero-primary {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: .75rem 1.75rem;
            font-size: .95rem;
            font-weight: 700;
            text-decoration: none;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .btn-hero-primary:hover {
            background: var(--primary-dark);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-hero-outline {
            background: transparent;
            color: rgba(255, 255, 255, .75);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 9px;
            padding: .75rem 1.5rem;
            font-size: .95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .btn-hero-outline:hover {
            border-color: rgba(255, 255, 255, .4);
            color: #fff;
        }

        /* ── Code panel ──────────────────────────── */
        .code-panel {
            background: #0d1117;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .35);
        }

        .code-panel-header {
            background: #161b22;
            padding: .65rem 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .code-panel-header .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .code-panel-header .label {
            font-size: .72rem;
            color: rgba(255, 255, 255, .3);
            font-family: 'IBM Plex Mono', monospace;
            margin-left: auto;
        }

        .code-panel pre {
            margin: 0;
            padding: 1.25rem 1.5rem;
            font-family: 'IBM Plex Mono', monospace;
            font-size: .78rem;
            line-height: 1.7;
            color: #c9d1d9;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .code-panel .kw {
            color: #ff7b72;
        }

        .code-panel .str {
            color: #a5d6ff;
        }

        .code-panel .key {
            color: #79c0ff;
        }

        .code-panel .val {
            color: #f8c555;
        }

        .code-panel .cmt {
            color: #6e7681;
            font-style: italic;
        }

        .code-panel .url {
            color: #56d364;
        }

        /* ── Feature cards ───────────────────────── */
        .section-features {
            padding: 5rem 0;
            background: #fff;
        }

        .feature-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.75rem;
            height: 100%;
            transition: border-color .2s, box-shadow .2s;
        }

        .feature-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 20px rgba(37, 99, 235, .08);
        }

        .feature-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .feature-card h5 {
            font-size: .975rem;
            font-weight: 700;
            margin-bottom: .5rem;
        }

        .feature-card p {
            font-size: .875rem;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        /* ── How it works ────────────────────────── */
        .section-how {
            padding: 5rem 0;
            background: #f8fafc;
        }

        .step-card {
            text-align: center;
            padding: 1rem;
        }

        .step-num {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            margin: 0 auto 1rem;
        }

        .step-card h6 {
            font-size: .95rem;
            font-weight: 700;
            margin-bottom: .4rem;
        }

        .step-card p {
            font-size: .83rem;
            color: #64748b;
            margin: 0;
        }

        .step-connector {
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 1.6rem;
            color: #cbd5e1;
            font-size: 1.2rem;
        }

        /* ── Calculator ──────────────────────────── */
        .section-calc {
            padding: 5rem 0;
            background: var(--navy);
        }

        .calc-card {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 640px;
            margin: 0 auto;
        }

        .calc-card label {
            color: rgba(255, 255, 255, .75);
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: .4rem;
        }

        .calc-range {
            width: 100%;
            -webkit-appearance: none;
            height: 5px;
            border-radius: 3px;
            background: rgba(255, 255, 255, .15);
            outline: none;
        }

        .calc-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--primary);
            cursor: pointer;
        }

        .calc-result {
            background: rgba(37, 99, 235, .2);
            border: 1px solid rgba(37, 99, 235, .4);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            margin-top: 1.75rem;
        }

        .calc-result .result-label {
            font-size: .78rem;
            color: rgba(255, 255, 255, .5);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .calc-result .result-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
        }

        .calc-result .result-sub {
            font-size: .83rem;
            color: rgba(255, 255, 255, .5);
            margin-top: .25rem;
        }

        .calc-compare {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .calc-compare .comp-item {
            text-align: center;
        }

        .calc-compare .comp-label {
            font-size: .72rem;
            color: rgba(255, 255, 255, .4);
        }

        .calc-compare .comp-value {
            font-size: .95rem;
            font-weight: 700;
        }

        .comp-positive {
            color: #34d399;
        }

        .comp-negative {
            color: #f87171;
        }

        /* ── Footer ──────────────────────────────── */
        .footer {
            background: #060e1c;
            color: rgba(255, 255, 255, .4);
            padding: 2rem 0;
            font-size: .83rem;
        }

        .footer a {
            color: rgba(255, 255, 255, .5);
            text-decoration: none;
            transition: color .15s;
        }

        .footer a:hover {
            color: #fff;
        }

        /* ── Section headings ────────────────────── */
        .section-eyebrow {
            font-size: .73rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: .6rem;
        }

        .section-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            color: #0f172a;
            margin-bottom: .75rem;
            line-height: 1.25;
        }

        .section-sub {
            font-size: .95rem;
            color: #64748b;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.65;
        }
    </style>
</head>

<body>

    {{-- ── Navbar ────────────────────────────────────────────────────────────── --}}
    <nav class="navbar-main">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('landing') }}" class="navbar-brand-text">
                    <i class="bi bi-lightning-charge-fill"></i> Safari API
                </a>
                <div class="d-none d-md-flex align-items-center gap-1">
                    <a href="{{ route('api.docs') }}" class="nav-link-light">API Docs</a>
                    <a href="#pricing" class="nav-link-light">Pricing</a>
                    <a href="{{ route('organizations.register') }}" class="nav-link-light">Register Org</a>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('dashboard.login') }}" class="btn-nav-outline">Login</a>
                    <a href="{{ route('dashboard.register') }}" class="btn-nav-primary">
                        Get Started <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- ── Hero ───────────────────────────────────────────────────────────────── --}}
    <section class="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-eyebrow">
                        <i class="bi bi-stars"></i> Developer-First · Tanzania & East Africa
                    </div>
                    <h1>Accept payments from every bank &<br><span class="accent">mobile money</span> in Tanzania.</h1>
                    <p class="sub">
                        One API. All banks. All mobile money providers.
                        And we <strong style="color:#34d399;">pay you 1%</strong> on every float deposit you collect.
                    </p>
                    <div class="hero-ctas">
                        <a href="{{ route('dashboard.register') }}" class="btn-hero-primary">
                            <i class="bi bi-rocket-takeoff"></i> Start Building
                        </a>
                        <a href="{{ route('api.docs') }}" class="btn-hero-outline">
                            <i class="bi bi-book"></i> API Documentation
                        </a>
                    </div>
                    <div class="mt-4 d-flex gap-4 flex-wrap" style="font-size:.8rem;color:rgba(255,255,255,.4);">
                        <span><i class="bi bi-check-circle-fill me-1" style="color:#34d399;"></i> No setup fees</span>
                        <span><i class="bi bi-check-circle-fill me-1" style="color:#34d399;"></i> Live in &lt;10
                            minutes</span>
                        <span><i class="bi bi-check-circle-fill me-1" style="color:#34d399;"></i> RESTful JSON
                            API</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Feature cards ─────────────────────────────────────────────────────── --}}
    <section class="section-features">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-eyebrow">Why Safari API</div>
                <div class="section-title">Everything you need, nothing you don't.</div>
                <p class="section-sub">Built for developers who want to move fast, integrate once, and earn while they
                    build.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#eff6ff;color:#2563eb;"><i
                                class="bi bi-lightning-charge-fill"></i></div>
                        <h5>Go Live in Minutes</h5>
                        <p>Register, get your API keys instantly, and make your first API call in under 10 minutes. Zero
                            setup friction, no paperwork to start.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#ecfdf5;color:#059669;"><i
                                class="bi bi-currency-dollar"></i></div>
                        <h5>Earn 1% Commission</h5>
                        <p>We pay you 1% on every float deposit collected through your integration. The more you
                            collect, the more you earn — automatically, monthly.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#fdf4ff;color:#9333ea;"><i class="bi bi-bank"></i></div>
                        <h5>All Banks & Mobile Money</h5>
                        <p>CRDB, NMB, NBC, Equity, Vodacom M-Pesa, Tigo Pesa, Airtel Money — one integration covers
                            everything across Tanzania and East Africa.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#fff7ed;color:#ea580c;"><i class="bi bi-code-slash"></i>
                        </div>
                        <h5>Clean REST API</h5>
                        <p>Predictable JSON responses, clear error codes, comprehensive API documentation, and SDKs to
                            help you integrate faster.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-bell-fill"></i>
                        </div>
                        <h5>Real-Time Webhooks</h5>
                        <p>Get instant notifications on payment events. Configure custom webhook endpoints per
                            organization with built-in delivery retries.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#fefce8;color:#ca8a04;"><i
                                class="bi bi-bar-chart-fill"></i></div>
                        <h5>Developer Dashboard</h5>
                        <p>Monitor API requests, track collections, view your commission earnings, and manage
                            credentials — all in one clean dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── How it works ───────────────────────────────────────────────────────── --}}
    <section class="section-how">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-eyebrow">How It Works</div>
                <div class="section-title">Three steps to accepting payments.</div>
            </div>
            <div class="row align-items-start justify-content-center">
                <div class="col-6 col-md-3">
                    <div class="step-card">
                        <div class="step-num">1</div>
                        <h6>Register Account</h6>
                        <p>Create your developer account and register your organization in under 2 minutes.</p>
                    </div>
                </div>
                <div class="col-2 col-md-1 d-none d-md-block">
                    <div class="step-connector"><i class="bi bi-arrow-right"></i></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="step-card">
                        <div class="step-num">2</div>
                        <h6>Get API Keys</h6>
                        <p>Generate OAuth2 credentials from the dashboard. Use them in your application immediately.</p>
                    </div>
                </div>
                <div class="col-2 col-md-1 d-none d-md-block">
                    <div class="step-connector"><i class="bi bi-arrow-right"></i></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="step-card">
                        <div class="step-num" style="background:#059669;">3</div>
                        <h6>Collect & Earn</h6>
                        <p>Accept payments from every bank & mobile money provider — and earn 1% commission on deposits.
                        </p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('dashboard.register') }}" class="btn px-4 py-2"
                    style="background:var(--primary);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:.95rem;">
                    <i class="bi bi-rocket-takeoff me-1"></i> Get Started Free
                </a>
            </div>
        </div>
    </section>

    {{-- ── Commission Calculator ──────────────────────────────────────────────── --}}
    <section class="section-calc" id="pricing">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-eyebrow" style="color:#93c5fd;">Commission Calculator</div>
                <div class="section-title" style="color:#fff;">See how much you can earn.</div>
                <p class="section-sub" style="color:rgba(255,255,255,.5);">Move your slider to estimate your monthly
                    commission based on collection volume.</p>
            </div>
            <div class="calc-card">
                <div class="mb-4">
                    <label class="d-flex justify-content-between">
                        <span>Monthly Collection Volume</span>
                        <span style="color:#60a5fa;font-weight:700;font-family:'IBM Plex Mono',monospace;"
                            id="volumeDisplay">TZS 10,000,000</span>
                    </label>
                    <input type="range" class="calc-range mt-2" id="volumeSlider" min="0" max="100000000"
                        step="500000" value="10000000">
                    <div class="d-flex justify-content-between mt-1"
                        style="font-size:.7rem;color:rgba(255,255,255,.3);">
                        <span>TZS 0</span><span>TZS 100M</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="d-flex justify-content-between">
                        <span>Float duration</span>
                        <span style="color:#60a5fa;font-weight:700;font-family:'IBM Plex Mono',monospace;"
                            id="daysDisplay">7 days</span>
                    </label>
                    <input type="range" class="calc-range mt-2" id="daysSlider" min="1" max="30"
                        step="1" value="7">
                    <div class="d-flex justify-content-between mt-1"
                        style="font-size:.7rem;color:rgba(255,255,255,.3);">
                        <span>1 day</span><span>30 days</span>
                    </div>
                </div>

                <div class="calc-result">
                    <div class="result-label">Your monthly commission</div>
                    <div class="result-value" id="commissionResult">TZS 100,000</div>
                    <div class="result-sub">at 1% on TZS 10,000,000 monthly volume</div>
                </div>
                <div class="calc-compare">
                    <div class="comp-item">
                        <div class="comp-label">Your earnings</div>
                        <div class="comp-value comp-positive" id="earnValue">+ TZS 100,000</div>
                    </div>
                    <div class="comp-item">
                        <div class="comp-label">Typical aggregator fees (2.5%)</div>
                        <div class="comp-value comp-negative" id="aggFee">− TZS 250,000</div>
                    </div>
                    <div class="comp-item">
                        <div class="comp-label">Net advantage</div>
                        <div class="comp-value comp-positive" id="netAdv">TZS 350,000</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Footer ─────────────────────────────────────────────────────────────── --}}
    <footer class="footer">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <a href="{{ route('landing') }}"
                        style="color:#fff;font-weight:700;text-decoration:none;font-size:.95rem;">
                        <i class="bi bi-lightning-charge-fill" style="color:#60a5fa;"></i> Safari API
                    </a>
                    <div class="mt-1">© {{ date('Y') }} SafariBank Africa Ltd. All rights reserved.</div>
                </div>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('api.docs') }}">API Docs</a>
                    <a href="{{ route('dashboard.register') }}">Register</a>
                    <a href="{{ route('dashboard.login') }}">Login</a>
                    <a href="{{ route('organizations.register') }}">Register Org</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fmt(n) {
            return 'TZS ' + Math.round(n).toLocaleString('en-US');
        }

        const volumeSlider = document.getElementById('volumeSlider');
        const daysSlider = document.getElementById('daysSlider');

        function updateCalc() {
            const volume = parseInt(volumeSlider.value);
            const days = parseInt(daysSlider.value);

            const commission = volume * 0.01;
            const aggFee = volume * 0.025;
            const netAdv = commission + aggFee;

            document.getElementById('volumeDisplay').textContent = fmt(volume);
            document.getElementById('daysDisplay').textContent = days + (days === 1 ? ' day' : ' days');
            document.getElementById('commissionResult').textContent = fmt(commission);
            document.querySelector('.calc-result .result-sub').textContent =
                'at 1% on ' + fmt(volume) + ' monthly volume';
            document.getElementById('earnValue').textContent = '+ ' + fmt(commission);
            document.getElementById('aggFee').textContent = '− ' + fmt(aggFee);
            document.getElementById('netAdv').textContent = fmt(netAdv);
        }

        volumeSlider.addEventListener('input', updateCalc);
        daysSlider.addEventListener('input', updateCalc);
        updateCalc();
    </script>
</body>

</html>
