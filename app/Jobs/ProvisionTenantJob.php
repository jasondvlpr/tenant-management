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

        // 2. If Auto DNS is set, register A/CNAME record to Cloudflare pointing to cluster endpoint/IP
        if ($success && $this->tenant->auto_dns && $this->tenant->clusterNode) {
            $target = $this->tenant->clusterNode->ip_address;
            $type = filter_var($target, FILTER_VALIDATE_IP) ? 'A' : 'CNAME';

            $cfResult = $cfService->syncRecord(
                $this->tenant->domain,
                $type,
                $target,
                true,
                $this->tenant->name . ' (Primary DNS)'
            );

            if (!empty($cfResult['success']) && $cfResult['success']) {
                $this->tenant->update([
                    'cf_status' => 'Proxied (Orange Cloud)',
                    'cf_zone_id' => $cfResult['zone_id'] ?? null,
                    'cf_zone_status' => $cfResult['zone_status'] ?? 'pending',
                    'cf_nameservers' => $cfResult['name_servers'] ?? [],
                ]);

            } else {
                $this->tenant->update(['cf_status' => 'Sync Error (Check API Logs)', 'cf_zone_status' => 'error']);
            }
        }
    }
}
