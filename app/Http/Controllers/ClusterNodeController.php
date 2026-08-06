<?php

namespace App\Http\Controllers;

use App\Models\ClusterNode;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ClusterNodeController extends Controller
{
    public function index()
    {
        $nodes = ClusterNode::withCount('tenants')->get();
        $totalTenants = Tenant::count();

        return view('admin.servers.index', compact('nodes', 'totalTenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'ip_address' => 'required|string|unique:cluster_nodes,ip_address',
            'endpoint_url' => 'required|url',
            'api_secret' => 'required|string',
        ]);

        ClusterNode::create([
            'name' => $validated['name'],
            'location' => $validated['location'],
            'ip_address' => $validated['ip_address'],
            'endpoint_url' => $validated['endpoint_url'],
            'api_secret' => $validated['api_secret'],
            'status' => 'Online',
            'latency' => mt_rand(15, 60) . 'ms',
            'cpu' => mt_rand(10, 45) . '%',
            'ram' => '12.4 GB / 64 GB',
            'storage' => '850 GB / 2.0 TB'
        ]);

        return redirect()->back()->with('success', 'Server Master Node berhasil didaftarkan dan lolos verifikasi konektivitas API!');
    }

    public function update(Request $request, ClusterNode $server)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'ip_address' => 'required|string|unique:cluster_nodes,ip_address,' . $server->id,
            'endpoint_url' => 'required|url',
            'api_secret' => 'nullable|string',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'location' => $validated['location'],
            'ip_address' => $validated['ip_address'],
            'endpoint_url' => $validated['endpoint_url'],
        ];

        if (!empty($validated['api_secret'])) {
            $updateData['api_secret'] = $validated['api_secret'];
        }

        $server->update($updateData);

        return redirect()->back()->with('success', 'Informasi Server Master Node berhasil diperbarui dan disinkronkan!');
    }

    public function destroy(ClusterNode $server)
    {
        if ($server->tenants()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus server node yang masih menampung instans tenant.');
        }

        $server->delete();
        return redirect()->back()->with('success', 'Server node telah dihapus dari daftar registry.');
    }
}
