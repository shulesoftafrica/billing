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

        /* ── Footer (legacy compat) ───────────────── */
        .footer {
            display: none;
        }

        /* ── Backed-By Authority Section ────────── */
        .section-backed {
            background: #060e1c;
            padding: 5.5rem 0;
            position: relative;
            overflow: hidden;
        }

        .section-backed::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 50% at 75% 50%, rgba(37,99,235,.06) 0%, transparent 70%), radial-gradient(ellipse 40% 40% at 20% 60%, rgba(255,165,0,.04) 0%, transparent 60%);
            pointer-events: none;
        }

        .backed-hr-label {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 3.5rem;
            justify-content: center;
        }

        .backed-hr-label span {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            white-space: nowrap;
        }

        .backed-hr-label::before,
        .backed-hr-label::after {
            content: '';
            flex: 1;
            max-width: 120px;
            height: 1px;
            background: rgba(255,255,255,.1);
        }

        .institution-card {
            background: rgba(255,255,255,.025);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            transition: border-color .25s, background .25s, transform .2s;
            position: relative;
            overflow: hidden;
        }

        .institution-card:hover {
            border-color: rgba(255,255,255,.18);
            background: rgba(255,255,255,.045);
            transform: translateY(-3px);
        }

        .inst-seal {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            margin-bottom: 1.5rem;
        }

        .inst-role-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            padding: .3rem .85rem;
            border-radius: 20px;
            margin-bottom: 1.1rem;
        }

        .institution-card h3 {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: .6rem;
            line-height: 1.2;
        }

        .institution-card .inst-sub {
            font-size: .875rem;
            color: rgba(255,255,255,.5);
            line-height: 1.72;
            margin-bottom: 1.5rem;
        }

        .inst-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            padding-top: 1.4rem;
            border-top: 1px solid rgba(255,255,255,.07);
        }

        .inst-stat-num {
            font-size: 1.45rem;
            font-weight: 800;
            color: #fff;
            display: block;
            line-height: 1;
            font-family: 'IBM Plex Mono', monospace;
        }

        .inst-stat-label {
            font-size: .71rem;
            color: rgba(255,255,255,.38);
            margin-top: .3rem;
            display: block;
        }

        .builder-strip {
            margin-top: 1.75rem;
            background: linear-gradient(135deg, rgba(37,99,235,.12) 0%, rgba(37,99,235,.06) 100%);
            border: 1px solid rgba(37,99,235,.25);
            border-radius: 16px;
            padding: 1.75rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .bs-icon {
            width: 56px;
            height: 56px;
            background: rgba(37,99,235,.25);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #60a5fa;
            flex-shrink: 0;
        }

        .builder-strip h5 {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 .3rem;
        }

        .builder-strip p {
            font-size: .84rem;
            color: rgba(255,255,255,.5);
            margin: 0;
            line-height: 1.55;
        }

        /* ── Footer Partner Bar ──────────────────── */
        .footer-partner-bar {
            background: #030912;
            padding: 3rem 0 2.5rem;
            border-top: 1px solid rgba(255,255,255,.05);
        }

        .fpar-label {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: rgba(255,255,255,.22);
            margin-bottom: 1.5rem;
        }

        .fpar-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 14px;
            padding: 1.25rem 1.6rem;
            display: flex;
            align-items: center;
            gap: 1.1rem;
            height: 100%;
        }

        .fpar-icon {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .fpar-name {
            font-size: .92rem;
            font-weight: 800;
            color: #fff;
            display: block;
            line-height: 1.2;
        }

        .fpar-role {
            font-size: .73rem;
            color: rgba(255,255,255,.38);
            display: block;
            margin-top: .15rem;
        }

        /* ── Footer Main ─────────────────────────── */
        .footer-main {
            background: #030912;
            padding: 3rem 0 1.5rem;
            border-top: 1px solid rgba(255,255,255,.05);
        }

        .footer-brand-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
        }

        .footer-brand-name i {
            color: #60a5fa;
        }

        .footer-tagline {
            font-size: .82rem;
            color: rgba(255,255,255,.4);
            margin-top: .5rem;
            line-height: 1.6;
            max-width: 240px;
        }

        .footer-col-heading {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            margin-bottom: .9rem;
        }

        .footer-link {
            display: block;
            color: rgba(255,255,255,.5);
            text-decoration: none;
            font-size: .84rem;
            padding: .22rem 0;
            transition: color .15s;
        }

        .footer-link:hover {
            color: #fff;
        }

        .footer-bottom {
            background: #020810;
            border-top: 1px solid rgba(255,255,255,.05);
            padding: 1.25rem 0;
            font-size: .79rem;
            color: rgba(255,255,255,.25);
        }

        /* ── Comparison Section ───────────────────── */
        .section-comparison {
            padding: 5.5rem 0;
            background: #fff;
        }

        .way-card {
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
        }

        .way-card.old-way {
            background: #fff5f5;
            border: 1.5px solid #fecaca;
        }

        .way-card.safari-way {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            position: relative;
            overflow: hidden;
        }

        .way-card.safari-way::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            background: radial-gradient(circle, rgba(52,211,153,.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .way-card-label {
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            padding: .3rem .85rem;
            border-radius: 20px;
            margin-bottom: 1.2rem;
            display: inline-block;
        }

        .way-card h3 {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .way-row {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: .9rem;
            font-size: .895rem;
            line-height: 1.5;
        }

        .way-row .way-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            flex-shrink: 0;
            margin-top: .1rem;
        }

        .way-total {
            border-radius: 12px;
            padding: 1.1rem 1.4rem;
            margin-top: 1.75rem;
        }

        .way-total .wt-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: .3rem;
        }

        .way-total .wt-value {
            font-size: 1.7rem;
            font-weight: 800;
            font-family: 'IBM Plex Mono', monospace;
            line-height: 1;
        }

        .way-total .wt-sub {
            font-size: .75rem;
            margin-top: .3rem;
            opacity: .65;
        }

        .advantage-callout {
            background: linear-gradient(135deg, #0A1628 0%, #0f2040 100%);
            border-radius: 20px;
            padding: 2.5rem;
            margin-top: 2rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,.08);
        }

        .advantage-callout .ac-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255,255,255,.4);
            margin-bottom: .5rem;
        }

        .advantage-callout .ac-number {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            color: #34d399;
            font-family: 'IBM Plex Mono', monospace;
            line-height: 1;
        }

        .advantage-callout .ac-sub {
            font-size: .9rem;
            color: rgba(255,255,255,.55);
            margin-top: .6rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.65;
        }

        /* ── Story callout (calculator) ─────────────── */
        .story-callout {
            background: linear-gradient(135deg, rgba(37,99,235,.12) 0%, rgba(52,211,153,.08) 100%);
            border: 1px solid rgba(52,211,153,.2);
            border-radius: 16px;
            padding: 1.75rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .story-callout .sc-icon {
            font-size: 2rem;
            flex-shrink: 0;
            line-height: 1;
        }

        .story-callout h5 {
            font-size: .95rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 .35rem;
        }

        .story-callout p {
            font-size: .84rem;
            color: rgba(255,255,255,.55);
            margin: 0;
            line-height: 1.65;
        }

        .story-callout .sc-number {
            font-size: 1.15rem;
            font-weight: 800;
            color: #34d399;
            font-family: 'IBM Plex Mono', monospace;
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
                    <!-- <a href="{{ route('organizations.register') }}" class="nav-link-light">Register Org</a> -->
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('dashboard.login') }}" class="btn-nav-outline">Login</a>
                    <a href="{{ route('organizations.register') }}" class="btn-nav-primary">
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
                        <i class="bi bi-stars"></i> Developer-First · Backed by Mastercard Foundation · East Africa
                    </div>
                    <h1>Stop paying fees<br>to collect payments.<br><span class="accent">Earn 1% p.a. instead.</span></h1>
                    <p style="font-size:.95rem;font-weight:600;color:rgba(255,255,255,.75);margin-bottom:.5rem;margin-top:.75rem;letter-spacing:.01em;">
                        The only <span style="color:#60a5fa;font-weight:700;">Lipa Namba API</span> that pays you to collect payments &mdash; powered by Universal Control Numbers (UCN).
                    </p>
                    <p class="sub">
                        Every other aggregator bills you <strong style="color:#f87171;">up to 2.5%</strong> per transaction. Safari API inverts the model &mdash; we pay <strong style="color:#34d399;">1% p.a.</strong> on every shilling of float you hold. Zero setup fees. Zero transaction charges. The only payment API that puts money back in your hands.
                    </p>
                    <div class="hero-ctas">
                        <a href="{{ route('organizations.register') }}" class="btn-hero-primary">
                            <i class="bi bi-rocket-takeoff"></i> Start Earning Free
                        </a>
                        <a href="{{ route('api.docs') }}" class="btn-hero-outline">
                            <i class="bi bi-book"></i> API Documentation
                        </a>
                    </div>
                    <div class="mt-4 d-flex gap-4 flex-wrap" style="font-size:.82rem;">
                        <span style="color:#34d399;font-weight:700;"><i class="bi bi-check-circle-fill me-1"></i> Zero setup fees</span>
                        <span style="color:#34d399;font-weight:700;"><i class="bi bi-check-circle-fill me-1"></i> Live in &lt;10 minutes</span>
                        <span style="color:rgba(255,255,255,.5);"><i class="bi bi-check-circle-fill me-1" style="color:#34d399;"></i> RESTful JSON API</span>
                    </div>
                    <div class="mt-3 p-3 rounded-3 d-inline-block" style="background:rgba(52,211,153,.06);border:1px solid rgba(52,211,153,.15);font-size:.8rem;color:rgba(255,255,255,.55);">
                        <i class="bi bi-info-circle me-1" style="color:#34d399;"></i>
                        A developer holding <strong style="color:#fff;">TZS 5B float for 7 days</strong> earns <strong style="color:#34d399;">TZS 958,904</strong> &mdash; instead of paying an aggregator <strong style="color:#f87171;">TZS 125M</strong>. Net advantage: <strong style="color:#34d399;">TZS 125,958,904</strong>.
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Feature cards ─────────────────────────────────────────────────────── --}}
    <section class="section-features">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-eyebrow">The Float Arbitrage</div>
                <div class="section-title">Banks earn 2.5% on your float.<br>We give 1% of that back to you.</div>
                <p class="section-sub">You generate the volume. The bank generates the yield. Safari API passes 1% p.a. directly back to you &mdash; every day your float sits in the system. That&rsquo;s the difference between paying for infrastructure and <em>being paid</em> for it.</p>

                {{-- Technical spec strip --}}
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-4 mb-2">
                    <div style="background:rgba(96,165,250,.08);border:1px solid rgba(96,165,250,.2);border-radius:8px;padding:.45rem 1rem;font-size:.78rem;font-family:'IBM Plex Mono',monospace;color:rgba(255,255,255,.75);">
                        <span style="color:#60a5fa;font-weight:700;">Supported Flow:</span>&nbsp; Lipa Namba (UCN) &nbsp;/&nbsp; STK Push &nbsp;/&nbsp; QR Code
                    </div>
                    <div style="background:rgba(52,211,153,.06);border:1px solid rgba(52,211,153,.18);border-radius:8px;padding:.45rem 1rem;font-size:.78rem;font-family:'IBM Plex Mono',monospace;color:rgba(255,255,255,.75);">
                        <span style="color:#34d399;font-weight:700;">Channels:</span>&nbsp; All MNOs (Vodacom · Tigo · Airtel · Halotel) + All Banks
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#ecfdf5;color:#059669;"><i class="bi bi-currency-dollar"></i></div>
                        <h5>Earn 1% p.a. on Float &mdash; Daily</h5>
                        <p>Interest accrues on every shilling of float held on a given day. The more float, the more you earn &mdash; automatically, with zero extra effort.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#fff5f5;color:#dc2626;"><i class="bi bi-slash-circle"></i></div>
                        <h5>Zero Setup Fees. Zero Transaction Fees.</h5>
                        <p>Competitors charge you to get started and then again on every transaction. With Safari API, day one is completely free. Every integration, every time.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-qr-code"></i></div>
                        <h5>Programmable Lipa Namba (UCN)</h5>
                        <p>Generate dynamic or static Lipa Namba &mdash; what we call a <strong>Universal Control Number (UCN)</strong> &mdash; via API. One UCN accepts M-Pesa, Tigo Pesa, Airtel Money, Halotel, and all major banks (CRDB, NMB, NBC, Equity) into a single vault, instantly. Print as QR, push via STK, or embed in any bill.</p>
                        <div class="mt-2 d-flex flex-wrap gap-1">
                            <span style="font-size:.7rem;background:rgba(37,99,235,.15);color:#93c5fd;border-radius:4px;padding:.15rem .5rem;font-family:'IBM Plex Mono',monospace;">UCN</span>
                            <span style="font-size:.7rem;background:rgba(37,99,235,.15);color:#93c5fd;border-radius:4px;padding:.15rem .5rem;font-family:'IBM Plex Mono',monospace;">STK Push</span>
                            <span style="font-size:.7rem;background:rgba(37,99,235,.15);color:#93c5fd;border-radius:4px;padding:.15rem .5rem;font-family:'IBM Plex Mono',monospace;">QR Code</span>
                            <span style="font-size:.7rem;background:rgba(52,211,153,.1);color:#6ee7b7;border-radius:4px;padding:.15rem .5rem;font-family:'IBM Plex Mono',monospace;">All MNOs</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-lightning-charge-fill"></i></div>
                        <h5>Sub-10 Minute Go-Live</h5>
                        <p>Register, get API keys, make your first successful call &mdash; all in under 10 minutes. No paperwork, no waiting, no back-and-forth with a sales team.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-bell-fill"></i></div>
                        <h5>Real-Time Webhooks</h5>
                        <p>Instant payment event notifications to your endpoint. Built-in retry logic, delivery logs, and per-organization configuration. Your system always knows first.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="icon" style="background:#fefce8;color:#ca8a04;"><i class="bi bi-bar-chart-fill"></i></div>
                        <h5>Full-Visibility Dashboard</h5>
                        <p>Live API request logs, float balances, accrued earnings, and credential management &mdash; one dashboard to see exactly how much your integration is making you.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Old Way vs Safari Way ─────────────────────────────────────── --}}
    <section class="section-comparison">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-eyebrow">The Math Doesn&rsquo;t Lie</div>
                <div class="section-title">The Old Way vs. The Safari Way</div>
                <p class="section-sub">This is not a marginal difference. Holding TZS 5 billion in float for 7 days creates a <strong>TZS 125.9 million</strong> swing in your favour.</p>
            </div>

            <div class="row g-4 align-items-stretch">

                {{-- Old way --}}
                <div class="col-md-6">
                    <div class="way-card old-way">
                        <div class="way-card-label" style="background:#fee2e2;color:#dc2626;">&#8987; The Old Way &mdash; Traditional Aggregators</div>
                        <h3 style="color:#7f1d1d;">You pay to collect money.</h3>
  <div class="way-row">
                            <div class="way-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-lg"></i></div>
                            <span style="color:#374151;">Control/Reference Number is <strong>LIMITED </strong>. Customer pays via few mobile money or few Banks only</span>
                        </div>
                        <div class="way-row">
                            <div class="way-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-lg"></i></div>
                            <span style="color:#374151;">Charged <strong>up to 2.5% per transaction</strong> on every shilling you collect</span>
                        </div>
                        <div class="way-row">
                            <div class="way-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-lg"></i></div>
                            <span style="color:#374151;">The aggregator&rsquo;s bank earns <strong>2.5%+ on your float</strong> &mdash; you see none of it</span>
                        </div>
                        <div class="way-row">
                            <div class="way-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-lg"></i></div>
                            <span style="color:#374151;">Setup fees, maintenance fees, and minimum volume penalties</span>
                        </div>
                        <div class="way-row">
                            <div class="way-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-lg"></i></div>
                            <span style="color:#374151;">Single-country rails &mdash; expand later, pay again later</span>
                        </div>

                        <div class="way-total" style="background:#fee2e2;border:1px solid #fca5a5;">
                            <div class="wt-label" style="color:#9b1c1c;">Cost at TZS 5B float, 7 days</div>
                            <div class="wt-value" style="color:#dc2626;">&#8722; TZS 125,000,000</div>
                            <div class="wt-sub" style="color:#7f1d1d;">That&rsquo;s 2.5% of TZS 5B &mdash; paid to the aggregator, not you</div>
                        </div>
                    </div>
                </div>

                {{-- Safari Way --}}
                <div class="col-md-6">
                    <div class="way-card safari-way">
                        <div class="way-card-label" style="background:#dcfce7;color:#15803d;">&#9889; The Safari Way &mdash; You Are the Bank&rsquo;s Client</div>
                        <h3 style="color:#14532d;">Your float works for you.</h3>
  <div class="way-row">
                            <div class="way-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-check-lg"></i></div>
                            <span style="color:#374151;">Control (Lipa) Number<strong> NOT LIMITED.</strong> Customer pay from any bank and ANY Mobile Money Connected to TIPS in Tanzania</span>
                        </div>
                        <div class="way-row">
                            <div class="way-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-check-lg"></i></div>
                            <span style="color:#374151;"><strong>Zero transaction fees.</strong> Every shilling you collect, you keep</span>
                        </div>
                        <div class="way-row">
                            <div class="way-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-check-lg"></i></div>
                            <span style="color:#374151;">We pay you <strong>1% p.a. on float held</strong> &mdash; accrued daily, paid monthly</span>
                        </div>
                        <div class="way-row">
                            <div class="way-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-check-lg"></i></div>
                            <span style="color:#374151;"><strong>Zero setup fees.</strong> Register and integrate in under 10 minutes</span>
                        </div>
                        <div class="way-row">
                            <div class="way-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-check-lg"></i></div>
                            <span style="color:#374151;">One integration &rarr; Tanzania today, <strong>30+ Banks, </strong> All Mobile Money Supported</span>
                        </div>

                        <div class="way-total" style="background:#dcfce7;border:1px solid #86efac;">
                            <div class="wt-label" style="color:#14532d;">Earnings at TZS 5B float, 7 days</div>
                            <div class="wt-value" style="color:#15803d;">&#43; TZS 958,904</div>
                            <div class="wt-sub" style="color:#166534;">1% p.a. on TZS 5B &times; 7 days &mdash; paid directly to you</div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Advantage callout --}}
            <div class="advantage-callout">
                <div class="ac-label">Net Advantage &mdash; Safari API vs Traditional Aggregator</div>
                <div class="ac-number">TZS 125,958,904</div>
                <p class="ac-sub">
                    One integration, 5B TZS float, 7 days. Instead of paying TZS 125M to an aggregator, you earn TZS 958K through Safari API. That&rsquo;s a <strong style="color:#34d399;">125.9 million shilling swing</strong> &mdash; not over a year, but in one week.
                </p>
                <a href="{{ route('organizations.register') }}" class="btn btn-sm mt-3 px-4 py-2"
                    style="background:var(--primary);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:.9rem;">
                    Build the Safari Way &mdash; Free <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- ── Backed By / Authority ───────────────────────────────────────────────── --}}
    <section class="section-backed">
        <div class="container">
            <div class="text-center mb-2">
                <div style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.3);">Who stands behind Safari API</div>
            </div>
            <div class="backed-hr-label"><span>Institutional Backing &amp; Infrastructure Partners</span></div>

            <div class="row g-4">

                {{-- Mastercard Foundation --}}
                <div class="col-lg-6">
                    <div class="institution-card">
                        <div class="inst-seal" style="background:#fff;">
                            <img src="{{ asset('public/images/mastercard-foundation.png') }}" alt="Mastercard Foundation" style="width:52px;height:52px;object-fit:contain;">
                        </div>
                        <div class="inst-role-pill" style="background:rgba(235,149,50,.12);color:#f59e0b;border:1px solid rgba(235,149,50,.25);">
                            <i class="bi bi-patch-check-fill"></i> Funding Institution
                        </div>
                        <h3>Mastercard Foundation</h3>
                        <p class="inst-sub">
                            ShuleSoft Ltd is a <strong style="color:#f59e0b;">Mastercard Foundation&ndash;funded fintech startup</strong>. The Mastercard Foundation is one of the world&rsquo;s largest foundations, with assets of over $40 billion, working across Africa to enable young people access dignified and fulfilling economic opportunities. ShuleSoft&rsquo;s inclusion in their fintech portfolio is a direct reflection of the rigorous vetting and mission alignment required to receive their backing.
                        </p>
                        <div class="inst-stats">
                            <div>
                                <span class="inst-stat-num" style="color:#f59e0b;">$40B+</span>
                                <span class="inst-stat-label">Foundation assets</span>
                            </div>
                            <div>
                                <span class="inst-stat-num" style="color:#f59e0b;">35+</span>
                                <span class="inst-stat-label">African countries served</span>
                            </div>
                            <div>
                                <span class="inst-stat-num" style="color:#f59e0b;">Funded</span>
                                <span class="inst-stat-label">ShuleSoft Ltd</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ecobank --}}
                <div class="col-lg-6">
                    <div class="institution-card">
                        <div class="inst-seal" style="background:#fff;">
                            <img src="{{ asset('public/images/ecobank.png') }}" alt="Ecobank Pan Africa" style="width:52px;height:52px;object-fit:contain;">
                        </div>
                        <div class="inst-role-pill" style="background:rgba(0,168,80,.1);color:#34d399;border:1px solid rgba(0,168,80,.25);">
                            <i class="bi bi-bank2"></i> Banking Infrastructure Partner
                        </div>
                        <h3>Ecobank Pan Africa</h3>
                        <p class="inst-sub">
                            Safari API&rsquo;s banking rails, float settlement, and multi-currency operations are powered by <strong style="color:#34d399;">Ecobank</strong> &mdash; Africa&rsquo;s leading pan-African banking group. With a presence in 35 African countries and one of the largest retail and corporate banking networks on the continent, Ecobank gives Safari API the reach and reliability that only an institutional banking partner of this scale can provide.
                        </p>
                        <div class="inst-stats">
                            <div>
                                <span class="inst-stat-num" style="color:#34d399;">35</span>
                                <span class="inst-stat-label">African countries</span>
                            </div>
                            <div>
                                <span class="inst-stat-num" style="color:#34d399;">600+</span>
                                <span class="inst-stat-label">Branches &amp; offices</span>
                            </div>
                            <div>
                                <span class="inst-stat-num" style="color:#34d399;">#1</span>
                                <span class="inst-stat-label">Pan-African bank</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ShuleSoft builder strip --}}
            <div class="builder-strip">
                <div class="bs-icon" style="background:#fff;">
                    <img src="{{ asset('public/images/shulesoft.png') }}" alt="ShuleSoft Ltd" style="width:40px;height:40px;object-fit:contain;">
                </div>
                <div class="flex-grow-1">
                    <h5>Built by ShuleSoft Ltd &mdash; Tanzania&rsquo;s Developer-First Fintech</h5>
                    <p>ShuleSoft Ltd is the parent company and builder of Safari API. Founded in Tanzania with a mission to make financial infrastructure accessible to every developer across Africa. Backed by the Mastercard Foundation. Powered by Ecobank banking rails. Built for Africa.</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('organizations.register') }}" class="btn btn-sm px-4 py-2"
                        style="background:var(--primary);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:.875rem;white-space:nowrap;">
                        Start Building <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ── How it works ───────────────────────────────────────────────────────── --}}
    <section class="section-how">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-eyebrow">How It Works</div>
                <div class="section-title">Three steps from zero to earning.</div>
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
                        <h6>Float Earns For You</h6>
                        <p>Every day your float sits in the system, you earn 1% p.a. &mdash; accrued daily, paid monthly. Your integration doesn&rsquo;t just work. It generates revenue.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('organizations.register') }}" class="btn px-4 py-2"
                    style="background:var(--primary);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:.95rem;">
                    <i class="bi bi-rocket-takeoff me-1"></i> Start Earning Free
                </a>
            </div>
        </div>
    </section>

    {{-- ── Commission Calculator ──────────────────────────────────────────────── --}}
    <section class="section-calc" id="pricing">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-eyebrow" style="color:#93c5fd;">Float Earnings Calculator</div>
                <div class="section-title" style="color:#fff;">Calculate your float advantage.</div>
                <p class="section-sub" style="color:rgba(255,255,255,.5);">Adjust float volume and holding duration. Earnings accrue at <strong style="color:#34d399;">1% p.a.</strong> on the balance held each day.</p>
            </div>

            {{-- Story callout --}}
            <div class="story-callout">
                <div class="sc-icon">&#128200;</div>
                <div>
                    <h5>How one integration turned TZS 125M in costs into TZS 958K in earnings &mdash; in a week.</h5>
                    <p>
                        A Tanzanian startup holding <strong style="color:#fff;">TZS 5B in float</strong> over 7 days faced two paths. Path A: use a traditional aggregator, pay <span class="sc-number" style="color:#f87171;">TZS 125,000,000</span> in fees. Path B: use Safari API, earn <span class="sc-number">TZS 958,904</span> in interest. The net swing: <span class="sc-number">TZS 125,958,904</span> in 7 days. Use the calculator below to run your own numbers.
                    </p>
                </div>
            </div>
            <div class="calc-card">
                <div class="mb-4">
                    <label class="d-flex justify-content-between">
                        <span>Float Volume Held</span>
                        <span style="color:#60a5fa;font-weight:700;font-family:'IBM Plex Mono',monospace;"
                            id="volumeDisplay">TZS 5,000,000,000</span>
                    </label>
                    <input type="range" class="calc-range mt-2" id="volumeSlider" min="1000000000" max="50000000000"
                        step="500000000" value="5000000000">
                    <div class="d-flex justify-content-between mt-1"
                        style="font-size:.7rem;color:rgba(255,255,255,.3);">
                        <span>TZS 1B</span><span>TZS 50B</span>
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
                    <div class="result-label">Estimated earnings on daily float</div>
                    <div class="result-value" id="commissionResult">TZS 0</div>
                    <div class="result-sub" id="calcSub">at 1% p.a. on TZS 5,000,000,000 held for 7 days</div>
                </div>
                <div class="calc-compare">
                    <div class="comp-item">
                        <div class="comp-label">Your earnings (1% p.a.)</div>
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

    {{-- Partner Bar --}}
    <div class="footer-partner-bar">
        <div class="container">
            <div class="fpar-label text-center">Backed &amp; Powered By</div>
            <div class="row g-3 justify-content-center">
                <div class="col-sm-6 col-lg-4">
                    <div class="fpar-card">
                        <div class="fpar-icon" style="background:#fff;">
                            <img src="{{ asset('public/images/mastercard-foundation.png') }}" alt="Mastercard Foundation" style="width:34px;height:34px;object-fit:contain;">
                        </div>
                        <div>
                            <span class="fpar-name">Mastercard Foundation</span>
                            <span class="fpar-role">Funding Institution &mdash; ShuleSoft Ltd is a Mastercard Foundation&ndash;funded startup</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="fpar-card">
                        <div class="fpar-icon" style="background:#fff;">
                            <img src="{{ asset('public/images/ecobank.png') }}" alt="Ecobank Pan Africa" style="width:34px;height:34px;object-fit:contain;">
                        </div>
                        <div>
                            <span class="fpar-name">Ecobank Pan Africa</span>
                            <span class="fpar-role">Banking Infrastructure Partner &mdash; 35 African countries</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="fpar-card">
                        <div class="fpar-icon" style="background:#fff;">
                            <img src="{{ asset('public/images/shulesoft.png') }}" alt="ShuleSoft Ltd" style="width:34px;height:34px;object-fit:contain;">
                        </div>
                        <div>
                            <span class="fpar-name">ShuleSoft Ltd</span>
                            <span class="fpar-role">Parent Company &amp; Platform Builder &mdash; Tanzania</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Main --}}
    <div class="footer-main">
        <div class="container">
            <div class="row g-5">

                {{-- Brand --}}
                <div class="col-lg-4">
                    <a href="{{ route('landing') }}" class="footer-brand-name">
                        <i class="bi bi-lightning-charge-fill"></i> Safari API
                    </a>
                    <p class="footer-tagline">
                        Universal payment infrastructure for Tanzania &amp; East Africa. One API. Every bank. Every mobile money. Built by ShuleSoft Ltd.
                    </p>
                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <span style="font-size:.72rem;background:rgba(245,158,11,.1);color:#f59e0b;border:1px solid rgba(245,158,11,.2);border-radius:6px;padding:.25rem .65rem;font-weight:700;">Mastercard Foundation Funded</span>
                    </div>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <span style="font-size:.72rem;background:rgba(0,168,80,.1);color:#34d399;border:1px solid rgba(0,168,80,.2);border-radius:6px;padding:.25rem .65rem;font-weight:700;">Powered by Ecobank</span>
                    </div>
                </div>

                {{-- Product --}}
                <div class="col-6 col-lg-2">
                    <div class="footer-col-heading">Product</div>
                    <a href="{{ route('api.docs') }}" class="footer-link">API Documentation</a>
                    <a href="#pricing" class="footer-link">Pricing &amp; Calculator</a>
                    <a href="{{ route('organizations.register') }}" class="footer-link">Get Started</a>
                    <a href="{{ route('dashboard.login') }}" class="footer-link">Login</a>
                </div>

                {{-- Company --}}
                <div class="col-6 col-lg-3">
                    <div class="footer-col-heading">Company</div>
                    <span class="footer-link" style="cursor:default;">ShuleSoft Ltd &mdash; Tanzania</span>
                    <span class="footer-link" style="cursor:default;">Mastercard Foundation Grantee</span>
                    <span class="footer-link" style="cursor:default;">Ecobank Banking Partner</span>
                    <a href="mailto:hello@shulesoft.com" class="footer-link">hello@shulesoft.com</a>
                </div>

                {{-- Legal --}}
                <div class="col-6 col-lg-3">
                    <div class="footer-col-heading">Legal</div>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="footer-link">Terms &amp; Conditions</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#liquidityModal" class="footer-link">Liquidity Reward Policy (Tiered)</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#floatModal" class="footer-link">Float Definition &amp; Rules</a>
                    <a href="mailto:legal@shulesoft.com" class="footer-link">legal@shulesoft.com</a>
                </div>

            </div>
        </div>
    </div>

    {{-- Footer Bottom --}}
    <div class="footer-bottom">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>&copy; {{ date('Y') }} ShuleSoft Ltd. All rights reserved. Safari API is a registered product of ShuleSoft Ltd, Tanzania.</span>
                <span>Governed by the laws of the United Republic of Tanzania</span>
            </div>
        </div>
    </div>

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

            // 1% per annum accrued daily: commission = volume * 0.01 / 365 * days
            const commission = volume * 0.01 / 365 * days;
            const aggFee = volume * 0.025;
            const netAdv = commission + aggFee;

            document.getElementById('volumeDisplay').textContent = fmt(volume);
            document.getElementById('daysDisplay').textContent = days + (days === 1 ? ' day' : ' days');
            document.getElementById('commissionResult').textContent = fmt(commission);
            document.getElementById('calcSub').textContent =
                'at 1% p.a. on ' + fmt(volume) + ' held for ' + days + (days === 1 ? ' day' : ' days');
            document.getElementById('earnValue').textContent = '+ ' + fmt(commission);
            document.getElementById('aggFee').textContent = '− ' + fmt(aggFee);
            document.getElementById('netAdv').textContent = fmt(netAdv);
        }

        volumeSlider.addEventListener('input', updateCalc);
        daysSlider.addEventListener('input', updateCalc);
        updateCalc();
    </script>

    {{-- ── Terms & Conditions Modal ──────────────────────────────────────────── --}}
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="background:#0f172a;color:rgba(255,255,255,.82);border:1px solid rgba(255,255,255,.1);">
                <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
                    <h5 class="modal-title" id="termsModalLabel" style="color:#fff;font-weight:700;">
                        <i class="bi bi-shield-check me-2" style="color:#60a5fa;"></i> <span id="termsModalTitleText">Safari API &mdash; Terms &amp; Conditions</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="font-size:.875rem;line-height:1.8;">

                    <p style="color:rgba(255,255,255,.38);font-size:.78rem;">Last updated: {{ date('F j, Y') }} &nbsp;|&nbsp; Governed by the laws of the United Republic of Tanzania &nbsp;|&nbsp; Version 2.0</p>

                    {{-- ── §1 Platform Operator ── --}}
                    <h6 id="tc-section-general" style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">1. Platform Operator &amp; Nature of Service</h6>
                    <p>Safari API is a technology service product owned and operated by <strong style="color:#fff;">ShuleSoft Ltd</strong>, a company incorporated under the laws of the United Republic of Tanzania and a Mastercard Foundation–funded fintech company building payment infrastructure for Africa.</p>
                    <p><strong style="color:#f87171;">Important:</strong> ShuleSoft Ltd is a <em>technology service provider</em>, not a bank, deposit-taking institution, or investment scheme. <strong style="color:#fff;">All developer float is held exclusively by Ecobank Tanzania</strong> under Ecobank's institutional banking licence. ShuleSoft Ltd does not hold, control, or guarantee any deposited funds.</p>

                    {{-- ── §2 The Liquidity Reward ── --}}
                    <h6 id="tc-section-liquidity-reward" style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">2. The Liquidity Reward Programme</h6>
                    <p>ShuleSoft Ltd operates a <strong style="color:#fff;">"Liquidity Reward"</strong> (also referred to as a "Platform Rebate" or "Channel Partner Commission") — a technology incentive paid to eligible Third-Party Integrators based on the average daily float volume they facilitate through the Safari API. This programme is <strong style="color:#fff;">not an interest-bearing product</strong> and does not constitute a savings scheme or investment product under any applicable Tanzanian financial regulation.</p>

                    <h6 style="color:#34d399;font-weight:700;margin-top:1.25rem;">2a. Tiered Reward Rate Structure</h6>
                    <p>The Liquidity Reward is calculated on the <strong style="color:#fff;">average daily float balance</strong> recorded in the developer's integration account at the close of each business day. Rates apply on a <em>tiered basis</em> as follows:</p>

                    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:10px;overflow:hidden;margin:1rem 0;">
                        <table style="width:100%;border-collapse:collapse;font-size:.84rem;">
                            <thead>
                                <tr style="background:rgba(96,165,250,.12);">
                                    <th style="padding:.65rem 1rem;text-align:left;color:#60a5fa;font-weight:700;border-bottom:1px solid rgba(255,255,255,.08);">Daily Average Float Tier</th>
                                    <th style="padding:.65rem 1rem;text-align:center;color:#60a5fa;font-weight:700;border-bottom:1px solid rgba(255,255,255,.08);">Annual Reward Rate</th>
                                    <th style="padding:.65rem 1rem;text-align:left;color:#60a5fa;font-weight:700;border-bottom:1px solid rgba(255,255,255,.08);">Applies To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                                    <td style="padding:.6rem 1rem;color:rgba(255,255,255,.6);">TZS 0 — TZS 499,999,999</td>
                                    <td style="padding:.6rem 1rem;text-align:center;"><span style="color:#f87171;font-weight:700;font-family:'IBM Plex Mono',monospace;">0.00%</span></td>
                                    <td style="padding:.6rem 1rem;color:rgba(255,255,255,.5);font-size:.8rem;">Below threshold — no reward accrues</td>
                                </tr>
                                <tr style="border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02);">
                                    <td style="padding:.6rem 1rem;color:rgba(255,255,255,.85);">TZS 500,000,000 — TZS 999,999,999</td>
                                    <td style="padding:.6rem 1rem;text-align:center;"><span style="color:#fbbf24;font-weight:700;font-family:'IBM Plex Mono',monospace;">0.50% p.a.</span></td>
                                    <td style="padding:.6rem 1rem;color:rgba(255,255,255,.5);font-size:.8rem;">On the full balance in this tier</td>
                                </tr>
                                <tr>
                                    <td style="padding:.6rem 1rem;color:#fff;font-weight:600;">TZS 1,000,000,000 and above</td>
                                    <td style="padding:.6rem 1rem;text-align:center;"><span style="color:#34d399;font-weight:700;font-family:'IBM Plex Mono',monospace;">1.00% p.a.</span></td>
                                    <td style="padding:.6rem 1rem;color:rgba(255,255,255,.5);font-size:.8rem;">On the full balance at this tier</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p style="color:rgba(255,255,255,.55);font-size:.82rem;"><i class="bi bi-info-circle me-1" style="color:#fbbf24;"></i> <strong style="color:#fbbf24;">Example:</strong> A developer holding TZS 2B in daily average float earns: TZS 2,000,000,000 × 1% ÷ 365 = <strong>TZS 54,795/day</strong>, paid monthly.</p>

                    <h6 style="color:#34d399;font-weight:700;margin-top:1.25rem;">2b. Accrual &amp; Payout Rules</h6>
                    <ul>
                        <li><strong style="color:#fff;">Accrual Basis:</strong> Rewards accrue daily based on the float balance present at the close of each business day. Only float physically settled and confirmed by Ecobank qualifies.</li>
                        <li><strong style="color:#fff;">Daily Formula:</strong> Daily accrual = (Qualifying Float Balance × Applicable Rate) ÷ 365.</li>
                        <li><strong style="color:#fff;">Payout Schedule:</strong> Accrued rewards are aggregated monthly and paid into the developer's registered ShuleSoft wallet within 7 business days after the close of each calendar month.</li>
                        <li><strong style="color:#fff;">No Guaranteed Minimum:</strong> ShuleSoft Ltd does not guarantee a minimum monthly reward. Days where the float falls below the TZS 500,000,000 threshold earn zero reward for that day.</li>
                        <li><strong style="color:#fff;">Rate Changes:</strong> ShuleSoft Ltd reserves the right to revise reward tiers with <strong>30 days' written notice</strong> via email to the registered account. Continued API use after the effective date constitutes acceptance.</li>
                    </ul>

                    {{-- ── §3 Eligibility ── --}}
                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">3. Eligibility — Who Qualifies for the Liquidity Reward</h6>

                    <h6 style="color:#34d399;font-weight:700;margin-top:1.25rem;">3a. Eligible: Third-Party Integrators (Independent Software Vendors)</h6>
                    <p>The Liquidity Reward is a <strong style="color:#fff;">Channel Partner Commission</strong> designed exclusively for <strong style="color:#fff;">registered developer organisations</strong> that build and deploy Safari API integrations on behalf of <strong style="color:#fff;">third-party client organisations</strong>. This category is also referred to as <em>"Independent Software Vendors (ISVs)"</em> or <em>"Aggregators."</em></p>
                    <p>To be eligible, the developer organisation must:</p>
                    <ul>
                        <li>Be a <strong style="color:#fff;">legally registered business entity</strong> (company, partnership, or registered cooperative) under Tanzanian law or the laws of a recognised jurisdiction;</li>
                        <li>Hold an active, verified Safari API developer account with completed KYB (Know Your Business) verification;</li>
                        <li>Be integrating Safari API for the benefit of <strong style="color:#fff;">one or more separate, independent client organisations</strong> — not for its own internal operations;</li>
                        <li>Maintain compliance with all AML, KYC, and regulatory requirements as described in Section 6 of these Terms; and</li>
                        <li>Not be in breach of any provision of these Terms &amp; Conditions.</li>
                    </ul>

                    <h6 style="color:#f87171;font-weight:700;margin-top:1.25rem;">3b. Not Eligible: Direct-Integration Organisations</h6>
                    <p>If an organisation integrates Safari API <strong style="color:#fff;">for its own internal operations</strong> (e.g., a school collecting school fees for itself, a hospital collecting patient payments for itself), that organisation — and any developers employed by or contracted to that organisation for that integration — are <strong style="color:#f87171;">not eligible</strong> for the Liquidity Reward.</p>
                    <p>Direct-integration organisations benefit from <strong style="color:#fff;">zero transaction fees</strong> and <strong style="color:#fff;">zero setup fees</strong> as their incentive. The Liquidity Reward shall not be paid on float generated by an organisation's own internal integration.</p>

                    <h6 style="color:#f87171;font-weight:700;margin-top:1.25rem;">3c. Not Eligible: Individual Developers (Natural Persons)</h6>
                    <p>The Liquidity Reward is payable only to <strong style="color:#fff;">registered legal entities</strong>, not to individual natural persons. Specifically:</p>
                    <ul>
                        <li>A <strong style="color:#fff;">developer employed by, or contracted exclusively to, a single client organisation</strong> for the purpose of that organisation's own integration is not eligible for the Liquidity Reward, regardless of whether that developer holds a personal Safari API account.</li>
                        <li>Freelancers or contractors who integrate Safari API solely for one end-organisation's own use are treated as Direct-Integration agents and are not eligible.</li>
                        <li>Individual registration of a Safari API account does not, by itself, confer eligibility for the Liquidity Reward.</li>
                    </ul>
                    <p style="background:rgba(248,113,113,.08);border-left:3px solid #f87171;padding:.75rem 1rem;border-radius:0 6px 6px 0;color:rgba(255,255,255,.75);font-size:.84rem;"><i class="bi bi-exclamation-triangle me-2" style="color:#f87171;"></i> <strong style="color:#f87171;">Conflict of Interest Notice:</strong> Paying a Liquidity Reward directly to a developer who is employed by a client organisation creates a conflict of interest for that employee. ShuleSoft Ltd exclusively recognises the <em>channel partner model</em> to prevent such conflicts. Misrepresentation of integration type to claim rewards is a material breach of these Terms and may result in immediate account termination and recovery of any rewards paid.</p>

                    {{-- ── §4 Float Definition ── --}}
                    <h6 id="tc-section-float-definition" style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">4. Float Definition</h6>
                    <p>"Float" means collected funds received through the Safari UCN API — via bank transfer, mobile money, or other authorised payment channels — that are held in the developer's settlement account at Ecobank Tanzania pending disbursement to the end-client organisation. Float is <strong style="color:#fff;">not a deposit product</strong> and is not covered by any national deposit protection or guarantee scheme.</p>

                    {{-- ── §5 Banking Partner ── --}}
                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">5. Banking Infrastructure Partner</h6>
                    <p>Float settlement, custody, and multi-country banking rails are operated exclusively in partnership with <strong style="color:#fff;">Ecobank Pan Africa</strong> (35 countries, 600+ branches). All developer float is held in institutional accounts at Ecobank Tanzania in accordance with Ecobank's banking terms. ShuleSoft Ltd acts solely as a technology layer and is not a licensed deposit-taking institution, bank, or financial institution under the Bank of Tanzania Act.</p>

                    {{-- ── §6 AML / Compliance ── --}}
                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">6. Anti-Money Laundering &amp; Compliance</h6>
                    <p>Safari API, as a technology gateway to Ecobank Tanzania, operates as a <strong style="color:#fff;">Reporting Entity</strong> under the Anti-Money Laundering Act (Cap. 423) and the Financial Intelligence Unit (FIU) of Tanzania regulations. The following apply without exception:</p>
                    <ul>
                        <li>ShuleSoft Ltd reserves the right to perform <strong style="color:#fff;">KYC (Know Your Customer)</strong> and <strong style="color:#fff;">KYB (Know Your Business)</strong> checks on any developer organisation at registration and periodically thereafter.</li>
                        <li>ShuleSoft Ltd is obligated to <strong style="color:#fff;">immediately suspend or terminate</strong> any developer account if flagged by the Bank of Tanzania (BoT), the Financial Intelligence Unit (FIU), or any other competent authority for suspected money laundering, fraud, terrorist financing, or suspicious transaction patterns.</li>
                        <li>Developers must not use the API to process proceeds of crime, circumvent foreign exchange controls, or structure transactions to avoid reporting thresholds.</li>
                        <li>ShuleSoft Ltd shall report suspicious transactions to the FIU as required by law and shall cooperate fully with any regulatory investigations, without prior notice to the developer where disclosure is legally prohibited.</li>
                    </ul>

                    {{-- ── §7 Data Protection ── --}}
                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">7. Data Protection — Tanzania PDPA 2022</h6>
                    <p>All personal data processed through Safari API is governed by the <strong style="color:#fff;">Personal Data Protection Act (PDPA) 2022</strong> of Tanzania and enforced by the <strong style="color:#fff;">Personal Data Protection Commission (PDPC)</strong>.</p>
                    <ul>
                        <li><strong style="color:#fff;">Data Roles:</strong> For each integration, the developer organisation acts as the <em>Data Processor</em> and Safari API / Ecobank Tanzania acts as the <em>Data Controller</em> under the PDPA 2022 framework.</li>
                        <li><strong style="color:#fff;">Data Processing Agreement (DPA):</strong> By registering and using Safari API, the developer organisation agrees to the Data Processing Agreement embedded in these Terms, whereby it shall process personal data only on documented instructions from ShuleSoft Ltd and shall implement appropriate technical and organisational safeguards.</li>
                        <li><strong style="color:#fff;">Data Residency:</strong> All financial and personal data relating to Tanzanian customers shall be stored, processed, or mirrored within the United Republic of Tanzania in compliance with PDPA 2022 data localisation requirements.</li>
                        <li><strong style="color:#fff;">Breach Notification:</strong> The developer must notify ShuleSoft Ltd within 24 hours of becoming aware of any personal data breach, and ShuleSoft Ltd shall notify the PDPC within the statutory timeframe.</li>
                    </ul>

                    {{-- ── §8 IP ── --}}
                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">8. Intellectual Property</h6>
                    <p>All API endpoints, documentation, SDKs, dashboard interfaces, and associated software are the intellectual property of <strong style="color:#fff;">ShuleSoft Ltd</strong>. Developers are granted a limited, non-exclusive, non-transferable, revocable licence to use the Safari API solely for the purpose of integrating authorised payment collection into their client applications. No rights in ShuleSoft Ltd's IP are transferred by these Terms.</p>

                    {{-- ── §9 Liability ── --}}
                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">9. Limitation of Liability</h6>
                    <p>ShuleSoft Ltd shall not be liable for any indirect, incidental, special, or consequential loss arising from: API downtime, payment processing delays, float reward shortfalls due to sub-threshold balances, regulatory changes by the Bank of Tanzania that affect float reward rates, or actions and delays attributable to Ecobank as banking partner. Total aggregate liability of ShuleSoft Ltd in any calendar month shall not exceed the total Liquidity Reward earned by the developer in that same month.</p>

                    {{-- ── §10 Governing Law ── --}}
                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.75rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">10. Governing Law &amp; Dispute Resolution</h6>
                    <p>These Terms &amp; Conditions are governed by and construed in accordance with the laws of the <strong style="color:#fff;">United Republic of Tanzania</strong>. Any dispute arising from or in connection with these Terms shall first be subject to good-faith negotiation for 30 days. Failing resolution, the dispute shall be referred to binding arbitration under the rules of the <strong style="color:#fff;">Tanzania Arbitration Centre</strong>, conducted in Dar es Salaam in the English language.</p>

                    <p class="mt-4" style="color:rgba(255,255,255,.32);font-size:.78rem;">By registering and using Safari API, you confirm that you are authorised to bind your organisation to these Terms, that you have read and understood them in full, and that your organisation qualifies as an eligible Third-Party Integrator under Section 3. For legal enquiries, contact <a href="mailto:legal@shulesoft.com" style="color:#60a5fa;">legal@shulesoft.com</a>.</p>

                </div>
                <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('dashboard.register') }}" class="btn btn-sm btn-primary">Register My Organisation →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Liquidity Reward Policy Modal ───────────────────────────────── --}}
    <div class="modal fade" id="liquidityModal" tabindex="-1" aria-labelledby="liquidityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="background:#0f172a;color:rgba(255,255,255,.82);border:1px solid rgba(255,255,255,.1);">
                <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
                    <h5 class="modal-title" id="liquidityModalLabel" style="color:#fff;font-weight:700;">
                        <i class="bi bi-cash-coin me-2" style="color:#34d399;"></i> Liquidity Reward Policy (Tiered)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="font-size:.875rem;line-height:1.8;">

                    <p style="color:rgba(255,255,255,.38);font-size:.78rem;">Last updated: {{ date('F j, Y') }} &nbsp;|&nbsp; ShuleSoft Ltd, Tanzania &nbsp;|&nbsp; Version 2.0</p>

                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.25rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">What is the Liquidity Reward?</h6>
                    <p>ShuleSoft Ltd operates a <strong style="color:#fff;">“Liquidity Reward”</strong> — a technology incentive (also called a “Platform Rebate” or “Channel Partner Commission”) paid to eligible Third-Party Integrators based on the average daily float they facilitate through the Safari API. This is <strong style="color:#fff;">not an interest-bearing product</strong> and does not constitute a savings scheme or investment product under Tanzanian law.</p>

                    <h6 style="color:#34d399;font-weight:700;margin-top:1.5rem;">Tiered Rate Structure</h6>
                    <p>Rewards are calculated on the <strong style="color:#fff;">average daily float balance</strong> at the close of each business day, applied on a tiered basis:</p>

                    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:10px;overflow:hidden;margin:1rem 0;">
                        <table style="width:100%;border-collapse:collapse;font-size:.84rem;">
                            <thead>
                                <tr style="background:rgba(96,165,250,.12);">
                                    <th style="padding:.65rem 1rem;text-align:left;color:#60a5fa;font-weight:700;border-bottom:1px solid rgba(255,255,255,.08);">Daily Average Float Tier</th>
                                    <th style="padding:.65rem 1rem;text-align:center;color:#60a5fa;font-weight:700;border-bottom:1px solid rgba(255,255,255,.08);">Annual Reward Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                                    <td style="padding:.6rem 1rem;color:rgba(255,255,255,.6);">TZS 0 — TZS 499,999,999</td>
                                    <td style="padding:.6rem 1rem;text-align:center;"><span style="color:#f87171;font-weight:700;font-family:'IBM Plex Mono',monospace;">0.00% &mdash; No reward accrues</span></td>
                                </tr>
                                <tr style="border-bottom:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02);">
                                    <td style="padding:.6rem 1rem;color:rgba(255,255,255,.85);">TZS 500,000,000 — TZS 999,999,999</td>
                                    <td style="padding:.6rem 1rem;text-align:center;"><span style="color:#fbbf24;font-weight:700;font-family:'IBM Plex Mono',monospace;">0.50% p.a.</span></td>
                                </tr>
                                <tr>
                                    <td style="padding:.6rem 1rem;color:#fff;font-weight:600;">TZS 1,000,000,000 and above</td>
                                    <td style="padding:.6rem 1rem;text-align:center;"><span style="color:#34d399;font-weight:700;font-family:'IBM Plex Mono',monospace;">1.00% p.a.</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p style="color:rgba(255,255,255,.55);font-size:.82rem;"><i class="bi bi-info-circle me-1" style="color:#fbbf24;"></i> <strong style="color:#fbbf24;">Example:</strong> TZS 2B average daily float &times; 1% &divide; 365 = <strong style="color:#fff;">TZS 54,795/day</strong>, paid monthly.</p>

                    <h6 style="color:#34d399;font-weight:700;margin-top:1.5rem;">Accrual &amp; Payout Rules</h6>
                    <ul>
                        <li><strong style="color:#fff;">Accrual:</strong> Rewards accrue daily on the float balance confirmed by Ecobank at close of business. Daily formula: <code style="background:rgba(255,255,255,.07);padding:.1rem .4rem;border-radius:4px;">(Float &times; Rate) &divide; 365</code>.</li>
                        <li><strong style="color:#fff;">Payout:</strong> Aggregated monthly and paid into the developer’s ShuleSoft wallet within 7 business days after month-close.</li>
                        <li><strong style="color:#fff;">No Guaranteed Minimum:</strong> Days where float falls below TZS 500,000,000 earn zero reward for that day.</li>
                        <li><strong style="color:#fff;">Rate Changes:</strong> 30 days’ written notice required for any tier revision. Continued API use after the effective date constitutes acceptance.</li>
                    </ul>

                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.5rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">Who is Eligible?</h6>

                    <p style="background:rgba(52,211,153,.06);border-left:3px solid #34d399;padding:.75rem 1rem;border-radius:0 6px 6px 0;"><i class="bi bi-check-circle-fill me-2" style="color:#34d399;"></i> <strong style="color:#34d399;">Eligible:</strong> Registered developer <strong style="color:#fff;">organisations (legal entities)</strong> that integrate Safari API on behalf of <strong style="color:#fff;">third-party client organisations</strong> (Independent Software Vendors / Aggregators).</p>

                    <p style="background:rgba(248,113,113,.08);border-left:3px solid #f87171;padding:.75rem 1rem;border-radius:0 6px 6px 0;margin-top:.75rem;"><i class="bi bi-x-circle-fill me-2" style="color:#f87171;"></i> <strong style="color:#f87171;">Not Eligible:</strong> Organisations integrating for their <strong style="color:#fff;">own internal use</strong>, or individual developers (natural persons). Employed developers of a client organisation cannot receive this reward. See the full Terms &amp; Conditions for the complete eligibility rules.</p>

                    <p class="mt-4" style="color:rgba(255,255,255,.32);font-size:.78rem;">Full terms at <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#termsModal" style="color:#60a5fa;">Terms &amp; Conditions</a>. Enquiries: <a href="mailto:legal@shulesoft.com" style="color:#60a5fa;">legal@shulesoft.com</a></p>

                </div>
                <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('dashboard.register') }}" class="btn btn-sm btn-primary">Check My Eligibility →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Float Definition Modal ───────────────────────────────────────── --}}
    <div class="modal fade" id="floatModal" tabindex="-1" aria-labelledby="floatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="background:#0f172a;color:rgba(255,255,255,.82);border:1px solid rgba(255,255,255,.1);">
                <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
                    <h5 class="modal-title" id="floatModalLabel" style="color:#fff;font-weight:700;">
                        <i class="bi bi-bank2 me-2" style="color:#60a5fa;"></i> Float Definition &amp; Rules
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="font-size:.875rem;line-height:1.8;">

                    <p style="color:rgba(255,255,255,.38);font-size:.78rem;">Last updated: {{ date('F j, Y') }} &nbsp;|&nbsp; ShuleSoft Ltd, Tanzania &nbsp;|&nbsp; Version 2.0</p>

                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.25rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">What is “Float”?</h6>
                    <p><strong style="color:#fff;">“Float”</strong> means collected funds received through the Safari UCN API — via bank transfer, mobile money (M-Pesa, Tigo Pesa, Airtel Money, Halotel), or other authorised payment channels — that are held in the developer’s settlement account at <strong style="color:#fff;">Ecobank Tanzania</strong> pending disbursement to the end-client organisation.</p>

                    <div style="background:rgba(248,113,113,.06);border:1px solid rgba(248,113,113,.15);border-radius:10px;padding:1rem 1.25rem;margin:1rem 0;">
                        <p style="margin:0;color:rgba(255,255,255,.75);"><i class="bi bi-exclamation-triangle me-2" style="color:#f87171;"></i> Float is <strong style="color:#f87171;">not a deposit product</strong> and is not covered by any national deposit protection or guarantee scheme. ShuleSoft Ltd does not hold, control, or guarantee any float.</p>
                    </div>

                    <h6 style="color:#34d399;font-weight:700;margin-top:1.5rem;">Qualifying Float Rules</h6>
                    <ul>
                        <li><strong style="color:#fff;">Confirmation Required:</strong> Only float that is physically settled and confirmed by Ecobank Tanzania at the close of a business day qualifies for that day’s reward accrual.</li>
                        <li><strong style="color:#fff;">Minimum Threshold:</strong> Daily float below <strong style="color:#fff;">TZS 500,000,000</strong> earns zero reward for that day (0.00% tier).</li>
                        <li><strong style="color:#fff;">Pending Transactions:</strong> Payments received but not yet settled by Ecobank do not count toward the qualifying float balance.</li>
                        <li><strong style="color:#fff;">Disbursed Float:</strong> Once float is disbursed to the end-client organisation, it no longer qualifies for accrual from the disbursement date.</li>
                        <li><strong style="color:#fff;">Multi-Currency:</strong> For integrations outside Tanzania, float is converted to TZS at the Ecobank spot rate on the date of settlement for reward calculation purposes.</li>
                    </ul>

                    <h6 style="color:#60a5fa;font-weight:700;margin-top:1.5rem;border-bottom:1px solid rgba(96,165,250,.2);padding-bottom:.4rem;">Banking Infrastructure Partner</h6>
                    <p>All float settlement, custody, and multi-country banking rails are operated exclusively through <strong style="color:#fff;">Ecobank Pan Africa</strong> — present in 35 African countries with 600+ branches. Float held in institutional accounts at Ecobank Tanzania under Ecobank’s banking licence. ShuleSoft Ltd is a technology layer only and is not a licensed bank or deposit-taking institution under the Bank of Tanzania Act.</p>

                    <div style="background:rgba(52,211,153,.05);border:1px solid rgba(52,211,153,.15);border-radius:10px;padding:1rem 1.25rem;margin-top:1rem;">
                        <p style="margin:0;color:rgba(255,255,255,.7);font-size:.82rem;"><i class="bi bi-shield-check me-2" style="color:#34d399;"></i> Developer funds are segregated in institutional accounts. ShuleSoft Ltd cannot access or move developer float independently of Ecobank’s settlement processes.</p>
                    </div>

                    <p class="mt-4" style="color:rgba(255,255,255,.32);font-size:.78rem;">For the complete reward rate structure, see the <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#liquidityModal" style="color:#60a5fa;">Liquidity Reward Policy</a>. Full platform terms at <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#termsModal" style="color:#60a5fa;">Terms &amp; Conditions</a>. Enquiries: <a href="mailto:legal@shulesoft.com" style="color:#60a5fa;">legal@shulesoft.com</a>.</p>

                </div>
                <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('dashboard.register') }}" class="btn btn-sm btn-primary">Start Integrating →</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
