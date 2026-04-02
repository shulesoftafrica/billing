<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
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
        $totalCollected = Payment::whereHas('invoice', function ($q) use ($orgId) {
            $q->whereHas('customer', function ($q2) use ($orgId) {
                $q2->where('organization_id', $orgId);
            });
        })->where('status', 'paid')->sum('amount');

        $totalInvoices  = Invoice::whereHas('customer', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })->count();

        $pendingInvoices = Invoice::whereHas('customer', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })->where('status', 'pending')->count();

        $todaysCollected = Payment::whereHas('invoice', function ($q) use ($orgId) {
            $q->whereHas('customer', function ($q2) use ($orgId) {
                $q2->where('organization_id', $orgId);
            });
        })->where('status', 'paid')->whereDate('created_at', today())->sum('amount');

        $todaysCommission = round($todaysCollected * 0.01, 2);

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
            'todays_commission'  => $todaysCommission,
        ];

        return view('dashboard.overview', compact('stats', 'recentInvoices', 'org'));
    }
}
