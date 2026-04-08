@extends('dashboard.layout')

@section('title', 'Overview')
@section('page-title', 'Overview')

@section('content')

{{-- ── 4 Stat Cards ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Total API Calls --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-start gap-3">
            <div class="stat-icon" style="background:#eff6ff;color:#2563eb;">
                <i class="bi bi-activity"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="stat-label">Total API Calls</div>
                <div class="stat-value">{{ number_format($stats['total_api_calls']) }}</div>
                <div class="stat-sub">
                    @if($stats['api_calls_trend'] >= 0)
                        <span style="color:#10b981;"><i class="bi bi-arrow-up-right"></i> {{ abs($stats['api_calls_trend']) }}%</span>
                    @else
                        <span style="color:#ef4444;"><i class="bi bi-arrow-down-right"></i> {{ abs($stats['api_calls_trend']) }}%</span>
                    @endif
                    this week
                </div>
            </div>
        </div>
    </div>

    {{-- Total Collected --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-start gap-3">
            <div class="stat-icon" style="background:#ecfdf5;color:#059669;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="stat-label">Total Collected</div>
                <div class="stat-value" style="font-size:1.25rem;">
                    TZS {{ number_format($stats['total_collected'], 0) }}
                </div>
                <div class="stat-sub">All paid invoices</div>
                @if($stats['market_savings'] > 0)
                <div class="mt-1" style="font-size:.72rem;font-weight:600;color:#059669;background:#ecfdf5;border-radius:4px;padding:.15rem .4rem;display:inline-block;">
                    <i class="bi bi-piggy-bank me-1"></i>Market Savings: TZS {{ number_format($stats['market_savings'], 0) }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Total Invoices --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-start gap-3">
            <div class="stat-icon" style="background:#fefce8;color:#d97706;">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="stat-label">Total Invoices</div>
                <div class="stat-value">{{ number_format($stats['total_invoices']) }}</div>
                <div class="stat-sub">
                    <span style="color:#f59e0b;">{{ number_format($stats['pending_invoices']) }} pending</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Commission --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-start gap-3">
            <div class="stat-icon" style="background:#fdf4ff;color:#9333ea;">
                <i class="bi bi-stars"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="stat-label">Today's Commission</div>
                <div class="stat-value" style="font-size:1.25rem;color:#9333ea;">
                    TZS {{ number_format($stats['todays_commission'], 0) }}
                </div>
                <div class="stat-sub">1% of today's collections</div>
            </div>
        </div>
    </div>

</div>

{{-- ── Recent Invoices ───────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header-flush">
        <i class="bi bi-receipt text-muted"></i>
        <h6>Recent Invoices</h6>
        <span class="ms-auto" style="font-size:.78rem;color:#94a3b8;">Last 10 invoices</span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentInvoices as $invoice)
                <tr>
                    <td class="mono">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</td>
                    <td>{{ $invoice->customer->name ?? '—' }}</td>
                    <td class="mono">{{ $invoice->currency ?? 'TZS' }} {{ number_format($invoice->total, 2) }}</td>
                    <td>
                        @php
                            $statusMap = [
                                'paid'    => 'badge-success',
                                'pending' => 'badge-warning',
                                'overdue' => 'badge-danger',
                                'draft'   => 'badge-info',
                            ];
                            $cls = $statusMap[$invoice->status] ?? 'badge-info';
                        @endphp
                        <span class="{{ $cls }}">{{ ucfirst($invoice->status) }}</span>
                    </td>
                    <td style="color:#64748b;font-size:.8rem;">{{ $invoice->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4" style="color:#94a3b8;font-size:.875rem;">
                        <i class="bi bi-inbox d-block mb-2" style="font-size:1.5rem;"></i>
                        No invoices yet. Start by creating customers and invoices via the API.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Quick Start ───────────────────────────────────────────────────────── --}}
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header-flush">
                <i class="bi bi-rocket-takeoff text-primary"></i>
                <h6>Quick Start</h6>
            </div>
            <div class="p-3">
                <div class="code-block">curl -X POST {{ url('/api/v1/auth/token') }} \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET"
  }'</div>
                <a href="{{ route('dashboard.organization') }}" class="btn btn-sm mt-2"
                   style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:6px;font-size:.8rem;padding:.3rem .75rem;">
                    <i class="bi bi-key me-1"></i> Get my API credentials →
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header-flush align-items-center">
                <i class="bi bi-graph-up-arrow" style="color:#9333ea;"></i>
                <h6 class="mb-0">Commission Tracker</h6>

                {{-- Dynamic Tier Badge --}}
                @php
                    $badgeStyle = match($stats['tier']) {
                        'silver'   => 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;',
                        'platinum' => 'background:linear-gradient(135deg,#fef3c7,#ede9fe);color:#6d28d9;border:1px solid #c4b5fd;',
                        default    => 'background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;',
                    };
                    $badgeLabel = match($stats['tier']) {
                        'silver'   => '★ Silver Partner',
                        'platinum' => '✦ Platinum Partner',
                        default    => 'Standard',
                    };
                @endphp
                <span class="ms-2" style="font-size:.68rem;font-weight:700;padding:.2rem .65rem;border-radius:999px;letter-spacing:.03em;{{ $badgeStyle }}">
                    {{ $badgeLabel }}
                </span>
            </div>

            <div class="p-3">

                {{-- ── Goal Gradient Progress Bar ───────────────────────── --}}
                @if($stats['tier'] === 'platinum')
                    <div class="mb-3 p-2" style="background:linear-gradient(135deg,#fef3c7,#ede9fe);border-radius:8px;text-align:center;">
                        <span style="font-size:.78rem;font-weight:700;color:#6d28d9;">
                            ✦ Platinum Partner — 1.0% p.a. payout unlocked
                        </span>
                    </div>
                @else
                    <div class="mb-3" id="tier-progress-block">
                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                            <span style="font-size:.73rem;color:#64748b;">
                                Progress toward
                                <strong style="color:{{ $stats['tier'] === 'silver' ? '#6d28d9' : '#1d4ed8' }};">
                                    {{ $stats['tier_next_label'] }}
                                </strong>
                            </span>
                            <span style="font-size:.73rem;font-weight:700;color:#2ecc71;">
                                {{ number_format($stats['float_bar_pct'], 1) }}%
                            </span>
                        </div>

                        {{-- Single bar over 0→1B with milestone markers --}}
                        <div style="position:relative;height:8px;background:#f1f5f9;border-radius:4px;" id="commission-bar-wrap">
                            {{-- Filled portion --}}
                            <div style="height:100%;width:{{ $stats['float_bar_pct'] }}%;background:linear-gradient(90deg,#2ecc71,#16a34a);border-radius:4px;transition:width .8s ease;"></div>
                            {{-- 500M milestone marker --}}
                            <div style="position:absolute;top:-3px;left:50%;width:2px;height:14px;background:#cbd5e0;border-radius:1px;" title="500M TZS – Silver Partner">
                                <div style="position:absolute;top:16px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:.6rem;color:#94a3b8;font-weight:600;">500M</div>
                            </div>
                            {{-- 1B milestone marker --}}
                            <div style="position:absolute;top:-3px;right:0;width:2px;height:14px;background:#a78bfa;border-radius:1px;" title="1B TZS – Platinum Partner">
                                <div style="position:absolute;top:16px;right:0;transform:translateX(50%);white-space:nowrap;font-size:.6rem;color:#a78bfa;font-weight:600;">1B</div>
                            </div>
                        </div>

                        {{-- Milestone labels row --}}
                        <div class="d-flex justify-content-between mt-4" style="font-size:.65rem;">
                            <span style="color:#94a3b8;">TZS 0</span>
                            <span style="color:#1d4ed8;font-weight:600;">Silver 500M</span>
                            <span style="color:#6d28d9;font-weight:600;">Platinum 1B</span>
                        </div>
                    </div>
                @endif

                {{-- ── Earnings row with "Commission Lost" hover tooltip ──── --}}
                <div id="commission-tracker-row"
                     style="cursor:default;"
                     data-tier="{{ $stats['tier'] }}"
                     data-remaining="{{ $stats['tier_remaining'] }}"
                     data-next-label="{{ $stats['tier_next_label'] }}">

                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span style="font-size:.83rem;color:#64748b;">Today's earnings</span>
                        <span style="font-weight:700;color:#9333ea;">TZS {{ number_format($stats['todays_commission'], 0) }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span style="font-size:.78rem;color:#94a3b8;">Total accrued reward</span>
                        <span style="font-weight:600;font-size:.85rem;color:#334155;">TZS {{ number_format($stats['accrued_reward'], 0) }}</span>
                    </div>

                    {{-- Commission Lost tooltip (visible on hover via JS) --}}
                    @if($stats['tier'] !== 'platinum')
                    <div id="commission-lost-hint" style="display:none;background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;padding:.45rem .65rem;margin-bottom:.5rem;">
                        <span style="font-size:.73rem;color:#c2410c;">
                            <i class="bi bi-lightning-charge-fill me-1"></i>
                            You are <strong>TZS {{ number_format($stats['tier_remaining'], 0) }}</strong> away
                            from unlocking your next reward: <strong>{{ $stats['tier_next_label'] }}</strong>.
                        </span>
                    </div>
                    @endif
                </div>

                <p style="font-size:.73rem;color:#94a3b8;margin:6px 0 0;">
                    You earn <strong>1%</strong> on every float deposit collected through your integration.
                    <a href="{{ route('api.docs') }}" style="color:#2563eb;" target="_blank">Learn more →</a>
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Commission Tracker interactivity --}}
<script>
(function () {
    const row  = document.getElementById('commission-tracker-row');
    const hint = document.getElementById('commission-lost-hint');
    if (!row || !hint) return;

    row.addEventListener('mouseenter', () => { hint.style.display = 'block'; });
    row.addEventListener('mouseleave', () => { hint.style.display = 'none';  });

    const barBlock = document.getElementById('tier-progress-block');
    if (barBlock) {
        barBlock.addEventListener('mouseenter', () => { hint.style.display = 'block'; });
        barBlock.addEventListener('mouseleave', () => { hint.style.display = 'none';  });
    }
})();
</script>
@endsection