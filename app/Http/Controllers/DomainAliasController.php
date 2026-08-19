<?php

namespace App\Http\Controllers;

use App\Jobs\SyncCloudflareDnsJob;
use App\Jobs\SyncTenantDomainJob;
use App\Models\DomainAlias;
use App\Models\Tenant;
use App\Services\CloudflareDnsService;
use Illuminate\Http\Request;

class DomainAliasController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'alias' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                $exists = Tenant::whereJsonContains('domains', ['domain' => $value])->exists();
                if ($exists) {
                    $fail('Domain ini sudah terdaftar di sistem.');
                }
            }],
            'type' => 'nullable|string',
            'cf_proxy' => 'nullable|string',
            'subdomains' => 'nullable|string'
        ]);

        $tenant = Tenant::with('clusterNode')->findOrFail($validated['tenant_id']);
        
        $subdomainsStr = $request->input('subdomains', '');
        $subdomains = array_values(array_filter(array_map('trim', explode(',', $subdomainsStr))));

        $newDomain = [
            'id' => uniqid(),
            'domain' => $validated['alias'],
            'subdomains' => $subdomains,
            'type' => $request->input('type', 'A'),
            'cf_status' => str_contains($request->input('cf_proxy', 'Proxied'), 'Proxied') ? 'Proxied (Orange Cloud)' : 'DNS Only (Grey Cloud)',
            'cf_zone_id' => null,
            'cf_zone_status' => 'pending',
            'cf_nameservers' => [],
        ];

        $domains = $tenant->domains ?? [];
        $domains[] = $newDomain;
        $tenant->update(['domains' => $domains]);

        $targetIp = $tenant->clusterNode ? $tenant->clusterNode->ip_address : '127.0.0.1';
        
        // Pass the domain array as an object for compatibility with Jobs
        $domainObj = (object)$newDomain;
        $domainObj->tenant = $tenant;
        
        SyncCloudflareDnsJob::dispatch($domainObj, $targetIp, $tenant->name);
        SyncTenantDomainJob::dispatch($domainObj, 'add');

        return redirect()->back()->with('success', 'Domain Alias ' . $newDomain['domain'] . ' berhasil didaftarkan dan di-sinkronkan dengan Cloudflare!');
    }

    public function destroy($domainId, CloudflareDnsService $cfService, \App\Services\RemoteProvisioningService $provisionService)
    {
        // Find tenant that has this domain
        $tenants = Tenant::all();
        $targetTenant = null;
        $targetDomain = null;
        $domainIndex = -1;

        foreach ($tenants as $t) {
            $domains = $t->domains ?? [];
            foreach ($domains as $index => $dom) {
                if ($dom['id'] == $domainId) {
                    $targetTenant = $t;
                    $targetDomain = $dom;
                    $domainIndex = $index;
                    break 2;
                }
            }
        }

        if (!$targetTenant || !$targetDomain) {
            return redirect()->back()->with('error', 'Domain tidak ditemukan.');
        }

        $cfService->deleteRecord($targetDomain['domain'], $targetTenant->name ?? 'Unknown');
        
        $domainObj = (object)$targetDomain;
        $domainObj->tenant = $targetTenant;
        
        $provisionService->removeDomainAlias($domainObj);

        $domains = $targetTenant->domains ?? [];
        unset($domains[$domainIndex]);
        $targetTenant->update(['domains' => array_values($domains)]);

        return redirect()->back()->with('success', 'Domain Alias telah dihapus dari zona Cloudflare dan API Tenant.');
    }

    public function addSubdomain(Request $request, Tenant $tenant, $domainId)
    {
        $validated = $request->validate([
            'subdomain' => 'required|string|max:255'
        ]);

        $subdomain = trim($validated['subdomain']);
        $domains = $tenant->domains ?? [];
        $domainIndex = -1;

        foreach ($domains as $index => $dom) {
            if ($dom['id'] == $domainId) {
                $domainIndex = $index;
                break;
            }
        }

        if ($domainIndex === -1) {
            return redirect()->back()->with('error', 'Domain tidak ditemukan.');
        }

        $currentSubdomains = $domains[$domainIndex]['subdomains'] ?? [];
        if (!in_array($subdomain, $currentSubdomains)) {
            $currentSubdomains[] = $subdomain;
            $domains[$domainIndex]['subdomains'] = $currentSubdomains;
            $tenant->update(['domains' => $domains]);
            
            // Dispatch job to add subdomain as an alias in the node/aaPanel
            $fullSubdomain = $subdomain . '.' . $domains[$domainIndex]['domain'];
            $subdomainObj = (object)[
                'id' => uniqid(),
                'domain' => $fullSubdomain,
                'type' => $domains[$domainIndex]['type'] ?? 'CNAME',
                'cf_status' => 'Proxied (Orange Cloud)',
                'tenant' => $tenant
            ];
            
            $targetIp = $tenant->clusterNode ? $tenant->clusterNode->ip_address : '127.0.0.1';
            
            // Sync to aaPanel
            \App\Jobs\SyncTenantDomainJob::dispatch($subdomainObj, 'add');
            
            // Sync to Cloudflare
            \App\Jobs\SyncCloudflareDnsJob::dispatch($subdomainObj, $targetIp, $tenant->name);

            return redirect()->back()->with('success', "Subdomain $subdomain berhasil ditambahkan dan antrian sinkronisasi ke server (serta DNS) telah dijalankan.");
        }

        return redirect()->back()->with('error', 'Subdomain sudah ada.');
    }

    public function destroySubdomain(Request $request, Tenant $tenant, $domainId, $subdomain, CloudflareDnsService $cfService, \App\Services\RemoteProvisioningService $provisionService)
    {
        $domains = $tenant->domains ?? [];
        $domainIndex = -1;

        foreach ($domains as $index => $dom) {
            if ($dom['id'] == $domainId) {
                $domainIndex = $index;
                break;
            }
        }

        if ($domainIndex === -1) {
            return redirect()->back()->with('error', 'Domain tidak ditemukan.');
        }

        $currentSubdomains = $domains[$domainIndex]['subdomains'] ?? [];
        $subIndex = array_search($subdomain, $currentSubdomains);

        if ($subIndex === false) {
            return redirect()->back()->with('error', 'Subdomain tidak ditemukan.');
        }

        // Remove from array
        unset($currentSubdomains[$subIndex]);
        $domains[$domainIndex]['subdomains'] = array_values($currentSubdomains);
        $tenant->update(['domains' => $domains]);

        // Full domain name
        $fullSubdomain = $subdomain . '.' . $domains[$domainIndex]['domain'];

        // Remove from Cloudflare
        $cfService->deleteRecord($fullSubdomain, $tenant->name);

        // Remove from aaPanel
        $subdomainObj = (object)[
            'domain' => $fullSubdomain,
            'tenant' => $tenant
        ];
        $provisionService->removeDomainAlias($subdomainObj);

        return redirect()->back()->with('success', "Subdomain $subdomain berhasil dihapus dari server dan DNS.");
    }

    public function createWwwRedirect(Request $request, Tenant $tenant, $domainId, \App\Services\CloudflareDnsService $cfService)
    {
        $domains = $tenant->domains ?? [];
        $domainIndex = -1;
        foreach ($domains as $index => $dom) {
            if ($dom['id'] == $domainId) {
                $domainIndex = $index;
                break;
            }
        }

        if ($domainIndex === -1) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'error' => 'Domain tidak ditemukan'], 404);
            return redirect()->back()->with('error', 'Domain tidak ditemukan.');
        }

        $domainData = $domains[$domainIndex];
        $zoneId = $domainData['cf_zone_id'] ?? null;
        $domainName = $domainData['domain'];

        if (empty($zoneId)) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'error' => 'Zone ID belum ada. Pastikan domain sudah sinkron dengan Cloudflare.'], 400);
            return redirect()->back()->with('error', 'Zone ID belum ada. Pastikan domain sudah sinkron dengan Cloudflare.');
        }

        $result = $cfService->createWwwRedirect($zoneId, $domainName, $tenant->name);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['error']);
    }
}
