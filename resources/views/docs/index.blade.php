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
                        <a class="nav-link" href="#invoices">Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#wallets">Wallets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#subscriptions">Subscriptions</a>
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
                        invoices with multiple products, wallets, subscriptions, and integrated payment processing.
                    </p>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Base URL:</strong> <code>https://api.billing.com/v1</code>
                    </div>

                    <h4 class="h5 fw-bold mt-4">Key Features</h4>
                    <ul class="mb-0">
                        <li><strong>Multi-Product Invoices:</strong> Create invoices with single or multiple products in one request</li>
                        <li><strong>Flexible Product Lookup:</strong> Reference products by ID, code, or price plan</li>
                        <li><strong>Integrated Payment Gateways:</strong> Automatic Flutterwave and EcoBank control number generation</li>
                        <li><strong>Wallet Management:</strong> Full wallet system with credits, debits, and transfers</li>
                        <li><strong>Tax Support:</strong> Automatic tax calculations with configurable tax rates</li>
                        <li><strong>Real-time Webhooks:</strong> Get notified of payments, invoices, and wallet events</li>
                    </ul>
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
                        Register a user account and login via <code>POST /api/auth/login</code> to receive your personal access token. 
                        <span class="badge bg-success text-white">New: Token-based authentication available!</span>
                    </p>
                </section>

                <!-- Quick Start -->
                <section id="quickstart" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Quick Start</h2>
                    <p>Here's a quick example to create your first invoice with payment gateway integration:</p>

                    <div class="card bg-dark text-white mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <span>cURL Example - Create Invoice with Flutterwave</span>
                            <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#quickstart-example')">Copy</button>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0 text-white" id="quickstart-example"><code>curl -X POST https://api.billing.com/v1/invoices \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "organization_id": 1,
    "customer": {
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+255712345678"
    },
    "products": [
      {
        "product_code": "HOSTING-BASIC",
        "amount": 50000
      }
    ],
    "payment_gateway": "flutterwave",
    "success_url": "https://yourapp.com/payment/success",
    "cancel_url": "https://yourapp.com/payment/cancel",
    "currency": "TZS"
  }'</code></pre>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Success!</strong> The API will return an invoice with a Flutterwave payment link that your customer can use to pay immediately.
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

                <!-- Invoices API -->
                <section id="invoices" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Invoices</h2>
                    <p class="text-muted mb-4">Create and manage invoices with support for three different invoice types: one-time, subscription, and usage-based billing.</p>
                    
                    <!-- Invoice Types Overview -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold mb-3">Invoice Types Overview</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice Type</th>
                                        <th>Product Type</th>
                                        <th>Billing Pattern</th>
                                        <th>Use Case</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-primary">One-Time</span></td>
                                        <td>One-time Product (product_type_id: 1)</td>
                                        <td>Single charge</td>
                                        <td>One-off services, consulting, project work</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">Subscription</span></td>
                                        <td>Subscription Product (product_type_id: 2)</td>
                                        <td>Recurring charges</td>
                                        <td>SaaS, memberships, monthly/yearly plans</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">Usage-Based</span></td>
                                        <td>Usage Product (product_type: usage)</td>
                                        <td>Pay-per-use</td>
                                        <td>API calls, storage, bandwidth, credits</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Important:</strong> The invoice type is automatically determined by the product type associated with the price plan. You don't need to explicitly specify the invoice type in your request.
                        </div>
                    </div>

                    <!-- One-Time Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">1. Create One-Time Invoice</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/invoices</code></p>
                        <p class="text-muted">One-time invoices are for products that are charged once without creating a subscription. Perfect for consulting services, one-off projects, or standalone purchases.</p>

                        
                        <h6 class="fw-bold mt-4">Required Parameters</h6>
                        <ul class="mb-3">
                            <li><code>organization_id</code> (integer) - ID of your organization</li>
                            <li><code>customer</code> (object) - Customer information</li>
                            <li><code>customer.name</code> (string) - Customer's full name</li>
                            <li><code>customer.email</code> (string) - Customer's email address</li>
                            <li><code>customer.phone</code> (string) - Customer's phone number</li>
                            <li><code>products</code> (array) - Array of products (minimum 1)</li>
                            <li><code>products.*.price_plan_id</code> (integer) - Price plan ID for a one-time product</li>
                            <li><code>products.*.amount</code> (number) - Invoice amount for this product</li>
                            <li><code>currency</code> (string) - 3-letter currency code (e.g., "TZS", "USD")</li>
                        </ul>

                        <h6 class="fw-bold mt-4">Optional Parameters</h6>
                        <ul class="mb-3">
                            <li><code>tax_rate_ids</code> (array) - Array of tax rate IDs to apply</li>
                            <li><code>description</code> (string) - Invoice description</li>
                            <li><code>status</code> (string) - Invoice status: draft, issued, paid, cancelled (default: "issued")</li>
                            <li><code>date</code> (string) - Invoice date in Y-m-d format (default: current date)</li>
                            <li><code>due_date</code> (string) - Payment due date in Y-m-d format</li>
                            <li><code>payment_gateway</code> (string) - flutterwave, control_number, or both</li>
                            <li><code>success_url</code> (string) - Redirect URL after successful payment</li>
                            <li><code>cancel_url</code> (string) - Redirect URL after cancelled payment</li>
                        </ul>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Example - One-Time Invoice</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-one-time')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-one-time"><code>{
  "organization_id": 1,
  "customer": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+255712345678"
  },
  "products": [
    {
      "price_plan_id": 5,
      "amount": 50000
    }
  ],
  "description": "Website development project",
  "currency": "TZS",
  "status": "issued",
  "date": "2026-02-26",
  "due_date": "2026-03-26"
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - One-Time Invoice</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 123,
      "invoice_number": "INV-2026-00123",
      "customer_id": 45,
      "customer_name": "John Doe",
      "customer_email": "john@example.com",
      "currency": "TZS",
      "status": "issued",
      "description": "Website development project",
      "subtotal": 50000,
      "tax_total": 0,
      "total": 50000,
      "date": "2026-02-26",
      "due_date": "2026-03-26",
      "issued_at": "2026-02-26T10:30:00.000000Z",
      "items": [
        {
          "id": 456,
          "price_plan_id": 5,
          "product_name": "Website Development",
          "quantity": 1,
          "unit_price": 50000,
          "total": 50000
        }
      ],
      "taxes": [],
      "payments": []
    }
  }
}</code></pre>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> If a customer with the same email or phone already exists in your organization, the existing customer record will be used instead of creating a new one.
                        </div>
                    </div>

                    <!-- Subscription Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">2. Create Subscription Invoice</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/invoices</code></p>
                        <p class="text-muted">Subscription invoices automatically create a subscription record for recurring billing. The subscription remains in "pending" status until the invoice is paid, then becomes "active".</p>
                        
                        <h6 class="fw-bold mt-4">Required Parameters</h6>
                        <ul class="mb-3">
                            <li><code>organization_id</code> (integer) - ID of your organization</li>
                            <li><code>customer</code> (object) - Customer information</li>
                            <li><code>customer.name</code> (string) - Customer's full name</li>
                            <li><code>customer.email</code> (string) - Customer's email address</li>
                            <li><code>customer.phone</code> (string) - Customer's phone number</li>
                            <li><code>products</code> (array) - Array of products (minimum 1)</li>
                            <li><code>products.*.price_plan_id</code> (integer) - Price plan ID for a subscription product</li>
                            <li><code>products.*.amount</code> (number) - Invoice amount for this product</li>
                            <li><code>currency</code> (string) - 3-letter currency code</li>
                        </ul>

                        <h6 class="fw-bold mt-4">Optional Parameters</h6>
                        <ul class="mb-3">
                            <li><code>tax_rate_ids</code> (array) - Array of tax rate IDs to apply</li>
                            <li><code>description</code> (string) - Invoice description</li>
                            <li><code>status</code> (string) - Invoice status (default: "issued")</li>
                            <li><code>date</code> (string) - Invoice date in Y-m-d format</li>
                            <li><code>due_date</code> (string) - Payment due date in Y-m-d format</li>
                            <li><code>payment_gateway</code> (string) - flutterwave, control_number, or both</li>
                            <li><code>success_url</code> (string) - Redirect URL after successful payment</li>
                            <li><code>cancel_url</code> (string) - Redirect URL after cancelled payment</li>
                        </ul>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Example - Subscription Invoice</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-subscription')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-subscription"><code>{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 8,
      "amount": 75000
    }
  ],
  "description": "Premium hosting - Monthly subscription",
  "currency": "TZS",
  "status": "issued",
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel"
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - Subscription Invoice</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 124,
      "invoice_number": "INV-2026-00124",
      "customer_id": 46,
      "currency": "TZS",
      "status": "issued",
      "description": "Premium hosting - Monthly subscription",
      "subtotal": 75000,
      "tax_total": 0,
      "total": 75000,
      "due_date": null,
      "issued_at": "2026-02-26T11:15:00.000000Z",
      "items": [
        {
          "id": 457,
          "price_plan_id": 8,
          "subscription_id": 89,
          "product_name": "Premium Hosting Plan",
          "billing_interval": "monthly",
          "quantity": 1,
          "unit_price": 75000,
          "total": 75000
        }
      ],
      "subscription": {
        "id": 89,
        "status": "pending",
        "price_plan_id": 8,
        "start_date": null,
        "next_billing_date": null,
        "note": "Subscription will activate upon payment"
      },
      "payment_details": {
        "flutterwave": {
          "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
          "tx_ref": "INV-2026-00124-1708956234",
          "expires_at": "2026-03-05T11:15:00.000000Z"
        }
      }
    }
  }
}</code></pre>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> The subscription is created in "pending" status. It will automatically activate when the invoice is paid, and the next billing date will be calculated based on the price plan's billing interval.
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Duplicate Prevention:</strong> If a pending subscription already exists for the same customer and price plan, a new invoice will not be created. The existing invoice will be returned instead.
                        </div>
                    </div>

                    <!-- Usage-Based Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">3. Create Usage-Based Invoice</h4>
                        <p class="text-muted">Usage-based billing is a two-step process: first record usage, then create invoices based on accumulated usage.</p>
                        
                        <h6 class="fw-bold mt-4">Step 1: Record Product Usage</h6>
                        <p><span class="badge bg-success">POST</span> <code>/api/product-usage</code></p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Example - Record Usage</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#usage-record')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="usage-record"><code>{
  "customer_id": 45,
  "product_id": 12,
  "quantity": 5000
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - Usage Recorded</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Product usage recorded successfully",
  "data": {
    "id": 789,
    "customer_id": 45,
    "product_id": 12,
    "quantity": 5000,
    "created_at": "2026-02-26T12:00:00.000000Z",
    "product": {
      "id": 12,
      "name": "API Calls",
      "product_type": "usage",
      "unit": "calls"
    },
    "customer": {
      "id": 45,
      "name": "Tech Startup Inc",
      "email": "billing@techstartup.com"
    }
  }
}</code></pre>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4">Step 2: Get Usage Report</h6>
                        <p><span class="badge bg-primary">GET</span> <code>/api/product-usage/report/{customer_id}</code></p>
                        <p class="text-muted">Retrieve accumulated usage data for a customer to calculate charges.</p>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - Usage Report</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "data": {
    "customer_id": 45,
    "customer_name": "Tech Startup Inc",
    "usage_summary": [
      {
        "product_id": 12,
        "product_name": "API Calls",
        "product_code": "API-USAGE",
        "total_purchased": 50000,
        "total_used": 45000,
        "balance": 5000,
        "unit": "calls"
      },
      {
        "product_id": 13,
        "product_name": "Cloud Storage",
        "product_code": "STORAGE-GB",
        "total_purchased": 1000,
        "total_used": 750,
        "balance": 250,
        "unit": "GB"
      }
    ]
  }
}</code></pre>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4">Step 3: Create Invoice for Usage</h6>
                        <p><span class="badge bg-success">POST</span> <code>/api/invoices</code></p>
                        <p class="text-muted">Create an invoice based on the usage data. Calculate the amount based on your pricing model (e.g., price per API call, per GB).</p>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Example - Usage Invoice</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-usage')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-usage"><code>{
  "organization_id": 1,
  "customer": {
    "name": "Tech Startup Inc",
    "email": "billing@techstartup.com",
    "phone": "+255734567890"
  },
  "products": [
    {
      "price_plan_id": 15,
      "amount": 45000
    }
  ],
  "description": "API Usage - 45,000 calls @ TZS 1 per call",
  "currency": "TZS",
  "status": "issued"
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - Usage Invoice</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 125,
      "invoice_number": "INV-2026-00125",
      "customer_id": 45,
      "currency": "TZS",
      "status": "issued",
      "description": "API Usage - 45,000 calls @ TZS 1 per call",
      "subtotal": 45000,
      "tax_total": 0,
      "total": 45000,
      "issued_at": "2026-02-26T12:30:00.000000Z",
      "items": [
        {
          "id": 458,
          "price_plan_id": 15,
          "product_name": "API Usage Charges",
          "quantity": 1,
          "unit_price": 45000,
          "total": 45000,
          "metadata": {
            "usage_period": "2026-02-01 to 2026-02-28",
            "total_calls": 45000,
            "rate_per_call": 1
          }
        }
      ]
    }
  }
}</code></pre>
                            </div>
                        </div>

                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Usage-Based Billing Pattern:</strong> 
                            Record usage throughout the billing period → Retrieve usage report → Calculate charges → Create invoice
                        </div>
                    </div>

                    <!-- Create Multiple Products Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">4. Create Multi-Product Invoice (Mixed Types)</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/invoices</code></p>
                        <p class="text-muted">Create a single invoice with multiple products of different types (one-time and subscription products can be combined).</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Example - Mixed Products</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-multiple')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-multiple"><code>{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 3,
      "amount": 100000
    },
    {
      "price_plan_id": 5,
      "amount": 50000
    },
    {
      "price_plan_id": 8,
      "amount": 25000
    }
  ],
  "tax_rate_ids": [1, 2],
  "description": "Bundle: Hosting + Domain + SSL",
  "currency": "TZS",
  "status": "issued"
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - Multi-Product Invoice</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 126,
      "invoice_number": "INV-2026-00126",
      "customer_id": 46,
      "currency": "TZS",
      "status": "issued",
      "description": "Bundle: Hosting + Domain + SSL",
      "subtotal": 175000,
      "tax_total": 31500,
      "total": 206500,
      "issued_at": "2026-02-26T13:00:00.000000Z",
      "items": [
        {
          "id": 459,
          "price_plan_id": 3,
          "subscription_id": 90,
          "product_name": "Premium Hosting",
          "product_type": "Subscription Product",
          "quantity": 1,
          "unit_price": 100000,
          "total": 100000
        },
        {
          "id": 460,
          "price_plan_id": 5,
          "subscription_id": null,
          "product_name": "Domain Registration",
          "product_type": "One-time Product",
          "quantity": 1,
          "unit_price": 50000,
          "total": 50000
        },
        {
          "id": 461,
          "price_plan_id": 8,
          "subscription_id": null,
          "product_name": "SSL Certificate",
          "product_type": "One-time Product",
          "quantity": 1,
          "unit_price": 25000,
          "total": 25000
        }
      ],
      "taxes": [
        {
          "tax_rate_id": 1,
          "name": "VAT",
          "percentage": 15,
          "amount": 26250
        },
        {
          "tax_rate_id": 2,
          "name": "Service Tax",
          "percentage": 3,
          "amount": 5250
        }
      ],
      "subscriptions": [
        {
          "id": 90,
          "price_plan_id": 3,
          "status": "pending",
          "product_name": "Premium Hosting"
        }
      ]
    }
  }
}</code></pre>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Mixed Invoice Behavior:</strong> When an invoice contains both one-time and subscription products, subscriptions are created only for subscription-type products. One-time products are charged without creating a subscription.
                        </div>
                    </div>

                    <!-- Flexible Product Lookup -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">5. Flexible Product Lookup Methods</h4>
                        <p class="text-muted">You can specify products using three different lookup methods. Choose the method that best fits your integration:</p>
                        
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Method</th>
                                        <th>Parameter</th>
                                        <th>When to Use</th>
                                        <th>Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Price Plan ID</strong></td>
                                        <td><code>price_plan_id</code></td>
                                        <td>Most specific - when you know the exact plan</td>
                                        <td>price_plan_id: 5</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Product Code</strong></td>
                                        <td><code>product_code</code></td>
                                        <td>User-friendly - use readable codes</td>
                                        <td>product_code: "HOSTING-BASIC"</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Product ID</strong></td>
                                        <td><code>product_id</code></td>
                                        <td>Simple product reference</td>
                                        <td>product_id: 12</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Using Product Code</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-product-code')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-product-code"><code>{
  "organization_id": 1,
  "customer": {
    "name": "Bob Wilson",
    "email": "bob@startup.com",
    "phone": "+255734567890"
  },
  "products": [
    {
      "product_code": "HOSTING-BASIC",
      "amount": 50000
    },
    {
      "product_code": "DOMAIN-COM",
      "amount": 15000
    }
  ]
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Using Product ID</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-product-id')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-product-id"><code>{
  "organization_id": 1,
  "customer": {
    "name": "Alice Johnson",
    "email": "alice@tech.com",
    "phone": "+255745678901"
  },
  "products": [
    {
      "product_id": 12,
      "amount": 75000
    }
  ]
}</code></pre>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Each product must have exactly ONE identifier (price_plan_id, product_code, or product_id). Using multiple identifiers for the same product will result in a validation error.
                        </div>
                    </div>

                    <!-- Invoice with Payment Gateway -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">6. Invoices with Payment Gateway Integration</h4>
                        <p class="text-muted">Generate payment links automatically when creating invoices. Supports Flutterwave (card/mobile money) and EcoBank control numbers (bank payments).</p>
                        
                        <h6 class="fw-bold mt-4">Payment Gateway Parameters</h6>
                        <ul class="mb-3">
                            <li><code>payment_gateway</code> (string) - optional: "flutterwave", "control_number", or "both"</li>
                            <li><code>success_url</code> (string) - required for Flutterwave: redirect URL after successful payment</li>
                            <li><code>cancel_url</code> (string) - required for Flutterwave: redirect URL after cancelled payment</li>
                        </ul>
                        
                        <h6 class="fw-bold mt-4">Flutterwave Payment Link</h6>
                        <p class="text-muted">Generate a hosted payment page for card, mobile money, and bank transfer payments.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request - Flutterwave Payment</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-flutterwave')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-flutterwave"><code>{
  "organization_id": 1,
  "customer": {
    "name": "Sarah Lee",
    "email": "sarah@business.com",
    "phone": "+255756789012"
  },
  "products": [
    {
      "price_plan_id": 7,
      "amount": 120000
    }
  ],
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel",
  "description": "Premium hosting package",
  "currency": "TZS"
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - Flutterwave Payment</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Invoice created successfully with Flutterwave payment link",
  "data": {
    "invoice": {
      "id": 127,
      "invoice_number": "INV-2026-00127",
      "total": 120000,
      "status": "issued",
      "customer_email": "sarah@business.com"
    },
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz789",
        "tx_ref": "INV-2026-00127-1708960000",
        "expires_at": "2026-03-05T14:00:00.000000Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer",
        "supported_methods": [
          "card",
          "mobile_money",
          "bank_transfer"
        ]
      }
    },
    "redirect_url": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz789"
  }
}</code></pre>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4">Control Number (EcoBank)</h6>
                        <p class="text-muted">Generate a control number for bank payments through EcoBank.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request - Control Number</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-control-number')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-control-number"><code>{
  "organization_id": 1,
  "customer": {
    "name": "Michael Brown",
    "email": "michael@enterprise.com",
    "phone": "+255767890123"
  },
  "products": [
    {
      "product_code": "CLOUD-SERVER-M",
      "amount": 500000
    }
  ],
  "payment_gateway": "control_number",
  "description": "Cloud server subscription",
  "currency": "TZS"
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - Control Number</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Invoice created successfully with control number",
  "data": {
    "invoice": {
      "id": 128,
      "invoice_number": "INV-2026-00128",
      "total": 500000,
      "status": "issued",
      "customer_email": "michael@enterprise.com"
    },
    "payment_details": {
      "control_number": {
        "reference": "9912345678",
        "amount": 500000,
        "currency": "TZS",
        "expires_at": "2026-03-12T14:30:00.000000Z",
        "payment_instructions": {
          "mobile_banking": "Dial *150*01*9912345678# from your registered mobile number",
          "internet_banking": "Login to your internet banking and pay bill using control number: 9912345678",
          "agent_banking": "Visit any bank agent and provide the control number: 9912345678",
          "atm": "Use ATM bill payment option with control number: 9912345678"
        }
      }
    }
  }
}</code></pre>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4">Both Payment Methods</h6>
                        <p class="text-muted">Generate both Flutterwave payment link AND control number to give customers multiple payment options.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request - Both Gateways</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-both-gateways')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-both-gateways"><code>{
  "organization_id": 1,
  "customer": {
    "name": "David Chen",
    "email": "david@corp.com",
    "phone": "+255778901234"
  },
  "products": [
    {
      "price_plan_id": 10,
      "amount": 250000
    }
  ],
  "payment_gateway": "both",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel",
  "currency": "TZS"
}</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Response - Both Payment Methods</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Invoice created successfully with multiple payment options",
  "data": {
    "invoice": {
      "id": 129,
      "invoice_number": "INV-2026-00129",
      "total": 250000,
      "status": "issued"
    },
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/xyz789abc",
        "tx_ref": "INV-2026-00129-1708961000",
        "expires_at": "2026-03-12T15:00:00.000000Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer"
      },
      "control_number": {
        "reference": "9912345679",
        "amount": 250000,
        "currency": "TZS",
        "expires_at": "2026-03-12T15:00:00.000000Z",
        "payment_instructions": {
          "mobile_banking": "Dial *150*01*9912345679# from your registered mobile number",
          "internet_banking": "Login to your internet banking and pay bill using control number",
          "agent_banking": "Visit any bank agent and provide the control number"
        }
      }
    },
    "urls": {
      "success_url": "https://yourapp.com/payment/success",
      "cancel_url": "https://yourapp.com/payment/cancel"
    }
  }
}</code></pre>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Gateway Option</th>
                                        <th>Description</th>
                                        <th>Required Parameters</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>flutterwave</code></td>
                                        <td>Card, mobile money, and bank transfer via Flutterwave</td>
                                        <td>success_url, cancel_url</td>
                                    </tr>
                                    <tr>
                                        <td><code>control_number</code></td>
                                        <td>EcoBank control number for bank payments</td>
                                        <td>None</td>
                                    </tr>
                                    <tr>
                                        <td><code>both</code></td>
                                        <td>Both Flutterwave link AND control number</td>
                                        <td>success_url, cancel_url</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-success mt-3">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Payment Flexibility:</strong> Using "both" payment_gateway option allows customers to choose their preferred payment method - online payments or bank transfers.
                        </div>
                    </div>

                    <!-- Get Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Invoice by ID</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/invoices/{id}</code></p>
                        <p class="text-muted">Retrieve detailed invoice information including items, taxes, payments, subscriptions, and control numbers.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Example Request</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>GET /api/invoices/123
Authorization: Bearer YOUR_API_KEY</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Get Invoices by Product -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Invoices by Product</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/invoices?product_id={id}&status={status}</code></p>
                        <p class="text-muted">Filter invoices by product and optionally by status (paid, issued, cancelled).</p>
                    </div>

                    <!-- Complete Parameter Reference -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Complete Parameter Reference</h4>
                        <p class="text-muted">Comprehensive list of all parameters for invoice creation.</p>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>organization_id</code></td>
                                        <td>integer</td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Your organization ID</td>
                                    </tr>
                                    <tr>
                                        <td><code>customer</code></td>
                                        <td>object</td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Customer information object</td>
                                    </tr>
                                    <tr>
                                        <td><code>customer.name</code></td>
                                        <td>string</td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Customer's full name</td>
                                    </tr>
                                    <tr>
                                        <td><code>customer.email</code></td>
                                        <td>string (email)</td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Customer's email address</td>
                                    </tr>
                                    <tr>
                                        <td><code>customer.phone</code></td>
                                        <td>string</td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Customer's phone number</td>
                                    </tr>
                                    <tr>
                                        <td><code>products</code></td>
                                        <td>array</td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Array of products (minimum 1)</td>
                                    </tr>
                                    <tr>
                                        <td><code>products.*.price_plan_id</code></td>
                                        <td>integer</td>
                                        <td><span class="badge bg-warning">Conditional</span></td>
                                        <td>Price plan ID (use ONE of: price_plan_id, product_code, or product_id)</td>
                                    </tr>
                                    <tr>
                                        <td><code>products.*.product_code</code></td>
                                        <td>string</td>
                                        <td><span class="badge bg-warning">Conditional</span></td>
                                        <td>Product code (use ONE of: price_plan_id, product_code, or product_id)</td>
                                    </tr>
                                    <tr>
                                        <td><code>products.*.product_id</code></td>
                                        <td>integer</td>
                                        <td><span class="badge bg-warning">Conditional</span></td>
                                        <td>Product ID (use ONE of: price_plan_id, product_code, or product_id)</td>
                                    </tr>
                                    <tr>
                                        <td><code>products.*.amount</code></td>
                                        <td>number</td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Invoice amount for this product (minimum: 0)</td>
                                    </tr>
                                    <tr>
                                        <td><code>currency</code></td>
                                        <td>string (3 chars)</td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>3-letter currency code (e.g., "TZS", "USD", "EUR")</td>
                                    </tr>
                                    <tr>
                                        <td><code>tax_rate_ids</code></td>
                                        <td>array</td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Array of tax rate IDs to apply to invoice</td>
                                    </tr>
                                    <tr>
                                        <td><code>description</code></td>
                                        <td>string</td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Invoice description or notes</td>
                                    </tr>
                                    <tr>
                                        <td><code>status</code></td>
                                        <td>string</td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Invoice status: draft, issued, paid, cancelled (default: "issued")</td>
                                    </tr>
                                    <tr>
                                        <td><code>date</code></td>
                                        <td>string (date)</td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Invoice date in Y-m-d format (default: current date)</td>
                                    </tr>
                                    <tr>
                                        <td><code>due_date</code></td>
                                        <td>string (date)</td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Payment due date in Y-m-d format</td>
                                    </tr>
                                    <tr>
                                        <td><code>payment_gateway</code></td>
                                        <td>string</td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Payment gateway: "flutterwave", "control_number", or "both"</td>
                                    </tr>
                                    <tr>
                                        <td><code>success_url</code></td>
                                        <td>string (URL)</td>
                                        <td><span class="badge bg-warning">Conditional</span></td>
                                        <td>Required if using Flutterwave - redirect URL after successful payment</td>
                                    </tr>
                                    <tr>
                                        <td><code>cancel_url</code></td>
                                        <td>string (URL)</td>
                                        <td><span class="badge bg-warning">Conditional</span></td>
                                        <td>Required if using Flutterwave - redirect URL after cancelled payment</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Product Identifier Rule:</strong> Each product in the products array must have EXACTLY ONE identifier: either <code>price_plan_id</code>, <code>product_code</code>, or <code>product_id</code>. Using multiple identifiers or none will result in a validation error.
                        </div>
                    </div>

                    <!-- Wallet Topup Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Create Wallet Topup Invoice</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/invoices/wallet-topup</code></p>
                        <p class="text-muted">Create an invoice specifically for adding credits to a customer's wallet.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-wallet-topup')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-wallet-topup"><code>{
  "organization_id": 1,
  "customer_id": 45,
  "amount": 100000,
  "wallet_type": "main",
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/wallet/success",
  "cancel_url": "https://yourapp.com/wallet/cancel",
  "description": "Wallet credit topup - 100,000 TZS"
}</code></pre>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Wallet Types:</strong> <code>main</code>, <code>bonus</code>, <code>promotional</code>
                        </div>
                    </div>
                </section>

                <!-- Wallets API -->
                <section id="wallets" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Wallets</h2>
                    <p class="text-muted mb-4">Manage customer wallet balances, credits, and transaction history.</p>
                    
                    <!-- Get Wallet Balance -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Wallet Balance</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/wallets/balance?customer_id={id}&wallet_type={type}</code></p>
                        <p class="text-muted">Check the current balance of a customer's wallet.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Example Request</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>GET /api/wallets/balance?customer_id=45&wallet_type=main
Authorization: Bearer YOUR_API_KEY</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white">
                            <div class="card-header">Sample Response</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "data": {
    "customer_id": 45,
    "wallet_type": "main",
    "balance": 150000,
    "currency": "TZS",
    "last_transaction": "2026-02-25T14:30:00.000000Z"
  }
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Add Credits -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Add Wallet Credits</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/wallets/credit</code></p>
                        <p class="text-muted">Manually add credits to a customer's wallet (admin operation).</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#wallet-credit')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="wallet-credit"><code>{
  "customer_id": 45,
  "amount": 50000,
  "wallet_type": "main",
  "description": "Promotional bonus credit",
  "reference": "PROMO-2026-FEB"
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Deduct Credits -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Deduct Wallet Credits</h4>
                        <p><span class="badge bg-warning">POST</span> <code>/api/wallets/deduct</code></p>
                        <p class="text-muted">Deduct credits from a customer's wallet (for service usage or purchases).</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#wallet-deduct')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="wallet-deduct"><code>{
  "customer_id": 45,
  "amount": 25000,
  "wallet_type": "main",
  "description": "SMS service usage",
  "reference": "SMS-USAGE-2026-02"
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Credits -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Transfer Credits Between Wallets</h4>
                        <p><span class="badge bg-info">POST</span> <code>/api/wallets/transfer</code></p>
                        <p class="text-muted">Transfer credits from one customer's wallet to another.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#wallet-transfer')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="wallet-transfer"><code>{
  "from_customer_id": 45,
  "to_customer_id": 67,
  "amount": 10000,
  "wallet_type": "main",
  "description": "Credits transfer to partner account"
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Get Transaction History -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Transaction History</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/wallets/{customer_id}/transactions?wallet_type={type}&limit={limit}</code></p>
                        <p class="text-muted">Retrieve wallet transaction history for a customer.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Example Request</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>GET /api/wallets/45/transactions?wallet_type=main&limit=50
Authorization: Bearer YOUR_API_KEY</code></pre>
                            </div>
                        </div>

                        <div class="card bg-dark text-white">
                            <div class="card-header">Sample Response</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "data": {
    "customer_id":45,
    "wallet_type": "main",
    "current_balance": 150000,
    "transactions": [
      {
        "id": 234,
        "type": "credit",
        "amount": 50000,
        "balance_before": 100000,
        "balance_after": 150000,
        "description": "Invoice payment INV-2026-00456",
        "reference": "INV-2026-00456",
        "created_at": "2026-02-25T14:30:00.000000Z"
      },
      {
        "id": 233,
        "type": "debit",
        "amount": 5000,
        "balance_before": 105000,
        "balance_after": 100000,
        "description": "SMS service usage",
        "reference": "SMS-USAGE-2026-02",
        "created_at": "2026-02-24T09:15:00.000000Z"
      }
    ]
  }
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Get Transactions by Wallet Type -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Transactions by Wallet Type (All Customers)</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/wallets/transactions?customer_id={id}&wallet_type={type}</code></p>
                        <p class="text-muted">Query transactions across customers filtered by wallet type.</p>
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
  "start_date": "2026-02-26"
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="h5 fw-bold">Cancel a Subscription</h4>
                        <p><span class="badge bg-warning">POST</span> <code>/api/subscriptions/{id}/cancel</code></p>
                    </div>
                </section>

                <!-- Payments API -->
                <section id="payments" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Payments</h2>
                    <p class="text-muted mb-4">Track and query payment transactions across your system.</p>
                    
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Payments by Date Range</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/payments?date_from={date}&date_to={date}</code></p>
                        <p class="text-muted">Retrieve all payments within a specific date range.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header">Example Request</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>GET /api/payments?date_from=2026-02-01&date_to=2026-02-28
Authorization: Bearer YOUR_API_KEY</code></pre>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Payments by Invoice</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/payments/by-invoice/{invoice_id}</code></p>
                        <p class="text-muted">Get all payment transactions for a specific invoice.</p>
                        
                        <div class="card bg-dark text-white">
                            <div class="card-header">Sample Response</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "data": {
    "invoice_id": 123,
    "invoice_number": "INV-2026-00123",
    "total_amount": 295000,
    "amount_paid": 295000,
    "status": "paid",
    "payments": [
      {
        "id": 456,
        "amount": 295000,
        "payment_method": "flutterwave",
        "transaction_reference": "FLW-2026-TX-789456",
        "status": "success",
        "paid_at": "2026-02-26T15:45:30.000000Z"
      }
    ]
  }
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Payment Status Values</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-success">success</span></td>
                                        <td>Payment completed successfully</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">pending</span></td>
                                        <td>Payment initiated but not yet confirmed</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-danger">failed</span></td>
                                        <td>Payment failed or was declined</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-secondary">cancelled</span></td>
                                        <td>Payment was cancelled by user or system</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Webhooks -->
                <section id="webhooks" class="mb-5">
                    <h2 class="h3 fw-bold mb-3">Webhooks</h2>
                    <p>Webhooks allow you to receive real-time notifications when events occur in your account.</p>

                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        Configure your webhook endpoint URL in your <a href="{{ route('dashboard') }}">dashboard settings</a>.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Description</th>
                                    <th>Payload Includes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>invoice.created</code></td>
                                    <td>Triggered when a new invoice is created</td>
                                    <td>invoice object, customer, items, taxes</td>
                                </tr>
                                <tr>
                                    <td><code>invoice.paid</code></td>
                                    <td>Triggered when an invoice is fully paid</td>
                                    <td>invoice object, payment details</td>
                                </tr>
                                <tr>
                                    <td><code>payment.success</code></td>
                                    <td>Triggered when a payment is successfully processed</td>
                                    <td>payment object, invoice reference, customer</td>
                                </tr>
                                <tr>
                                    <td><code>payment.failed</code></td>
                                    <td>Triggered when a payment fails</td>
                                    <td>payment attempt, error details, invoice</td>
                                </tr>
                                <tr>
                                    <td><code>wallet.credited</code></td>
                                    <td>Triggered when credits are added to a wallet</td>
                                    <td>wallet transaction, customer, new balance</td>
                                </tr>
                                <tr>
                                    <td><code>wallet.debited</code></td>
                                    <td>Triggered when credits are deducted from a wallet</td>
                                    <td>wallet transaction, customer, new balance</td>
                                </tr>
                                <tr>
                                    <td><code>subscription.created</code></td>
                                    <td>Triggered when a new subscription is created</td>
                                    <td>subscription object, price plans, customer</td>
                                </tr>
                                <tr>
                                    <td><code>subscription.activated</code></td>
                                    <td>Triggered when a subscription becomes active</td>
                                    <td>subscription object, activation date</td>
                                </tr>
                                <tr>
                                    <td><code>subscription.cancelled</code></td>
                                    <td>Triggered when a subscription is cancelled</td>
                                    <td>subscription object, cancellation reason</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <h4 class="h5 fw-bold">Webhook Security</h4>
                        <p class="text-muted">All webhook requests include a signature header for verification:</p>
                        
                        <div class="card bg-dark text-white">
                            <div class="card-header">Webhook Headers</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>X-Webhook-Signature: sha256_hash_of_payload
X-Webhook-Event: invoice.created
Content-Type: application/json</code></pre>
                            </div>
                        </div>
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
