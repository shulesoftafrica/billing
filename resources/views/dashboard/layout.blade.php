<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') – Safari API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:       #2563EB;
            --primary-dark:  #1D4ED8;
            --navy:          #0A1628;
            --navy-light:    #0f2040;
            --sidebar-w:     260px;
            --topbar-h:      64px;
            --surface:       #F8FAFC;
            --border:        #E2E8F0;
            --text:          #1E293B;
            --muted:         #64748B;
            --success:       #10B981;
            --danger:        #EF4444;
            --warning:       #F59E0B;
            --radius:        8px;
            --shadow:        0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--text);
            margin: 0;
            min-height: 100vh;
        }

        /* ── Sidebar ──────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--navy);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: transform .25s ease;
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand .brand-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.3px;
        }
        .sidebar-brand .brand-sub {
            font-size: .72rem;
            color: rgba(255,255,255,.45);
            font-family: 'IBM Plex Mono', monospace;
        }
        .org-badge {
            display: inline-block;
            margin-top: .5rem;
            background: rgba(37,99,235,.25);
            color: #93c5fd;
            font-size: .72rem;
            padding: .2rem .55rem;
            border-radius: 20px;
            font-family: 'IBM Plex Mono', monospace;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
        }
        .sidebar-nav .nav-label {
            font-size: .67rem;
            font-weight: 600;
            color: rgba(255,255,255,.3);
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: .6rem 1.5rem .35rem;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .65rem 1.5rem;
            color: rgba(255,255,255,.65);
            font-size: .875rem;
            font-weight: 500;
            border-radius: 0;
            transition: background .15s, color .15s;
            text-decoration: none;
        }
        .sidebar-nav .nav-link i { font-size: 1rem; flex-shrink: 0; }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,.06);
            color: #fff;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(37,99,235,.2);
            color: #fff;
            border-right: 3px solid var(--primary);
        }
        .sidebar-nav .nav-link .nav-badge {
            margin-left: auto;
            font-size: .65rem;
            background: rgba(255,255,255,.12);
            color: rgba(255,255,255,.7);
            padding: .1rem .4rem;
            border-radius: 10px;
        }
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-footer .user-info { display: flex; align-items: center; gap: .75rem; }
        .sidebar-footer .avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .8rem; font-weight: 700;
            flex-shrink: 0;
        }
        .sidebar-footer .user-name { font-size: .83rem; font-weight: 600; color: #fff; }
        .sidebar-footer .user-role { font-size: .72rem; color: rgba(255,255,255,.4); }
        .sidebar-footer .logout-btn {
            margin-top: .6rem;
            display: flex; align-items: center; gap: .4rem;
            color: rgba(255,255,255,.45);
            font-size: .8rem;
            cursor: pointer;
            background: none; border: none; padding: 0;
            transition: color .15s;
        }
        .sidebar-footer .logout-btn:hover { color: #f87171; }

        /* ── Main ─────────────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ───────────────────────────────── */
        .topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 1.75rem;
            gap: 1rem;
            z-index: 1030;
            box-shadow: var(--shadow);
        }
        .topbar .page-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            flex: 1;
        }
        .topbar .env-badge {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .7rem;
            font-weight: 600;
            padding: .25rem .7rem;
            border-radius: 20px;
            letter-spacing: .04em;
        }
        .topbar .env-badge.live  { background: #d1fae5; color: #065f46; }
        .topbar .env-badge.test  { background: #fef3c7; color: #92400e; }
        .topbar .topbar-date {
            font-size: .8rem;
            color: var(--muted);
            white-space: nowrap;
        }

        /* ── Page content ─────────────────────────── */
        .page-content {
            flex: 1;
            padding: 1.75rem;
        }

        /* ── Cards ────────────────────────────────── */
        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: #fff;
            box-shadow: var(--shadow);
        }
        .card-header-flush {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .card-header-flush h6 {
            margin: 0; font-size: .9rem; font-weight: 600;
        }

        /* ── Stat card ────────────────────────────── */
        .stat-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            box-shadow: var(--shadow);
        }
        .stat-card .stat-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .stat-card .stat-label { font-size: .78rem; color: var(--muted); font-weight: 500; }
        .stat-card .stat-value { font-size: 1.55rem; font-weight: 700; color: var(--text); line-height: 1.2; }
        .stat-card .stat-sub   { font-size: .75rem; color: var(--muted); margin-top: .15rem; }

        /* ── Badges ───────────────────────────────── */
        .badge-success { background: #ecfdf5; color: #065f46; font-size: .72rem; padding: .2rem .55rem; border-radius: 20px; font-weight: 500; }
        .badge-warning { background: #fffbeb; color: #92400e; font-size: .72rem; padding: .2rem .55rem; border-radius: 20px; font-weight: 500; }
        .badge-danger  { background: #fef2f2; color: #991b1b; font-size: .72rem; padding: .2rem .55rem; border-radius: 20px; font-weight: 500; }
        .badge-info    { background: #eff6ff; color: #1e40af; font-size: .72rem; padding: .2rem .55rem; border-radius: 20px; font-weight: 500; }

        /* ── Tables ───────────────────────────────── */
        .table { font-size: .875rem; }
        .table th { font-size: .75rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid var(--border); }
        .table td { vertical-align: middle; border-color: var(--border); }

        /* ── Alerts ───────────────────────────────── */
        .alert-success-custom {
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;
            border-radius: var(--radius); padding: .75rem 1rem; font-size: .875rem;
        }
        .alert-danger-custom {
            background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
            border-radius: var(--radius); padding: .75rem 1rem; font-size: .875rem;
        }

        /* ── Code / mono ──────────────────────────── */
        .mono { font-family: 'IBM Plex Mono', monospace; font-size: .82rem; }
        .code-block {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            font-family: 'IBM Plex Mono', monospace;
            font-size: .8rem;
            overflow-x: auto;
            white-space: pre;
        }

        /* ── Mobile sidebar toggle ────────────────── */
        .sidebar-toggle {
            display: none;
            background: none; border: none;
            color: var(--text); font-size: 1.3rem; cursor: pointer;
        }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .sidebar-toggle { display: block; }
            .sidebar-overlay {
                display: none;
                position: fixed; inset: 0;
                background: rgba(0,0,0,.45);
                z-index: 1039;
            }
            .sidebar-overlay.show { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name"><i class="bi bi-lightning-charge-fill text-primary me-1"></i> Safari API</div>
        <div class="brand-sub">api.safaribank.africa</div>
        @auth
        <div class="org-badge">{{ auth()->user()->organization->name ?? 'Unknown Org' }}</div>
        @endauth
    </div>

    <div class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="{{ route('dashboard.overview') }}"
           class="nav-link {{ request()->routeIs('dashboard.overview') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Overview
        </a>
        <a href="{{ route('dashboard.api-logs') }}"
           class="nav-link {{ request()->routeIs('dashboard.api-logs') ? 'active' : '' }}">
            <i class="bi bi-activity"></i> API Logs
        </a>
        <a href="{{ route('dashboard.organization') }}"
           class="nav-link {{ request()->routeIs('dashboard.organization') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Organization
        </a>
        <div class="nav-label mt-2">Resources</div>
        <a href="{{ route('api.docs') }}" target="_blank" class="nav-link">
            <i class="bi bi-book"></i> API Docs
            <i class="bi bi-box-arrow-up-right nav-badge" style="font-size:.65rem;"></i>
        </a>
    </div>

    <div class="sidebar-footer">
        @auth
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role ?? 'user') }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('dashboard.logout') }}">
            @csrf
            <button type="submit" class="logout-btn mt-2">
                <i class="bi bi-box-arrow-left"></i> Sign out
            </button>
        </form>
        @endauth
    </div>
</nav>

<!-- Main wrapper -->
<div class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
        <button class="sidebar-toggle me-1" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="page-title">@yield('page-title', 'Dashboard')</div>
        @auth
        <span class="env-badge {{ (auth()->user()->organization->account_type ?? '') === 'developer' ? 'test' : 'live' }}">
            {{ strtoupper(auth()->user()->organization->account_type ?? 'ORG') }}
        </span>
        @endauth
        <span class="topbar-date">{{ now()->format('D, d M Y') }}</span>
    </header>

    <!-- Flash messages -->
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert-success-custom d-flex align-items-center gap-2 mb-0">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-danger-custom d-flex align-items-center gap-2 mb-0">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-danger-custom mb-0">
                <div class="d-flex align-items-center gap-2 fw-semibold"><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:</div>
                <ul class="mb-0 ps-3 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Page content -->
    <main class="page-content">
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
}
</script>
@stack('scripts')
</body>
</html>
