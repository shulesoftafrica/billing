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
            <div class="card-header-flush">
                <i class="bi bi-graph-up-arrow" style="color:#9333ea;"></i>
                <h6>Commission Tracker</h6>
            </div>
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span style="font-size:.83rem;color:#64748b;">Today's earnings</span>
                    <span style="font-weight:700;color:#9333ea;">TZS {{ number_format($stats['todays_commission'], 0) }}</span>
                </div>
                <div class="progress mb-3" style="height:6px;border-radius:3px;">
                    @php $pct = min(($stats['todays_commission'] / max($stats['todays_commission'] + 1000, 1)) * 100, 100); @endphp
                    <div class="progress-bar" style="width:{{ $pct }}%;background:#9333ea;border-radius:3px;"></div>
                </div>
                <p style="font-size:.78rem;color:#94a3b8;margin:0;">
                    You earn <strong>1%</strong> on every float deposit collected through your integration.
                    <a href="{{ route('api.docs') }}" style="color:#2563eb;" target="_blank">Learn more →</a>
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
