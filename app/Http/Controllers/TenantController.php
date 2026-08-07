<?php

namespace App\Http\Controllers;

use App\Jobs\ProvisionTenantJob;
use App\Models\ClusterNode;
use App\Models\DomainAlias;
use App\Models\Tenant;
use App\Services\RemoteProvisioningService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('clusterNode', 'aliases')->latest()->get();
        $nodes = ClusterNode::all();

        return view('admin.tenants.index', compact('tenants', 'nodes'));
    }

    public function list()
    {
        $tenants = Tenant::with('clusterNode', 'aliases')->latest()->get();
        $nodes = ClusterNode::all();

        return view('admin.tenants.list', compact('tenants', 'nodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cluster_node_id' => 'required|exists:cluster_nodes,id',
            'name' => 'required|string|max:255',
            'remote_tenant_id' => 'required|string|regex:/^[a-zA-Z0-9\-\_]+$/|max:50|unique:tenants,remote_tenant_id',
            'domain' => 'required|string|max:255|unique:tenants,domain',
            'auto_dns' => 'nullable|boolean',
        ], [
            'remote_tenant_id.regex' => 'ID Tenant tidak boleh mengandung spasi atau karakter khusus.',
            'remote_tenant_id.unique' => 'ID Tenant ini sudah terdaftar di pangkalan data.',
        ]);

        $colors = ['indigo', 'emerald', 'purple', 'amber', 'blue', 'rose'];
        $cleanId = strtoupper($validated['remote_tenant_id']);

        $tenant = Tenant::create([
            'cluster_node_id' => $validated['cluster_node_id'],
            'remote_tenant_id' => $cleanId,
            'database_name' => 'giga_' . $cleanId,
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'auto_dns' => $request->has('auto_dns') || $request->input('auto_dns') == 1,
            'status' => 'Deploying',
            'cf_status' => 'Syncing Zone...',
            'avatar' => strtoupper(substr($cleanId, 0, 1)),
            'color' => $colors[array_rand($colors)],
        ]);

        // Execute background deployment immediately to create DB & VHost on aaPanel
        ProvisionTenantJob::dispatchSync($tenant);

        $fresh = $tenant->fresh();
        if ($fresh && $fresh->status === 'Active') {
            return redirect()->back()->with('success', "Tenant {$tenant->name} ({$cleanId}) berhasil dibangun via API! Virtual Host & Database di aaPanel kini telah siap.");
        }

        return redirect()->back()->with('success', "Tenant {$tenant->name} tercermin ke sistem. Periksa menu Log Request API untuk detail hasil pengiriman ke aaPanel.");
    }

    public function sync(Request $request, RemoteProvisioningService $service)
    {
        $nodes = ClusterNode::all();
        $totalSynced = 0;
        $errors = [];

        foreach ($nodes as $node) {
            $result = $service->syncTenantsFromNode($node, 50);
            if ($result['success']) {
                $totalSynced += $result['count'];
            } else {
                $errors[] = "Node {$node->name}: {$result['error']}";
            }
        }

        if ($totalSynced > 0) {
            return redirect()->back()->with('success', "Sinkronisasi dari Master Tenant berhasil! {$totalSynced} tenant diperbarui beserta status dan database.");
        }

        if (!empty($errors)) {
            return redirect()->back()->with('error', "Gagal menyinkronkan beberapa node: " . implode('; ', $errors));
        }

        return redirect()->back()->with('success', "Sinkronisasi selesai diproses dari seluruh klaster.");
    }

    public function destroy(Tenant $tenant, RemoteProvisioningService $service, \App\Services\CloudflareDnsService $cfService)
    {
        $remoteId = $tenant->remote_tenant_id ?: $tenant->id;
        $name = $tenant->name;
        $domain = $tenant->domain;

        $service->removeTenant($tenant);
        if ($tenant->auto_dns && $domain) {
            $cfService->deleteRecord($domain, $name);
        }
        $tenant->delete();

        return redirect()->back()->with('success', "Tenant {$name} ({$remoteId}) serta entri domain dan zona DNS di Cloudflare telah dibesut dan dicopot secara permanen!");
    }

    public function checkCloudflareStatus(Tenant $tenant, \App\Services\CloudflareDnsService $cfService)
    {
        $res = $cfService->checkZoneStatus($tenant->cf_zone_id, $tenant->domain);

        if (!empty($res['success']) && $res['success']) {
            $tenant->update([
                'cf_zone_id' => $res['id'] ?? $tenant->cf_zone_id,
                'cf_zone_status' => $res['status'] ?? 'pending',
                'cf_nameservers' => $res['name_servers'] ?? $tenant->cf_nameservers,
            ]);

            if ($res['status'] === 'active') {
                return redirect()->back()->with('success', "Selamat! Domain {$tenant->domain} resmi TERHUBUNG (Active) di Cloudflare. Perlindungan WAF & Reverse Proxy Orange Cloud bekerja sempurna!");
            } else {
                return redirect()->back()->with('success', "Status domain {$tenant->domain} di Cloudflare saat ini masih: " . strtoupper($res['status']) . ". Pastikan Anda telah memasang Name Server Cloudflare pada panel registrar domain Anda.");
            }
        }

        return redirect()->back()->with('error', $res['error'] ?? "Gagal memeriksa status koneksi Cloudflare untuk domain {$tenant->domain}.");
    }

    public function updateConfig(Request $request, Tenant $tenant, RemoteProvisioningService $service)
    {
        $validated = $request->validate([
            'settings' => 'nullable|array',
            'api_configs' => 'nullable|array',
        ]);

        $result = $service->updateTenantConfig($tenant, $validated);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Konfigurasi berhasil disimpan di Master Node!']);
        }

        return response()->json(['success' => false, 'error' => $result['error'] ?? 'Gagal menyimpan konfigurasi'], 500);
    }
}
