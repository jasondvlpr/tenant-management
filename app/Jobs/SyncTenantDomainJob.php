<?php

namespace App\Jobs;

use App\Services\RemoteProvisioningService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTenantDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public object $alias;
    public string $action;

    /**
     * Create a new job instance.
     * action can be 'add' or 'remove'
     */
    public function __construct(object $alias, string $action = 'add')
    {
        $this->alias = $alias;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(RemoteProvisioningService $provisioningService): void
    {
        if ($this->action === 'add') {
            $provisioningService->addDomainAlias($this->alias);
        } elseif ($this->action === 'remove') {
            $provisioningService->removeDomainAlias($this->alias);
        }
    }
}
