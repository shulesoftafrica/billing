<aside class="sidebar" id="sidebar">
    <h2 class="logo">API Documentation</h2>
    <input id="endpointSearch" class="search" type="text" placeholder="Search endpoints...">

    <nav id="sidebarNav">
        {{-- Authentication Section --}}
        <div class="nav-section">
            <a href="#authentication-guide" class="nav-section-toggle">
                <span>🔐 Authentication</span>
                <span>→</span>
            </a>
        </div>

        {{-- Products Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>📦 Products</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#list-all-products" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>List All Products</span>
                </a>
                <a href="#create-product" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Products</span>
                </a>
                <a href="#get-single-product" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Single Product</span>
                </a>
            </div>
        </div>

        {{-- Invoices Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>📄 Invoices</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#list-invoices" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>List All Invoices</span>
                </a>
                <a href="#create-invoice" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Invoice</span>
                </a>
                <a href="#get-invoice" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Single Invoice</span>
                </a>
                <a href="#cancel-invoice" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Cancel Invoice</span>
                </a>
            </div>
        </div>

        {{-- Subscriptions Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>🔄 Subscriptions</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#list-subscriptions" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>List Subscriptions</span>
                </a>
                <a href="#create-subscription" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Subscription</span>
                </a>
                <a href="#get-subscription" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Subscription</span>
                </a>
                <a href="#cancel-subscription" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Cancel Subscription</span>
                </a>
            </div>
        </div>

        {{-- Customers Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>👥 Customers</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#list-customers" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>List Customers</span>
                </a>
                <a href="#create-customer" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Customer</span>
                </a>
                <a href="#get-customer" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Customer</span>
                </a>
                <a href="#update-customer" class="nav-link">
                    <span class="method-badge method-put">PUT</span>
                    <span>Update Customer</span>
                </a>
            </div>
        </div>

        {{-- Payments Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>💳 Payments</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#list-payments" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>List Payments</span>
                </a>
                <a href="#process-payment" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Process Payment</span>
                </a>
                <a href="#get-payment" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Payment</span>
                </a>
            </div>
        </div>

        {{-- Taxes Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>📊 Tax Rates</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#list-tax-rates" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>List Tax Rates</span>
                </a>
                <a href="#create-tax-rate" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Tax Rate</span>
                </a>
                <a href="#get-tax-rate" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Tax Rate</span>
                </a>
            </div>
        </div>

        {{-- Webhooks Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>🔔 Webhooks</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#list-webhooks" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>List Webhooks</span>
                </a>
                <a href="#create-webhook" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Webhook</span>
                </a>
                <a href="#webhook-events" class="nav-link">
                    <span>→</span>
                    <span>Webhook Events</span>
                </a>
            </div>
        </div>

        {{-- Organizations Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>🏢 Organizations</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#list-organizations" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>List Organizations</span>
                </a>
                <a href="#create-organization" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Organization</span>
                </a>
                <a href="#get-organization" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Organization</span>
                </a>
            </div>
        </div>

        {{-- Wallets Section --}}
        <div class="nav-section">
            <button class="nav-section-toggle" type="button">
                <span>💰 Wallets & Usage</span>
                <span>▼</span>
            </button>
            <div class="nav-links">
                <a href="#create-usage-product" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Usage Product</span>
                </a>
                <a href="#create-wallet-invoice" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Create Wallet Invoice</span>
                </a>
                <a href="#record-usage" class="nav-link">
                    <span class="method-badge method-post">POST</span>
                    <span>Record Usage</span>
                </a>
                <a href="#get-usage-balance" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Usage Balance</span>
                </a>
                <a href="#get-usage-report" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Usage Report</span>
                </a>
                <a href="#get-usage-history" class="nav-link">
                    <span class="method-badge method-get">GET</span>
                    <span>Get Usage History</span>
                </a>
            </div>
        </div>
    </nav>
</aside>
