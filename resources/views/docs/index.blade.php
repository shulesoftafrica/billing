@extends('layouts.app')

@section('title', 'API Documentation')

@section('content')
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">Billing System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('docs') }}">Documentation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary ms-2" href="{{ route('register') }}">Get Started</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Documentation Content -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-light border-end" style="min-height: calc(100vh - 56px);">
            <div class="sticky-top pt-4">
                <h6 class="text-muted text-uppercase small px-3 mb-3">Getting Started</h6>
                <ul class="nav flex-column mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="#introduction">Introduction</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#authentication">Authentication</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#quickstart">Quick Start</a>
                    </li>
                </ul>

                <h6 class="text-muted text-uppercase small px-3 mb-3">API Reference</h6>
                <ul class="nav flex-column mb-4">
                    <li class="nav-item">
                        <a class="nav-link" href="#customers">Customers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#subscriptions">Subscriptions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#invoices">Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#payments">Payments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#webhooks">Webhooks</a>
                    </li>
                </ul>

                <h6 class="text-muted text-uppercase small px-3 mb-3">Resources</h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="#errors">Error Codes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#rate-limits">Rate Limits</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 px-md-4 py-5">
            <div class="mx-auto" style="max-width: 900px;">
                <!-- Introduction -->
                <section id="introduction" class="mb-5">
                    <h1 class="display-5 fw-bold mb-4">API Documentation</h1>
                    <p class="lead text-muted">
                        Welcome to the Billing System API documentation. Our REST API allows you to create and manage customers, 
                        subscriptions, invoices, and payments programmatically.
                    </p>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Base URL:</strong> <code>https://api.billing.com/v1</code>
                    </div>
                </section>

                <!-- Authentication -->
                <section id="authentication" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Authentication</h2>
                    <p>All API requests require authentication using API keys. Include your API key in the Authorization header:</p>

                    <div class="card bg-dark text-white mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <span>Request Headers</span>
                            <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#auth-example')">Copy</button>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0 text-white" id="auth-example"><code>Authorization: Bearer YOUR_API_KEY
Content-Type: application/json</code></pre>
                        </div>
                    </div>

                    <p class="text-muted">
                        You can generate API keys from your <a href="{{ route('dashboard') }}">dashboard</a> after creating an account.
                    </p>
                </section>

                <!-- Quick Start -->
                <section id="quickstart" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Quick Start</h2>
                    <p>Here's a quick example to create your first subscription:</p>

                    <div class="card bg-dark text-white mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <span>cURL Example</span>
                            <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#quickstart-example')">Copy</button>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0 text-white" id="quickstart-example"><code>curl -X POST https://api.billing.com/v1/subscriptions \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "plan_ids": [1, 2]
  }'</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Customers API -->
                <section id="customers" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Customers</h2>
                    
                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Create a Customer</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/customers</code></p>
                        
                        <div class="card bg-dark text-white">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#customer-create')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="customer-create"><code>{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "country_id": 1
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Get All Customers</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/customers</code></p>
                    </div>

                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Get a Customer</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/customers/{id}</code></p>
                    </div>
                </section>

                <!-- Subscriptions API -->
                <section id="subscriptions" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Subscriptions</h2>
                    
                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Create a Subscription</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/subscriptions</code></p>
                        
                        <div class="card bg-dark text-white">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#subscription-create')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="subscription-create"><code>{
  "customer_id": 1,
  "plan_ids": [1, 2],
  "start_date": "2026-02-18"
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Cancel a Subscription</h4>
                        <p><span class="badge bg-warning">POST</span> <code>/api/subscriptions/{id}/cancel</code></p>
                    </div>
                </section>

                <!-- Invoices API -->
                <section id="invoices" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Invoices</h2>
                    
                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Create an Invoice</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/invoices</code></p>
                    </div>

                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Get Invoice by ID</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/invoices/{id}</code></p>
                    </div>
                </section>

                <!-- Payments API -->
                <section id="payments" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Payments</h2>
                    
                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Get Payments by Date Range</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/payments?start_date=2026-01-01&end_date=2026-02-18</code></p>
                    </div>

                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Get Payments by Invoice</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/payments/by-invoice/{invoice_id}</code></p>
                    </div>
                </section>

                <!-- Webhooks -->
                <section id="webhooks" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Webhooks</h2>
                    <p>Webhooks allow you to receive real-time notifications when events occur in your account.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>payment.success</code></td>
                                    <td>Triggered when a payment is successfully processed</td>
                                </tr>
                                <tr>
                                    <td><code>subscription.created</code></td>
                                    <td>Triggered when a new subscription is created</td>
                                </tr>
                                <tr>
                                    <td><code>subscription.cancelled</code></td>
                                    <td>Triggered when a subscription is cancelled</td>
                                </tr>
                                <tr>
                                    <td><code>invoice.paid</code></td>
                                    <td>Triggered when an invoice is paid</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Error Codes -->
                <section id="errors" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Error Codes</h2>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>200</code></td>
                                    <td>Success</td>
                                </tr>
                                <tr>
                                    <td><code>400</code></td>
                                    <td>Bad Request - Invalid parameters</td>
                                </tr>
                                <tr>
                                    <td><code>401</code></td>
                                    <td>Unauthorized - Invalid or missing API key</td>
                                </tr>
                                <tr>
                                    <td><code>404</code></td>
                                    <td>Not Found - Resource doesn't exist</td>
                                </tr>
                                <tr>
                                    <td><code>429</code></td>
                                    <td>Too Many Requests - Rate limit exceeded</td>
                                </tr>
                                <tr>
                                    <td><code>500</code></td>
                                    <td>Internal Server Error</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Rate Limits -->
                <section id="rate-limits" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Rate Limits</h2>
                    <p>API requests are rate limited to prevent abuse:</p>
                    
                    <ul>
                        <li><strong>Free tier:</strong> 100 requests per hour</li>
                        <li><strong>Pro tier:</strong> 1,000 requests per hour</li>
                        <li><strong>Enterprise tier:</strong> 10,000 requests per hour</li>
                    </ul>

                    <p class="text-muted">Rate limit headers are included in every response:</p>
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <pre class="mb-0 text-white"><code>X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1645190400</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Get Started CTA -->
                <section class="text-center py-5 bg-light rounded">
                    <h3 class="h4 fw-bold mb-3">Ready to Get Started?</h3>
                    <p class="text-muted mb-4">Create your account and start building with our API today.</p>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Sign Up Free</a>
                </section>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .nav-link {
        color: #6c757d;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
    }
    .nav-link:hover {
        color: #000;
        background-color: #f8f9fa;
    }
    .nav-link.active {
        color: #4f46e5;
        background-color: #eef2ff;
    }
    pre code {
        font-size: 0.875rem;
        line-height: 1.5;
    }
    section {
        scroll-margin-top: 80px;
    }
</style>
@endpush

@push('scripts')
<script>
function copyToClipboard(selector) {
    const code = document.querySelector(selector).textContent;
    navigator.clipboard.writeText(code).then(() => {
        alert('Copied to clipboard!');
    });
}

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>
@endpush
@endsection
