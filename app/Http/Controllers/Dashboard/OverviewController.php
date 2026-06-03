<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ApiRequestLog;
use Illuminate\Support\Facades\Auth;

class OverviewController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $orgId  = $user->organization_id;
        $org    = $user->organization;

        // Stats
        $totalApiCalls  = ApiRequestLog::where('organization_id', $orgId)->count();
        $totalCollected = Payment::whereHas('invoices', function ($q) use ($orgId) {
            $q->whereHas('customer', function ($q2) use ($orgId) {
                $q2->where('organization_id', $orgId);
            });
        })->sum('amount');

        $totalInvoices  = Invoice::whereHas('customer', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })->count();

        $pendingInvoices = Invoice::whereHas('customer', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })->where('status', 'pending')->count();

        $todaysCollected = Payment::whereHas('invoices', function ($q) use ($orgId) {
            $q->whereHas('customer', function ($q2) use ($orgId) {
                $q2->where('organization_id', $orgId);
            });
        })->whereDate('created_at', now())->sum('amount');

        // ── Tier & Commission logic ─────────────────────────────────────────
        $silverThreshold   = 250_000_000;
        $platinumThreshold = 1_000_000_000;

        if ($todaysCollected >= $platinumThreshold) {
            $commissionRate = 0.015;
        } elseif ($todaysCollected >= $silverThreshold) {
            $commissionRate = 0.01;
        } else {
            $commissionRate = 0;
        }

        $todaysCommission = round($todaysCollected * $commissionRate, 2);
        $accruedReward    = $todaysCommission;
        // Keep existing card key; now represents earned commission value.
        $marketSavings    = $accruedReward;

        // Progress bar spans the full 0 → 1B annual journey
        $floatBarPct = min(round(($todaysCollected / $platinumThreshold) * 100, 2), 100);

        if ($todaysCollected >= $platinumThreshold) {
            $tier          = 'platinum';
            $tierRemaining = 0;
            $tierNextLabel = 'Platinum Partner';
        } elseif ($todaysCollected >= $silverThreshold) {
            $tier          = 'silver';
            $tierRemaining = $platinumThreshold - $todaysCollected;
            $tierNextLabel = 'Platinum Partner (1.5% p.a.)';
        } else {
            $tier          = 'standard';
            $tierRemaining = $silverThreshold - $todaysCollected;
            $tierNextLabel = 'Silver Partner (1.0% p.a.)';
        }
        // ───────────────────────────────────────────────────────────────────

        // API calls this week vs last week for trend
        $apiCallsThisWeek = ApiRequestLog::where('organization_id', $orgId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $apiCallsLastWeek = ApiRequestLog::where('organization_id', $orgId)
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->count();

        $apiCallsTrend = $apiCallsLastWeek > 0
            ? round((($apiCallsThisWeek - $apiCallsLastWeek) / $apiCallsLastWeek) * 100, 1)
            : ($apiCallsThisWeek > 0 ? 100 : 0);

        // Recent invoices (last 10)
        $recentInvoices = Invoice::with('customer')
            ->whereHas('customer', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_api_calls'    => $totalApiCalls,
            'api_calls_trend'    => $apiCallsTrend,
            'total_collected'    => $totalCollected,
            'total_invoices'     => $totalInvoices,
            'pending_invoices'   => $pendingInvoices,
            'todaysCollected'   => $todaysCollected,
            'commission_rate'    => $commissionRate,
            'todays_commission'  => $todaysCommission,
            'accrued_reward'     => $accruedReward,
            'market_savings'     => $marketSavings,
            'tier'               => $tier,
            'tier_remaining'     => $tierRemaining,
            'tier_next_label'    => $tierNextLabel,
            'float_bar_pct'      => $floatBarPct,
        ];

        return view('dashboard.overview', compact('stats', 'recentInvoices', 'org'));
    }
}
