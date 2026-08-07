<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClusterNode;
use App\Services\RemoteProvisioningService;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('tenants:sync-remote')]
#[Description('Sync tenants from all remote nodes')]
class SyncRemoteTenants extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(RemoteProvisioningService $service)
    {
        // Get all cluster nodes (assuming all should be synced)
        $nodes = ClusterNode::all();
        
        $this->info("Found {$nodes->count()} cluster node(s). Starting sync...");

        foreach ($nodes as $node) {
            $this->info("Syncing node: {$node->name} ({$node->ip_address})");
            try {
                // The service syncs multiple pages automatically or just one page? Let's assume it syncs what's needed.
                $result = $service->syncTenantsFromNode($node, 1000, 1);
                
                if ($result['success'] ?? false) {
                    $this->info("Successfully synced node {$node->name}.");
                } else {
                    $this->error("Failed to sync node {$node->name}: " . ($result['error'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $this->error("Exception syncing node {$node->name}: " . $e->getMessage());
                Log::error("Exception syncing node {$node->name}: " . $e->getMessage());
            }
        }
        
        $this->info("Sync complete.");
    }
}
