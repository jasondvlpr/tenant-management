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
        // 1. Periksa status zone domain di Cloudflare
        $result = $cfService->checkZoneStatus(null, $this->tenant->domain);

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

            $this->tenant->update([
                'cf_status' => $displayStatus,
                'cf_zone_id' => $result['id'] ?? null,
                'cf_zone_status' => strtolower($status),
                'cf_nameservers' => isset($result['name_servers']) ? json_encode($result['name_servers']) : null
            ]);
        } else {
            Log::warning("Gagal cek status CF untuk tenant {$this->tenant->domain}: " . ($result['error'] ?? 'Unknown Error'));
        }
    }
}
