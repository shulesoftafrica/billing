@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 fw-bold">Dashboard</h1>
        <p class="text-muted">Welcome back! Here's what's happening with your billing.</p>
    </div>
    <button class="btn btn-primary">
        <i class="bi bi-download me-2"></i> Download Report
    </button>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1">Total Revenue</p>
                        <h3 class="fw-bold mb-0">$127,458</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-currency-dollar text-primary fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success me-2">
                        <i class="bi bi-arrow-up me-1"></i> 12.5%
                    </span>
                    <small class="text-muted">vs last month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1">Active Subscriptions</p>
                        <h3 class="fw-bold mb-0">2,458</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="bi bi-arrow-repeat text-success fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success me-2">
                        <i class="bi bi-arrow-up me-1"></i> 8.2%
                    </span>
                    <small class="text-muted">vs last month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1">Total Customers</p>
                        <h3 class="fw-bold mb-0">8,742</h3>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="bi bi-people text-info fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success me-2">
                        <i class="bi bi-arrow-up me-1"></i> 15.8%
                    </span>
                    <small class="text-muted">vs last month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-muted mb-1">Float Balance</p>
                        <h3 class="fw-bold mb-0">$45,892</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-wallet2 text-warning fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success me-2">
                        <i class="bi bi-arrow-up me-1"></i> 1% APY
                    </span>
                    <small class="text-muted">interest rate</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Revenue Overview</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary">7D</button>
                        <button type="button" class="btn btn-outline-secondary active">30D</button>
                        <button type="button" class="btn btn-outline-secondary">90D</button>
                        <button type="button" class="btn btn-outline-secondary">1Y</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0">Payment Methods</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentMethodsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0">Recent Transactions</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="border-0">Customer</th>
                                <th class="border-0">Amount</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Date</th>
                                <th class="border-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <small>JD</small>
                                        </div>
                                        <span>John Doe</span>
                                    </div>
                                </td>
                                <td>$2,450.00</td>
                                <td><span class="badge bg-success">Paid</span></td>
                                <td>Feb 18, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-link">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <small>AS</small>
                                        </div>
                                        <span>Alice Smith</span>
                                    </div>
                                </td>
                                <td>$1,299.00</td>
                                <td><span class="badge bg-success">Paid</span></td>
                                <td>Feb 17, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-link">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <small>BW</small>
                                        </div>
                                        <span>Bob Wilson</span>
                                    </div>
                                </td>
                                <td>$899.00</td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>Feb 17, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-link">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <small>CM</small>
                                        </div>
                                        <span>Carol Martinez</span>
                                    </div>
                                </td>
                                <td>$3,299.00</td>
                                <td><span class="badge bg-success">Paid</span></td>
                                <td>Feb 16, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-link">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <small>DJ</small>
                                        </div>
                                        <span>David Johnson</span>
                                    </div>
                                </td>
                                <td>$599.00</td>
                                <td><span class="badge bg-danger">Failed</span></td>
                                <td>Feb 16, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-link">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('web.customers.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-2"></i> Add Customer
                    </a>
                    <a href="{{ route('web.subscriptions.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-repeat me-2"></i> Create Subscription
                    </a>
                    <a href="{{ route('web.invoices.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-file-text me-2"></i> Generate Invoice
                    </a>
                    <a href="{{ route('web.api-keys.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-key me-2"></i> Manage API Keys
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0">System Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>API Status</span>
                    <span class="badge bg-success">Operational</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Payment Gateway</span>
                    <span class="badge bg-success">Operational</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Webhooks</span>
                    <span class="badge bg-success">Operational</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue',
            data: [12000, 19000, 15000, 25000, 22000, 30000, 28000, 32000, 35000, 38000, 42000, 45000],
            borderColor: 'rgb(79, 70, 229)',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Payment Methods Chart
const paymentCtx = document.getElementById('paymentMethodsChart');
new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: ['Credit Card', 'Bank Transfer', 'PayPal', 'Other'],
        datasets: [{
            data: [45, 30, 20, 5],
            backgroundColor: [
                'rgb(79, 70, 229)',
                'rgb(16, 185, 129)',
                'rgb(245, 158, 11)',
                'rgb(107, 114, 128)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection
