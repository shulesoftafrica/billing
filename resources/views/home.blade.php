@extends('layouts.app')

@section('title', 'Safari API - UCN Payment Platform for Tanzania')

@section('content')

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/">
            <strong>Safari</strong>API
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="#how-it-works">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#pricing">Pricing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('docs') }}">Docs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Dashboard</a>
                </li>
                <li class="nav-item ms-3">
                    <a class="btn btn-primary" href="{{ route('register') }}">Start Free</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="gradient-hero py-5" style="min-height: 600px;">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6 text-white py-5">
                <div class="fade-in-up">
                    <h1 class="hero-headline">
                        Accept payments from all banks & mobile money
                    </h1>
                    <p class="hero-subtext">
                        Integrate UCN once. Accept from every bank and mobile money in Tanzania.<br>
                        No contracts. No VPN. No bureaucracy.
                    </p>
                    <div class="d-flex gap-3 mb-4">
                        <a href="{{ route('register') }}" class="btn btn-success btn-lg">
                            Start Integrating →
                        </a>
                        <a href="{{ route('docs') }}" class="btn btn-outline-light btn-lg">
                            View API Docs →
                        </a>
                    </div>
                    <p class="small text-white-50">
                        <i class="bi bi-check-circle me-2"></i> No credit card required
                        <i class="bi bi-check-circle ms-3 me-2"></i> 5 minute setup
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg border-0" style="background: rgba(255,255,255,0.95);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold" style="color: var(--dark-navy);">Generate UCN</h6>
                            <span class="badge-success">Live</span>
                        </div>
                        <pre style="margin: 0; background: #0B1F3A; border-radius: 8px;"><code style="color: #94A3B8;">POST /v1/ucn/generate

{
  "amount": 50000,
  "currency": "TZS",
  "customer_ref": "order_123"
}</code></pre>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Control Number:</span>
                            <strong style="color: var(--electric-blue); font-size: 18px;">991234567890</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Status:</span>
                            <span class="badge-success small">Payment Received</span>
                        </div>
                        <div class="alert alert-success mb-0" style="background: rgba(0, 196, 140, 0.1); border: none;">
                            <small><i class="bi bi-check-circle me-2"></i> Webhook delivered to your endpoint</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Developers Love It -->
<section class="py-5 gradient-section">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Why Developers Love It</h2>
            <p class="section-subtitle">No middlemen. No delays. Just clean API access to Tanzania's payment infrastructure.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-block text-center">
                    <div class="feature-icon icon-blue mx-auto">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <h4 class="fw-bold mb-3">5 Minute Setup</h4>
                    <p class="text-muted">No paperwork. Create account. Get API key. Start accepting payments.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-block text-center">
                    <div class="feature-icon icon-blue mx-auto">
                        <i class="bi bi-globe"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Accept Everywhere</h4>
                    <p class="text-muted">All banks. All mobile money. One simple integration.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-block text-center">
                    <div class="feature-icon icon-green mx-auto">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Earn 1% Float</h4>
                    <p class="text-muted">Unlike aggregators that charge you, we reward you with revenue share.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-block text-center">
                    <div class="feature-icon icon-navy mx-auto">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Bank Grade</h4>
                    <p class="text-muted">Powered by SafariBank infrastructure. Enterprise security.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5" id="how-it-works">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">Three steps to accept payments from all Tanzanian banks and mobile money</p>
        </div>
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="feature-icon icon-blue mx-auto mb-4" style="width: 80px; height: 80px; font-size: 36px;">
                        <strong>1</strong>
                    </div>
                    <h4 class="fw-bold mb-3">Create Developer Account</h4>
                    <p class="text-muted">Sign up in minutes. Instant approval. No waiting for contracts or legal reviews.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="feature-icon icon-blue mx-auto mb-4" style="width: 80px; height: 80px; font-size: 36px;">
                        <strong>2</strong>
                    </div>
                    <h4 class="fw-bold mb-3">Generate UCN via API</h4>
                    <p class="text-muted">Simple REST API. Generate control numbers programmatically for any amount.</p>
                    <code style="display: block; margin-top: 12px;">POST /v1/ucn/generate</code>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="feature-icon icon-green mx-auto mb-4" style="width: 80px; height: 80px; font-size: 36px;">
                        <strong>3</strong>
                    </div>
                    <h4 class="fw-bold mb-3">Receive Payment Webhook</h4>
                    <p class="text-muted">Real-time notifications when customers pay. Instant settlement confirmation.</p>
                    <code style="display: block; margin-top: 12px;">POST /webhook/payment-success</code>
                </div>
            </div>
        </div>
        
        <!-- Flow Diagram -->
        <div class="mt-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4" style="background: #F8FAFC;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 16px;">
                        <div class="text-center">
                            <div class="feature-icon icon-blue mx-auto mb-2" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="bi bi-code-square"></i>
                            </div>
                            <small class="fw-bold">Your App</small>
                        </div>
                        <i class="bi bi-arrow-right" style="font-size: 24px; color: var(--electric-blue);"></i>
                        <div class="text-center">
                            <div class="feature-icon icon-blue mx-auto mb-2" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="bi bi-cloud"></i>
                            </div>
                            <small class="fw-bold">Safari API</small>
                        </div>
                        <i class="bi bi-arrow-right" style="font-size: 24px; color: var(--electric-blue);"></i>
                        <div class="text-center">
                            <div class="feature-icon icon-blue mx-auto mb-2" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="bi bi-router"></i>
                            </div>
                            <small class="fw-bold">TIPS</small>
                        </div>
                        <i class="bi bi-arrow-right" style="font-size: 24px; color: var(--electric-blue);"></i>
                        <div class="text-center">
                            <div class="feature-icon icon-green mx-auto mb-2" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="bi bi-bank"></i>
                            </div>
                            <small class="fw-bold">All Banks/MNO</small>
                        </div>
                        <i class="bi bi-arrow-left" style="font-size: 24px; color: var(--emerald-green);"></i>
                        <div class="text-center">
                            <div class="feature-icon icon-green mx-auto mb-2" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <small class="fw-bold">Webhook</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Code Snippet Section -->
<section class="py-5 gradient-section">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Simple, Clean API</h2>
            <p class="section-subtitle">Production-ready in minutes. No complex SDK or dependencies required.</p>
        </div>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="position-relative">
                    <button class="copy-btn" onclick="copyCode()">
                        <i class="bi bi-clipboard me-2"></i> Copy
                    </button>
                    <pre><code id="code-example">curl -X POST https://api.safaribank.africa/v1/ucn \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{
    "amount": 50000,
    "currency": "TZS",
    "customer_reference": "order_123",
    "description": "Invoice Payment"
  }'</code></pre>
                </div>
                <div class="mt-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body" style="background: white;">
                            <h6 class="fw-bold mb-3" style="color: var(--dark-navy);">Response:</h6>
                            <pre style="background: #F8FAFC; color: var(--dark-navy); margin: 0;"><code>{
  "success": true,
  "ucn": "991234567890",
  "amount": 50000,
  "currency": "TZS",
  "expires_at": "2026-02-19T18:00:00Z",
  "status": "pending"
}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Revenue Share Highlight -->
<section class="py-5" id="pricing">
    <div class="container py-5">
        <div class="text-center mb-5">
            <div class="badge-success mb-3" style="font-size: 16px;">
                <i class="bi bi-star-fill me-2"></i> Developers Earn 1% Float Revenue
            </div>
            <h2 class="section-title">We Don't Charge. We Pay You.</h2>
            <p class="section-subtitle">Other platforms charge transaction fees. We share revenue with you.</p>
        </div>
        
        <!-- Comparison Table -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="comparison-table">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40%;"></th>
                                <th class="text-center" style="width: 30%;">Payment Aggregators</th>
                                <th class="text-center highlight" style="width: 30%; background: var(--emerald-green);">Safari API</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Transaction Fee</strong></td>
                                <td class="text-center">1–3% per transaction</td>
                                <td class="text-center highlight">0% 🎉</td>
                            </tr>
                            <tr style="background: #F8FAFC;">
                                <td><strong>Revenue Share</strong></td>
                                <td class="text-center">Nothing for you</td>
                                <td class="text-center highlight">You earn 1% float</td>
                            </tr>
                            <tr>
                                <td><strong>Approval Time</strong></td>
                                <td class="text-center">2-4 weeks</td>
                                <td class="text-center highlight">Instant</td>
                            </tr>
                            <tr style="background: #F8FAFC;">
                                <td><strong>VPN Setup Required</strong></td>
                                <td class="text-center">Yes</td>
                                <td class="text-center highlight">No</td>
                            </tr>
                            <tr>
                                <td><strong>Integration Support</strong></td>
                                <td class="text-center">Email tickets</td>
                                <td class="text-center highlight">Developer community</td>
                            </tr>
                            <tr style="background: #F8FAFC;">
                                <td><strong>All Banks + Mobile Money</strong></td>
                                <td class="text-center">Separate contracts</td>
                                <td class="text-center highlight">One API ✓</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center mt-5">
                    <a href="{{ route('register') }}" class="btn btn-success btn-lg">
                        Start Earning Today →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Supported Channels -->
<section class="py-5 gradient-section">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title">One UCN. Accept From Everywhere.</h2>
            <p class="section-subtitle">All major banks and mobile money operators supported</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                    <svg width="50" height="50" viewBox="0 0 50 50" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        <text x="25" y="32" font-family="Arial, sans-serif" font-size="16" font-weight="bold" fill="white" text-anchor="middle">NMB</text>
                    </svg>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">NMB Bank</p>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, #065f46 0%, #10b981 100%);">
                    <svg width="50" height="50" viewBox="0 0 50 50" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        <text x="25" y="32" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="white" text-anchor="middle">CRDB</text>
                    </svg>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">CRDB Bank</p>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, #7c2d12 0%, #ea580c 100%);">
                    <svg width="50" height="50" viewBox="0 0 50 50" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        <text x="25" y="32" font-family="Arial, sans-serif" font-size="16" font-weight="bold" fill="white" text-anchor="middle">NBC</text>
                    </svg>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">NBC Bank</p>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%);">
                    <svg width="50" height="50" viewBox="0 0 50 50" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        <text x="25" y="32" font-family="Arial, sans-serif" font-size="13" font-weight="bold" fill="white" text-anchor="middle">Equity</text>
                    </svg>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">Equity Bank</p>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, var(--electric-blue) 0%, #60a5fa 100%);">
                    <i class="bi bi-bank2" style="font-size: 28px; color: white;"></i>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">All Banks</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center mt-4">
            <div class="col-6 col-md-3 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, #E60012 0%, #ff1a1a 100%);">
                    <svg width="60" height="40" viewBox="0 0 100 50" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        <text x="50" y="32" font-family="Arial, sans-serif" font-size="20" font-weight="900" fill="white" text-anchor="middle">M-PESA</text>
                    </svg>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">Vodacom M-Pesa</p>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, #ED1C24 0%, #ff3d3d 100%);">
                    <svg width="60" height="40" viewBox="0 0 100 50" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        <ellipse cx="20" cy="25" rx="12" ry="20" fill="white" opacity="0.9"/>
                        <text x="50" y="32" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="white" text-anchor="middle">airtel</text>
                    </svg>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">Airtel Money</p>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, #0066B2 0%, #0080e0 100%);">
                    <svg width="60" height="40" viewBox="0 0 100 50" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        <rect x="15" y="15" width="20" height="20" rx="3" fill="white" opacity="0.9"/>
                        <text x="50" y="32" font-family="Arial, sans-serif" font-size="22" font-weight="bold" fill="white" text-anchor="middle">tigo</text>
                    </svg>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">Tigo Pesa</p>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="channel-icon mx-auto" style="background: linear-gradient(135deg, #FF6600 0%, #ff8533 100%);">
                    <svg width="60" height="40" viewBox="0 0 100 50" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                        <circle cx="25" cy="25" r="15" fill="white" opacity="0.3"/>
                        <circle cx="25" cy="25" r="10" fill="white" opacity="0.5"/>
                        <text x="55" y="32" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="white" text-anchor="middle">Halo</text>
                    </svg>
                </div>
                <p class="text-center small mt-2 mb-0 fw-bold">HaloPesa</p>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <p class="lead mb-0" style="color: var(--dark-navy);">
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                <strong>One integration. Every payment method in Tanzania.</strong>
            </p>
        </div>
    </div>
</section>

<!-- Developer Dashboard Preview -->
<section class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Built for Developers</h2>
            <p class="section-subtitle">Clean dashboard. Clear documentation. No confusion.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon icon-blue mb-3">
                            <i class="bi bi-key"></i>
                        </div>
                        <h5 class="fw-bold mb-3">API Keys Management</h5>
                        <p class="text-muted">Generate test and production keys instantly. Rotate keys without downtime.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon icon-blue mb-3">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Live Transaction Analytics</h5>
                        <p class="text-muted">Real-time transaction monitoring, success rates, and revenue tracking.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon icon-green mb-3">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Float Earnings Report</h5>
                        <p class="text-muted">Track your 1% float revenue share in real-time. Transparent reporting.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="feature-icon icon-navy mb-3">
                            <i class="bi bi-webhook"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Webhook Configuration</h5>
                        <p class="text-muted">Set up payment webhooks with retry logic and delivery logs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Security & Compliance -->
<section class="py-5 gradient-section">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Enterprise-Grade Security</h2>
            <p class="section-subtitle">Built on SafariBank infrastructure</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="feature-icon icon-navy mx-auto mb-3">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h6 class="fw-bold">TLS Encryption</h6>
                <p class="text-muted small">All API calls encrypted end-to-end</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon icon-navy mx-auto mb-3">
                    <i class="bi bi-bank"></i>
                </div>
                <h6 class="fw-bold">Bank Integration</h6>
                <p class="text-muted small">Direct connection to TIPS infrastructure</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon icon-navy mx-auto mb-3">
                    <i class="bi bi-eye"></i>
                </div>
                <h6 class="fw-bold">Fraud Monitoring</h6>
                <p class="text-muted small">Real-time transaction anomaly detection</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon icon-navy mx-auto mb-3">
                    <i class="bi bi-file-text"></i>
                </div>
                <h6 class="fw-bold">Audit Logs</h6>
                <p class="text-muted small">Complete transaction audit trail</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title">Frequently Asked Questions</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Do I need to sign a contract?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No. Create an account and start integrating immediately. No paperwork, no legal reviews.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do I need VPN to banks?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No. Our API is fully cloud-based. No VPN configuration or network setup required.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I accept from all mobile money?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. One UCN works for all banks and all mobile money operators in Tanzania.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                How do I earn 1% float revenue?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                When customers pay via UCN, funds are held in our float account briefly before settlement. You earn 1% of the interest earned on that float. It's automatic revenue sharing—no extra work required.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                What about testing?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Full sandbox environment available. Test all payment flows without real money before going live.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                How long does settlement take?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                T+0 settlement available. Funds can be settled to your account the same day for most transactions.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="gradient-hero py-5">
    <div class="container text-center py-5">
        <h2 class="display-4 fw-bold text-white mb-4">
            Build the future of payments in Tanzania
        </h2>
        <p class="lead text-white-50 mb-5">
            Join developers who are revolutionizing payment acceptance
        </p>
        <div class="d-flex gap-3 justify-content-center mb-4">
            <a href="{{ route('register') }}" class="btn btn-success btn-lg px-5">
                Start Free
            </a>
            <a href="{{ route('docs') }}" class="btn btn-outline-light btn-lg px-5">
                Read Docs
            </a>
        </div>
        <p class="text-white-50 small">
            <i class="bi bi-check-circle me-2"></i> No credit card required
            <i class="bi bi-check-circle ms-3 me-2"></i> API key in 30 seconds
            <i class="bi bi-check-circle ms-3 me-2"></i> Cancel anytime
        </p>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">SafariAPI</h5>
                <p class="text-white-50">Tanzania's first developer-first bank-powered UCN payment API.</p>
                <p class="small text-white-50 mb-0">
                    <i class="bi bi-bank me-2"></i> Powered by SafariBank
                </p>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3">Product</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Pricing</a></li>
                    <li class="mb-2"><a href="{{ route('docs') }}" class="text-white-50 text-decoration-none">Documentation</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">API Reference</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Status</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3">Resources</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Support</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Guides</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Community</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">GitHub</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3">Company</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">About</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Blog</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Careers</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3">Legal</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('privacy') }}" class="text-white-50 text-decoration-none">Privacy</a></li>
                    <li class="mb-2"><a href="{{ route('terms') }}" class="text-white-50 text-decoration-none">Terms</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Security</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="small text-white-50 mb-0">&copy; {{ date('Y') }} SafariBank. Licensed Payment Service Provider - Bank of Tanzania</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="small text-white-50 mb-0">Made with ❤️ for Tanzanian developers</p>
            </div>
        </div>
    </div>
</footer>

@push('scripts')
<script>
function copyCode() {
    const code = document.getElementById('code-example').textContent;
    navigator.clipboard.writeText(code).then(() => {
        const btn = event.target.closest('.copy-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check me-2"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    });
}
</script>
@endpush

@endsection
