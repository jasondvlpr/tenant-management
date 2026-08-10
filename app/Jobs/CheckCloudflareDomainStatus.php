<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\CloudflareDnsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckCloudflareDomainStatus implements ShouldQueue
{
    use Queueable;

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
    public function handle(CloudflareDnsService $cfService): void
    {
        $domains = $this->tenant->domains ?? [];
        if (empty($domains)) {
            return;
        }
        
        $mainDomainIndex = 0;
        $domainName = $domains[$mainDomainIndex]['domain'] ?? null;
        if (empty($domainName)) {
            return;
        }
        
        // 1. Periksa status zone domain di Cloudflare
        $result = $cfService->checkZoneStatus(null, $domainName);

        if ($result['success'] ?? false) {
            $status = $result['status'] ?? 'pending';
            
            // Konversi status CF ke format tampilan
            $displayStatus = match (strtolower($status)) {
                'active' => 'Proxied (Active)',
                'pending' => 'Pending Nameservers',
                'moved' => 'Moved',
                'deleted' => 'Deleted',
                'deactivated' => 'Deactivated',
                default => 'Unknown (' . $status . ')',
            };

            $domains[$mainDomainIndex]['cf_status'] = $displayStatus;
            $domains[$mainDomainIndex]['cf_zone_id'] = $result['id'] ?? null;
            $domains[$mainDomainIndex]['cf_zone_status'] = strtolower($status);
            $domains[$mainDomainIndex]['cf_nameservers'] = $result['name_servers'] ?? null;

            $this->tenant->update(['domains' => $domains]);
        } else {
            Log::warning("Gagal cek status CF untuk tenant {$domainName}: " . ($result['error'] ?? 'Unknown Error'));
        }
    }
}
