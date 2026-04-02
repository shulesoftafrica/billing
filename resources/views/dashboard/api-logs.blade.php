@extends('dashboard.layout')

@section('title', 'API Logs')
@section('page-title', 'API Logs')

@section('content')

{{-- ── Summary chips ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-label">Total Requests</div>
            <div class="stat-value">{{ number_format($totalRequests) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-label" style="color:#059669;">Successful</div>
            <div class="stat-value" style="color:#059669;">{{ number_format($successCount) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-label" style="color:#ef4444;">Errors</div>
            <div class="stat-value" style="color:#ef4444;">{{ number_format($errorCount) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-label">Avg Latency</div>
            <div class="stat-value">{{ $avgLatency ? round($avgLatency) . 'ms' : '—' }}</div>
        </div>
    </div>
</div>

{{-- ── Filters bar ────────────────────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="p-3">
        <form method="GET" action="{{ route('dashboard.api-logs') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#64748b;">Search endpoint</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;">
                        <i class="bi bi-search text-muted" style="font-size:.75rem;"></i>
                    </span>
                    <input type="text" name="search" class="form-control"
                           placeholder="/api/v1/invoices"
                           value="{{ $search ?? '' }}"
                           style="font-family:'IBM Plex Mono',monospace;font-size:.8rem;border-color:#e2e8f0;">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#64748b;">Status</label>
                <select name="filter" class="form-select form-select-sm" style="border-color:#e2e8f0;font-size:.83rem;">
                    <option value="all"     {{ ($filter ?? 'all') === 'all'     ? 'selected' : '' }}>All</option>
                    <option value="success" {{ ($filter ?? '') === 'success' ? 'selected' : '' }}>✅ Success</option>
                    <option value="errors"  {{ ($filter ?? '') === 'errors'  ? 'selected' : '' }}>❌ Errors</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#64748b;">From</label>
                <input type="date" name="from" class="form-control form-control-sm"
                       value="{{ request('from') }}" style="border-color:#e2e8f0;font-size:.83rem;">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#64748b;">To</label>
                <input type="date" name="to" class="form-control form-control-sm"
                       value="{{ request('to') }}" style="border-color:#e2e8f0;font-size:.83rem;">
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm flex-grow-1"
                        style="background:#2563eb;color:#fff;border-radius:6px;font-size:.83rem;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('dashboard.api-logs') }}" class="btn btn-sm"
                   style="background:#f1f5f9;color:#64748b;border-radius:6px;font-size:.83rem;">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Logs table ─────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header-flush">
        <i class="bi bi-terminal text-muted"></i>
        <h6>Request Logs</h6>
        <span class="ms-auto" style="font-size:.78rem;color:#94a3b8;">
            {{ $logs->total() }} {{ Str::plural('request', $logs->total()) }} found
        </span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width:100px;">Time</th>
                    <th style="width:70px;">Method</th>
                    <th>Endpoint</th>
                    <th style="width:90px;">Status</th>
                    <th style="width:85px;">Latency</th>
                    <th>Client ID</th>
                    <th style="width:60px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="log-row" data-id="{{ $log->id }}" style="cursor:pointer;" onclick="toggleDetail({{ $log->id }})">
                    <td class="mono" style="font-size:.75rem;color:#64748b;white-space:nowrap;">
                        {{ $log->created_at->format('H:i:s') }}<br>
                        <span style="font-size:.68rem;color:#94a3b8;">{{ $log->created_at->format('d M') }}</span>
                    </td>
                    <td>
                        @php
                            $methodColors = ['GET'=>'#2563eb','POST'=>'#059669','PUT'=>'#d97706','DELETE'=>'#ef4444','PATCH'=>'#7c3aed'];
                            $mc = $methodColors[$log->method] ?? '#64748b';
                        @endphp
                        <span class="mono" style="font-size:.72rem;font-weight:700;color:{{ $mc }};">{{ $log->method }}</span>
                    </td>
                    <td class="mono" style="font-size:.8rem;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $log->endpoint }}
                    </td>
                    <td>
                        @if($log->success)
                            <span class="badge-success"><i class="bi bi-check-circle me-1"></i>{{ $log->status_code }}</span>
                        @elseif($log->status_code >= 500)
                            <span class="badge-danger"><i class="bi bi-x-circle me-1"></i>{{ $log->status_code }}</span>
                        @else
                            <span class="badge-warning"><i class="bi bi-exclamation-circle me-1"></i>{{ $log->status_code }}</span>
                        @endif
                    </td>
                    <td class="mono" style="font-size:.8rem;">
                        @if($log->response_time_ms !== null)
                            <span style="color: {{ $log->response_time_ms < 200 ? '#059669' : ($log->response_time_ms < 1000 ? '#d97706' : '#ef4444') }};">
                                {{ $log->response_time_ms }}ms
                            </span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td class="mono" style="font-size:.73rem;color:#64748b;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $log->client_id ? Str::limit($log->client_id, 18) : '—' }}
                    </td>
                    <td>
                        <i class="bi bi-chevron-down text-muted" id="chevron-{{ $log->id }}" style="font-size:.8rem;transition:transform .2s;"></i>
                    </td>
                </tr>
                {{-- Expandable detail row --}}
                <tr id="detail-{{ $log->id }}" style="display:none;background:#f8fafc;">
                    <td colspan="7" class="p-0">
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div style="font-size:.73rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">
                                        Request Payload
                                    </div>
                                    <div class="code-block" style="font-size:.75rem;max-height:160px;overflow-y:auto;">{{ $log->request_payload ? json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'No payload' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div style="font-size:.73rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">
                                        Response Summary
                                    </div>
                                    <div class="code-block" style="font-size:.75rem;max-height:160px;overflow-y:auto;">{{ $log->response_summary ? json_encode($log->response_summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'No response' }}</div>
                                </div>
                            </div>
                            <div class="mt-2 d-flex gap-3" style="font-size:.75rem;color:#94a3b8;">
                                <span><i class="bi bi-geo-alt me-1"></i>IP: {{ $log->ip_address ?? '—' }}</span>
                                <span><i class="bi bi-clock me-1"></i>{{ $log->created_at->diffForHumans() }}</span>
                                @if($log->client_id)
                                <span><i class="bi bi-key me-1"></i>Client: {{ $log->client_id }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5" style="color:#94a3b8;">
                        <i class="bi bi-terminal d-block mb-2" style="font-size:2rem;"></i>
                        <div style="font-size:.9rem;">No API requests logged yet.</div>
                        <div style="font-size:.8rem;margin-top:.3rem;">Start making API calls with your credentials to see logs here.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div style="font-size:.8rem;color:#64748b;">
            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }}
        </div>
        <div>
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
const openRows = new Set();
function toggleDetail(id) {
    const row     = document.getElementById('detail-' + id);
    const chevron = document.getElementById('chevron-' + id);
    if (!row) return;
    if (openRows.has(id)) {
        row.style.display = 'none';
        chevron.style.transform = '';
        openRows.delete(id);
    } else {
        row.style.display = 'table-row';
        chevron.style.transform = 'rotate(180deg)';
        openRows.add(id);
    }
}
</script>
@endpush
