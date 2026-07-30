<?php

namespace App\Jobs;

use App\Models\DomainAlias;
use App\Services\CloudflareDnsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCloudflareDnsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public DomainAlias $alias;
    public string $targetIp;
    public string $tenantName;

    /**
     * Create a new job instance.
     */
    public function __construct(DomainAlias $alias, string $targetIp, string $tenantName)
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
        $target = $recordType === 'CNAME' ? $this->alias->tenant->domain : $this->targetIp;
        $proxied = str_contains($this->alias->cf_status, 'Orange');

        $result = $cfService->syncRecord(
            $this->alias->alias,
            $recordType,
            $target,
            $proxied,
            $this->tenantName . ' (Alias DNS)'
        );

        if (!empty($result['success']) && $result['success']) {
            $this->alias->update(['cf_status' => 'Proxied (Orange Cloud)', 'ssl' => 'Active (TLS 1.3)']);
        }
    }
}
