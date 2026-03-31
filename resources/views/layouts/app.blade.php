<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Billing Platform')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f8ff;
            --surface: #ffffff;
            --surface-soft: #f6f9ff;
            --border: #d7e2f0;
            --text: #0f1e36;
            --text-soft: #4f6688;
            --accent: #4f8dff;
            --accent-soft: rgba(79, 141, 255, 0.15);
            --success: #31c48d;
            --danger: #ef4444;
            --radius: 14px;
            --navbar-bg: #0f1e36;
        }
        [data-theme="dark"] {
            --bg: #0d1117;
            --surface: #161b22;
            --surface-soft: #1c2128;
            --border: #30363d;
            --text: #e6edf3;
            --text-soft: #c9d1d9;
            --accent: #58a6ff;
            --accent-soft: rgba(88, 166, 255, 0.15);
            --success: #3fb950;
            --danger: #f85149;
            --navbar-bg: #010409;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            background: var(--bg);
            font-family: 'Syne', system-ui, sans-serif;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }
        .navbar-brand { font-weight: 700; letter-spacing: 0.3px; font-size: 1.15rem; }
        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: none;
        }
        .form-label {
            font-weight: 500;
            font-size: 0.92rem;
            color: var(--text);
            margin-bottom: 0.3rem;
        }
        .form-control, .form-select {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.9rem;
            border-color: var(--border);
            border-radius: 10px;
            padding: 0.55rem 0.85rem;
            background: var(--surface);
            color: var(--text);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }
        .form-control::placeholder { color: var(--text-soft); opacity: 0.7; }
        .form-text { color: var(--text-soft); font-size: 0.84rem; }
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.92rem;
            letter-spacing: 0.2px;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #3b7ae8;
            border-color: #3b7ae8;
            box-shadow: 0 0 0 3px var(--accent-soft);
        }
        .btn-outline-primary {
            color: var(--accent);
            border-color: var(--accent);
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.88rem;
        }
        .btn-outline-primary:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        main { flex: 1; }
        .theme-toggle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.08);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.2s ease;
            padding: 0;
        }
        .theme-toggle:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.3);
            transform: scale(1.08);
        }
        .theme-toggle:active { transform: scale(0.94); }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg" style="background: var(--navbar-bg); border-bottom: 1px solid rgba(255,255,255,0.08);">
        <div class="container">
            <a class="navbar-brand text-white" href="{{ url('/') }}">API Billing</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-1 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link text-white text-opacity-75" href="{{ route('api.docs') }}" style="font-size:0.88rem;">API Docs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white text-opacity-75" href="{{ route('organizations.register') }}" style="font-size:0.88rem;">Register</a>
                    </li>
                    <li class="nav-item ms-2">
                        <button type="button" class="theme-toggle" id="themeToggle" title="Toggle dark/light mode">
                            <span id="themeIcon">🌙</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4 py-md-5">
        @yield('content')
    </main>

    <footer class="py-3 mt-auto" style="background: var(--navbar-bg); border-top: 1px solid rgba(255,255,255,0.06);">
        <div class="container text-center">
            <small style="color: rgba(255,255,255,0.35); font-size: 0.78rem;">&copy; {{ date('Y') }} Billing Platform</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const html = document.documentElement;

            const saved = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            html.setAttribute('data-theme', saved);
            themeIcon.textContent = saved === 'dark' ? '☀️' : '🌙';

            themeToggle.addEventListener('click', () => {
                const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                themeIcon.textContent = next === 'dark' ? '☀️' : '🌙';
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
