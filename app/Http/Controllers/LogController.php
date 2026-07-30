<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiLog::latest();
        
        if ($request->filled('cluster') && $request->input('cluster') !== 'all') {
            $query->where('cluster_name', 'like', '%' . $request->input('cluster') . '%');
        }

        $logs = $query->take(100)->get();
        
        // Stats calculations
        $totalRequests = ApiLog::count();
        $successCount = ApiLog::where('status_code', '>=', 200)->where('status_code', '<', 300)->count();
        $successRate = $totalRequests > 0 ? round(($successCount / $totalRequests) * 100, 1) . '%' : '100%';

        return view('admin.logs.index', compact('logs', 'totalRequests', 'successRate'));
    }

    public function destroy(Request $request)
    {
        ApiLog::truncate();
        return redirect()->back()->with('success', 'Seluruh riwayat log telemetri telah dibersihkan.');
    }
}
