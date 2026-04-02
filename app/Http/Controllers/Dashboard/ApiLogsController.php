<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiLogsController extends Controller
{
    public function index(Request $request)
    {
        $orgId = Auth::user()->organization_id;

        $query = ApiRequestLog::where('organization_id', $orgId)
            ->orderByDesc('created_at');

        // Search by endpoint
        if ($search = $request->get('search')) {
            $query->where('endpoint', 'like', '%' . $search . '%');
        }

        // Filter by result type
        $filter = $request->get('filter', 'all');
        if ($filter === 'success') {
            $query->where('success', true);
        } elseif ($filter === 'errors') {
            $query->where('success', false);
        }

        // Date range filter
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(25)->withQueryString();

        $totalRequests = ApiRequestLog::where('organization_id', $orgId)->count();
        $successCount  = ApiRequestLog::where('organization_id', $orgId)->where('success', true)->count();
        $errorCount    = ApiRequestLog::where('organization_id', $orgId)->where('success', false)->count();
        $avgLatency    = ApiRequestLog::where('organization_id', $orgId)->avg('response_time_ms');

        return view('dashboard.api-logs', compact(
            'logs', 'totalRequests', 'successCount', 'errorCount', 'avgLatency', 'filter', 'search'
        ));
    }
}
