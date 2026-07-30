<?php

namespace App\Http\Controllers;

use App\Jobs\SyncCloudflareDnsJob;
use App\Models\DomainAlias;
use App\Models\Tenant;
use App\Services\CloudflareDnsService;
use Illuminate\Http\Request;

class DomainAliasController extends Controller
{
    public function index()
    {
        $domains = DomainAlias::with('tenant.clusterNode')->latest()->get();
        $tenants = Tenant::with('clusterNode')->get();

        return view('admin.domains.index', compact('domains', 'tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'alias' => 'required|string|max:255|unique:domain_aliases,alias',
            'type' => 'nullable|string',
            'cf_proxy' => 'nullable|string'
        ]);

        $tenant = Tenant::with('clusterNode')->findOrFail($validated['tenant_id']);
        
        $alias = DomainAlias::create([
            'tenant_id' => $tenant->id,
            'alias' => $validated['alias'],
            'type' => $request->input('type', 'CNAME'),
            'cf_status' => str_contains($request->input('cf_proxy', 'Proxied'), 'Proxied') ? 'Proxied (Orange Cloud)' : 'DNS Only (Grey Cloud)',
            'ssl' => 'Active (TLS 1.3)'
        ]);

        $targetIp = $tenant->clusterNode ? $tenant->clusterNode->ip_address : '127.0.0.1';
        SyncCloudflareDnsJob::dispatch($alias, $targetIp, $tenant->name);

        return redirect()->back()->with('success', 'Domain Alias ' . $alias->alias . ' berhasil didaftarkan dan di-sinkronkan dengan Cloudflare!');
    }

    public function destroy(DomainAlias $domain, CloudflareDnsService $cfService)
    {
        $cfService->deleteRecord($domain->alias, $domain->tenant->name ?? 'Unknown');
        $domain->delete();

        return redirect()->back()->with('success', 'Domain Alias telah dihapus dari zona Cloudflare.');
    }
}
