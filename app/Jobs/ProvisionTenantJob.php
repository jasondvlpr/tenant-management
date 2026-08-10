<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\CloudflareDnsService;
use App\Services\RemoteProvisioningService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Tenant $tenant;

    /**
     * Create a new job instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     */
    public function handle(RemoteProvisioningService $provisioningService, CloudflareDnsService $cfService): void
    {
        // 1. Send instruction to Remote Server Node
        $success = $provisioningService->deployTenant($this->tenant);

        // 2. Register A/CNAME record to Cloudflare pointing to cluster endpoint/IP
        $domains = $this->tenant->domains ?? [];
        if ($success && !empty($domains) && $this->tenant->clusterNode) {
            $target = $this->tenant->clusterNode->ip_address;
            $type = filter_var($target, FILTER_VALIDATE_IP) ? 'A' : 'CNAME';

            $domainName = $domains[0]['domain'];
            $domains[0]['type'] = $type;

            $cfResult = $cfService->syncRecord(
                $domainName,
                $type,
                $target,
                true,
                $this->tenant->name . ' (Primary DNS)'
            );

            if (!empty($cfResult['success']) && $cfResult['success']) {
                $domains[0]['cf_status'] = 'Proxied (Orange Cloud)';
                $domains[0]['cf_zone_id'] = $cfResult['zone_id'] ?? null;
                $domains[0]['cf_zone_status'] = $cfResult['zone_status'] ?? 'pending';
                $domains[0]['cf_nameservers'] = $cfResult['name_servers'] ?? [];
                
                $this->tenant->update(['domains' => $domains]);
            } else {
                $domains[0]['cf_status'] = 'Sync Error (Check API Logs)';
                $domains[0]['cf_zone_status'] = 'error';
                $this->tenant->update(['domains' => $domains]);
            }
        }
    }
}
