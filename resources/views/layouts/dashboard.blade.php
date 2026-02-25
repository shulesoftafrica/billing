<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - Billing System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark text-white" id="sidebar-wrapper" style="width: 250px; min-height: 100vh;">
            <div class="sidebar-heading p-3 border-bottom border-secondary">
                <h4 class="mb-0">Billing System</h4>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="{{ route('web.customers.index') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('web.customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i> Customers
                </a>
                <a href="{{ route('web.subscriptions.index') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('web.subscriptions.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-repeat me-2"></i> Subscriptions
                </a>
                <a href="{{ route('web.invoices.index') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('web.invoices.*') ? 'active' : '' }}">
                    <i class="bi bi-file-text me-2"></i> Invoices
                </a>
                <a href="{{ route('web.payments.index') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('web.payments.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card me-2"></i> Payments
                </a>
                <a href="{{ route('web.api-keys.index') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('web.api-keys.*') ? 'active' : '' }}">
                    <i class="bi bi-key me-2"></i> API Keys
                </a>
                <a href="{{ route('web.webhooks.index') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('web.webhooks.*') ? 'active' : '' }}">
                    <i class="bi bi-globe me-2"></i> Webhooks
                </a>
                <a href="{{ route('web.logs.index') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('web.logs.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text me-2"></i> Logs
                </a>
                <a href="{{ route('settings') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->routeIs('settings') ? 'active' : '' }}">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="flex-fill">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center">
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <button class="btn btn-link text-decoration-none position-relative" type="button" id="notificationsDropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    3
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><a class="dropdown-item" href="#">New payment received</a></li>
                                <li><a class="dropdown-item" href="#">New customer signed up</a></li>
                                <li><a class="dropdown-item" href="#">Invoice #1234 paid</a></li>
                            </ul>
                        </div>

                        <!-- User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('settings') }}">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="container-fluid p-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')
    
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    </script>
</body>
</html>
