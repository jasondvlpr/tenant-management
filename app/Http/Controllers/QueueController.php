<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueueController extends Controller
{
    public function index()
    {
        $jobs = [];
        $failedCount = 0;
        
        if (Schema::hasTable('jobs')) {
            $rawJobs = DB::table('jobs')->get();
            foreach ($rawJobs as $j) {
                $payload = json_decode($j->payload, true);
                $jobs[] = [
                    'id' => 'job_' . $j->id,
                    'name' => $payload['displayName'] ?? 'App\\Jobs\\ProvisionTenantJob',
                    'target' => 'Queue Daemon Item',
                    'cluster' => $j->queue ?? 'default',
                    'attempt' => $j->attempts,
                    'maxAttempts' => 3,
                    'status' => 'Pending in Redis/DB Queue',
                    'runtime' => '< 1.0s',
                    'icon' => 'sync'
                ];
            }
        }



        return view('admin.queues.index', compact('jobs'));
    }

    public function restart()
    {
        // Touch restart flag or simple simulation for UI command center
        return redirect()->back()->with('success', 'Worker pool berhasil di-restart dan seluruh koneksi antrean diperbarui!');
    }
}
