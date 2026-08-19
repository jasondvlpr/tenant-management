<?php

namespace App\Jobs;

use App\Services\CloudflareDnsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCloudflareDnsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public object $alias;
    public string $targetIp;
    public string $tenantName;

    /**
     * Create a new job instance.
     */
    public function __construct(object $alias, string $targetIp, string $tenantName)
    {
        $this->alias = $alias;
        $this->targetIp = $targetIp;
        $this->tenantName = $tenantName;
    }

    /**
     * Execute the job.
     */
    public function handle(CloudflareDnsService $cfService): void
    {
        $recordType = $this->alias->type === 'CNAME' ? 'CNAME' : 'A';
        $target = $recordType === 'CNAME' ? $this->alias->tenant->domains[0]['domain'] : $this->targetIp;
        $proxied = str_contains($this->alias->cf_status, 'Orange');

        $result = $cfService->syncRecord(
            $this->alias->domain,
            $recordType,
            $target,
            $proxied,
            $this->tenantName . ' (Alias DNS)'
        );

        if (!empty($result['success']) && $result['success']) {
            $tenant = $this->alias->tenant;
            $domains = $tenant->domains ?? [];
            foreach ($domains as &$dom) {
                if ($dom['id'] === $this->alias->id) {
                    $finalProxied = isset($result['actual_proxied']) ? $result['actual_proxied'] : $proxied;
                    $dom['cf_status'] = $finalProxied ? 'Proxied (Orange Cloud)' : 'DNS Only (Grey Cloud)';
                    
                    if (isset($result['actual_type'])) {
                        $dom['type'] = strtoupper($result['actual_type']);
                    }
                    
                    if (isset($result['zone_id'])) $dom['cf_zone_id'] = $result['zone_id'];
                    if (isset($result['zone_status'])) $dom['cf_zone_status'] = $result['zone_status'];
                    if (isset($result['name_servers'])) $dom['cf_nameservers'] = $result['name_servers'];
                }
            }
            $tenant->update(['domains' => $domains]);
        }
    }
}
