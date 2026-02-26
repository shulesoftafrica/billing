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
                        You can generate API keys from your <a href="{{ route('dashboard') }}">dashboard</a> after creating an account.
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
                    <p class="text-muted mb-4">Create and manage invoices with support for single or multiple products, tax calculations, and integrated payment gateways.</p>
                    
                    <!-- Create Single Product Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Create Single Product Invoice</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/invoices</code></p>
                        <p class="text-muted">Create an invoice with a single product using price plan ID.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyToClipboard('#invoice-single')">Copy</button>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0 text-white" id="invoice-single"><code>{
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
  "description": "Monthly hosting subscription",
  "currency": "TZS",
  "status": "issued",
  "date": "2026-02-26",
  "due_date": "2026-03-26"
}</code></pre>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> If the customer email or phone already exists, the existing customer will be used.
                        </div>
                    </div>

                    <!-- Create Multiple Products Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Create Multiple Products Invoice</h4>
                        <p><span class="badge bg-success">POST</span> <code>/api/invoices</code></p>
                        <p class="text-muted">Create an invoice with multiple products in a single request.</p>
                        
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
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
                    </div>

                    <!-- Flexible Product Lookup -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Flexible Product Lookup</h4>
                        <p class="text-muted">You can specify products using three different methods:</p>
                        
                        <ul class="mb-3">
                            <li><strong>price_plan_id</strong> - Direct price plan reference (most specific)</li>
                            <li><strong>product_code</strong> - Product code lookup (user-friendly)</li>
                            <li><strong>product_id</strong> - Product ID lookup (simple)</li>
                        </ul>

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
                        <h4 class="h5 fw-bold">Create Invoice with Payment Gateway</h4>
                        <p class="text-muted">Generate payment links automatically when creating invoices. Supports Flutterwave and EcoBank control numbers.</p>
                        
                        <h6 class="fw-bold mt-4">Flutterwave Payment Link</h6>
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
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

                        <h6 class="fw-bold mt-4">Control Number (EcoBank)</h6>
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
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

                        <h6 class="fw-bold mt-4">Both Payment Methods</h6>
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-header d-flex justify-content-between">
                                <span>Request Body</span>
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
  "cancel_url": "https://yourapp.com/payment/cancel"
}</code></pre>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Payment Gateway Option</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>flutterwave</code></td>
                                        <td>Generates card/mobile money payment link via Flutterwave</td>
                                    </tr>
                                    <tr>
                                        <td><code>control_number</code></td>
                                        <td>Generates EcoBank control number for bank payments</td>
                                    </tr>
                                    <tr>
                                        <td><code>both</code></td>
                                        <td>Generates both Flutterwave link AND control number</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="fw-bold mt-4">Response with Payment Details</h6>
                        <div class="card bg-dark text-white">
                            <div class="card-header">Sample Response</div>
                            <div class="card-body">
                                <pre class="mb-0 text-white"><code>{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "id": 123,
    "invoice_number": "INV-2026-00123",
    "total": 250000,
    "tax": 45000,
    "grand_total": 295000,
    "status": "issued",
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/...",
        "tx_ref": "INV-2026-00123-1708956234",
        "expires_at": "2026-03-05T10:30:34.000000Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer"
      },
      "control_number": {
        "reference": "9912345678",
        "amount": 295000,
        "currency": "TZS",
        "expires_at": "2026-03-05T10:30:34.000000Z",
        "payment_instructions": {
          "mobile_banking": "Dial *150*01*9912345678# from your registered mobile number",
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
                    </div>

                    <!-- Get Invoice -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Invoice by ID</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/invoices/{id}</code></p>
                        <p class="text-muted">Retrieve detailed invoice information including items, taxes, payments, and control numbers.</p>
                    </div>

                    <!-- Get Invoices by Product -->
                    <div class="mb-5">
                        <h4 class="h5 fw-bold">Get Invoices by Product</h4>
                        <p><span class="badge bg-primary">GET</span> <code>/api/invoices?product_id={id}&status={status}</code></p>
                        <p class="text-muted">Filter invoices by product and optionally by status (paid, issued, cancelled).</p>
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
