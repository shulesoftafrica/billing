{{-- resources/views/billing/payment.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Pay Invoice {{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;1,300&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:       #0e0f0c;
            --paper:     #f5f2eb;
            --cream:     #ede9df;
            --warm:      #e8e3d8;
            --accent:    #1a3a2a;
            --accent2:   #c8873a;
            --muted:     #7a776e;
            --soft:      #b0ad a5;
            --border:    #d8d4c8;
            --success:   #1a3a2a;
            --error:     #8b2020;
            --radius:    14px;
        }

        html, body {
            min-height: 100vh;
            background: var(--paper);
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
        }

        body {
            display: flex;
            flex-direction: column;
            background-image:
                radial-gradient(ellipse at 20% 0%, rgba(200,135,58,0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(26,58,42,0.06) 0%, transparent 50%);
        }

        /* ── HEADER ── */
        .page-header {
            padding: 20px 40px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(245,242,235,0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo {
            font-family: 'Fraunces', serif;
            font-size: 22px;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: -0.02em;
        }

        .secure-tag {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
        }

        .secure-tag svg { width: 13px; height: 13px; }

        /* ── MAIN LAYOUT ── */
        .main {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 480px;
            gap: 0;
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            padding: 48px 40px;
            gap: 60px;
            animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── LEFT: INVOICE DETAILS ── */
        .invoice-panel { display: flex; flex-direction: column; gap: 32px; }

        .section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 14px;
        }

        /* Customer card */
        .customer-card {
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px 24px;
        }

        .customer-name {
            font-family: 'Fraunces', serif;
            font-size: 22px;
            font-weight: 400;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .customer-email {
            font-size: 14px;
            color: var(--muted);
        }

        /* Invoice meta */
        .invoice-meta {
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .invoice-meta-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .invoice-number {
            font-family: 'Fraunces', serif;
            font-size: 18px;
            color: var(--ink);
        }

        .invoice-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: rgba(200,135,58,0.12);
            color: var(--accent2);
            border: 1px solid rgba(200,135,58,0.25);
            letter-spacing: 0.04em;
        }

        .invoice-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 18px 24px;
            gap: 16px;
            border-bottom: 1px solid var(--border);
        }

        .date-item {}
        .date-label { font-size: 11px; color: var(--muted); margin-bottom: 4px; font-weight: 500; }
        .date-value { font-size: 14px; color: var(--ink); font-weight: 500; }

        /* Line items */
        .line-items { padding: 0 24px 8px; }
        .line-items-title { padding: 16px 0 10px; }

        .line-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            gap: 16px;
        }

        .line-item:last-child { border-bottom: none; }

        .item-info {}
        .item-name { font-size: 14px; color: var(--ink); font-weight: 500; margin-bottom: 2px; }
        .item-desc { font-size: 12px; color: var(--muted); }
        .item-price { font-size: 14px; font-weight: 600; color: var(--ink); white-space: nowrap; }

        /* Total */
        .total-block {
            background: var(--accent);
            border-radius: var(--radius);
            padding: 24px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .total-label {
            font-size: 13px;
            color: rgba(255,255,255,0.6);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .total-amount {
            font-family: 'Fraunces', serif;
            font-size: 38px;
            color: #fff;
            font-weight: 300;
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .total-currency {
            font-size: 16px;
            color: rgba(255,255,255,0.5);
            font-family: 'DM Sans', sans-serif;
            margin-right: 6px;
        }

        .total-due-date {
            text-align: right;
        }

        .due-label { font-size: 11px; color: rgba(255,255,255,0.5); margin-bottom: 4px; }
        .due-date  { font-size: 14px; color: rgba(255,255,255,0.85); font-weight: 500; }

        /* ── RIGHT: PAYMENT FORM ── */
        .payment-panel {
            display: flex;
            flex-direction: column;
        }

        .payment-title {
            font-family: 'Fraunces', serif;
            font-size: 28px;
            font-weight: 300;
            color: var(--ink);
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .payment-subtitle {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        /* Stripe Payment Element mounts here */
        #payment-element {
            margin-bottom: 24px;
        }

        /* Error message */
        .error-container {
            background: rgba(139,32,32,0.07);
            border: 1px solid rgba(139,32,32,0.2);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: var(--error);
            margin-bottom: 20px;
            display: none;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.5;
        }

        .error-container.visible { display: flex; }
        .error-container svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }

        /* Pay button */
        .pay-btn {
            width: 100%;
            padding: 18px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.15s, box-shadow 0.2s, background 0.2s;
            letter-spacing: 0.01em;
        }

        .pay-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
        }

        .pay-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(26,58,42,0.3);
        }

        .pay-btn:active:not(:disabled) { transform: translateY(0); }

        .pay-btn:disabled {
            background: #8a9e92;
            cursor: not-allowed;
            transform: none;
        }

        .btn-inner { display: flex; align-items: center; justify-content: center; gap: 10px; }

        .spinner {
            width: 17px; height: 17px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
            flex-shrink: 0;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .pay-btn.loading .spinner { display: block; }
        .pay-btn.loading .btn-label { opacity: 0.7; }

        /* Stripe badge */
        .stripe-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            color: var(--muted);
            margin-top: 18px;
        }

        /* ── SUCCESS STATE ── */
        .success-overlay {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
            flex: 1;
        }

        .success-overlay.visible { display: flex; }

        .success-icon {
            width: 72px; height: 72px;
            background: rgba(26,58,42,0.1);
            border: 1px solid rgba(26,58,42,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px;
            animation: popIn 0.5s cubic-bezier(0.34,1.56,0.64,1) both;
        }

        @keyframes popIn {
            from { transform: scale(0.4); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }

        .success-icon svg { width: 30px; height: 30px; }
        .success-title { font-family: 'Fraunces', serif; font-size: 26px; font-weight: 400; margin-bottom: 10px; color: var(--accent); }
        .success-msg { font-size: 14px; color: var(--muted); line-height: 1.7; max-width: 280px; }

        /* ── FOOTER ── */
        .page-footer {
            padding: 20px 40px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            color: var(--muted);
        }

        @media (max-width: 820px) {
            .main { grid-template-columns: 1fr; padding: 32px 20px; gap: 36px; }
            .page-header { padding: 16px 20px; }
        }
    </style>
</head>
<body>

{{-- HEADER --}}
<header class="page-header">
    <div class="logo">{{ config('app.name', 'BillFlow') }}</div>
    <div class="secure-tag">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        Secured by Stripe
    </div>
</header>

{{-- MAIN --}}
<main class="main">

    {{-- LEFT: Invoice Details --}}
    <div class="invoice-panel">

        {{-- Customer --}}
        <div>
            <div class="section-label">Billed to</div>
            <div class="customer-card">
                <div class="customer-name">{{ $customer->name }}</div>
                <div class="customer-email">{{ $customer->email }}</div>
            </div>
        </div>

        {{-- Invoice Meta --}}
        <div>
            <div class="section-label">Invoice details</div>
            <div class="invoice-meta">
                <div class="invoice-meta-header">
                    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    <div class="invoice-badge">UNPAID</div>
                </div>
                <div class="invoice-dates">
                    <div class="date-item">
                        <div class="date-label">Issue date</div>
                        <div class="date-value">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</div>
                    </div>
                    <div class="date-item">
                        <div class="date-label">Due date</div>
                        <div class="date-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</div>
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="line-items">
                    <div class="section-label line-items-title">Items</div>
                    @foreach($invoice->items as $item)
                    <div class="line-item">
                        <div class="item-info">
                            <div class="item-name">{{ $item->name }}</div>
                            @if($item->description)
                            <div class="item-desc">{{ $item->description }}</div>
                            @endif
                        </div>
                        <div class="item-price">
                            {{ strtoupper($invoice->currency) }} {{ number_format($item->amount, 2) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Total --}}
        <div class="total-block">
            <div>
                <div class="total-label">Total due</div>
                <div class="total-amount">
                    <span class="total-currency">{{ strtoupper($invoice->currency) }}</span>{{ number_format($invoice->total_amount, 2) }}
                </div>
            </div>
            <div class="total-due-date">
                <div class="due-label">Pay by</div>
                <div class="due-date">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</div>
            </div>
        </div>

    </div>

    {{-- RIGHT: Payment Form --}}
    <div class="payment-panel">

        {{-- Form state --}}
        <div id="form-state">
            <div class="payment-title">Complete payment</div>
            <div class="payment-subtitle">
                Enter your card details below. Your payment is encrypted and secure.
            </div>

            <div class="error-container" id="error-container">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12" y2="16"/>
                </svg>
                <span id="error-message">An error occurred.</span>
            </div>

            {{-- Stripe Payment Element mounts here --}}
            <div id="payment-element"></div>

            <button class="pay-btn" id="pay-btn" type="button">
                <div class="btn-inner">
                    <div class="spinner"></div>
                    <span class="btn-label">
                        Pay {{ strtoupper($invoice->currency) }} {{ number_format($invoice->total_amount, 2) }}
                    </span>
                </div>
            </button>

            <div class="stripe-note">
                🔒 Payments processed securely by Stripe
            </div>
        </div>

        {{-- Success state --}}
        <div class="success-overlay" id="success-state">
            <div class="success-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#1a3a2a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="success-title">Payment successful</div>
            <div class="success-msg">
                Thank you! Your payment for invoice <strong>{{ $invoice->invoice_number }}</strong>
                has been received. A receipt has been sent to {{ $customer->email }}.
            </div>
        </div>

    </div>
</main>

{{-- FOOTER --}}
<footer class="page-footer">
    <svg width="40" viewBox="0 0 60 25" fill="none"><text x="0" y="20" font-family="Arial" font-weight="800" font-size="22" fill="#b0ada5">stripe</text></svg>
    · SSL encrypted · PCI compliant
</footer>

<script>
    // ── Config from Laravel (passed via controller) ──────────────────
    const PUBLISHABLE_KEY = "{{ $stripePublishableKey }}";
    const CLIENT_SECRET   = "{{ $clientSecret }}";
    const RETURN_URL      = "{{ route('billing.payment.complete', $invoice->id) }}";

    // ── Init Stripe ───────────────────────────────────────────────────
    const stripe   = Stripe(PUBLISHABLE_KEY);
    const elements = stripe.elements({
        clientSecret: CLIENT_SECRET,
        appearance: {
            theme: 'stripe',
            variables: {
                colorPrimary:        '#1a3a2a',
                colorBackground:     '#f5f2eb',
                colorText:           '#0e0f0c',
                colorDanger:         '#8b2020',
                fontFamily:          'DM Sans, sans-serif',
                borderRadius:        '10px',
                spacingUnit:         '4px',
            },
            rules: {
                '.Input': {
                    border:          '1px solid #d8d4c8',
                    backgroundColor: '#ede9df',
                    boxShadow:       'none',
                    padding:         '12px 14px',
                },
                '.Input:focus': {
                    border:     '1px solid #1a3a2a',
                    boxShadow:  '0 0 0 3px rgba(26,58,42,0.12)',
                },
                '.Label': {
                    fontWeight: '500',
                    fontSize:   '12px',
                    color:      '#7a776e',
                },
            }
        }
    });

    // ── Mount Payment Element ─────────────────────────────────────────
    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    // ── Handle Pay Button ─────────────────────────────────────────────
    const payBtn        = document.getElementById('pay-btn');
    const errorBox      = document.getElementById('error-container');
    const errorMsg      = document.getElementById('error-message');
    const formState     = document.getElementById('form-state');
    const successState  = document.getElementById('success-state');

    function showError(message) {
        errorMsg.textContent = message;
        errorBox.classList.add('visible');
        payBtn.classList.remove('loading');
        payBtn.disabled = false;
    }

    function hideError() {
        errorBox.classList.remove('visible');
    }

    payBtn.addEventListener('click', async () => {
        hideError();
        payBtn.classList.add('loading');
        payBtn.disabled = true;

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: RETURN_URL,
            },
            // Don't redirect if payment succeeds — handle inline
            redirect: 'if_required',
        });

        if (error) {
            // Card declined, validation error, etc.
            showError(error.message);
            return;
        }

        // Payment succeeded (no redirect required e.g. card payment)
        formState.style.display    = 'none';
        successState.classList.add('visible');
    });
</script>

</body>
</html>
