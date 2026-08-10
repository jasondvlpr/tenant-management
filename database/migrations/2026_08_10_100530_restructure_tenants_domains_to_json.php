<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add new domains_json column to tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->json('domains_json')->nullable();
        });

        // 2. Migrate existing data
        $tenants = DB::table('tenants')->get();
        foreach ($tenants as $tenant) {
            $domains = [];
            
            // Extract old nameservers if any
            $ns = [];
            if (!empty($tenant->cf_nameservers)) {
                $nsRaw = json_decode($tenant->cf_nameservers, true);
                $ns = is_array($nsRaw) ? $nsRaw : [];
            }

            // Primary domain entry
            $domains[] = [
                'id' => uniqid(),
                'domain' => $tenant->domain,
                'subdomains' => [],
                'type' => 'A', // Assuming primary is A or we don't care exactly since it will be checked later
                'cf_status' => $tenant->cf_status ?? 'Proxied (Orange Cloud)',
                'cf_zone_id' => $tenant->cf_zone_id,
                'cf_zone_status' => $tenant->cf_zone_status ?? 'pending',
                'cf_nameservers' => $ns,
            ];

            // Domain aliases entries
            $aliases = DB::table('domain_aliases')->where('tenant_id', $tenant->id)->get();
            foreach ($aliases as $alias) {
                $domains[] = [
                    'id' => uniqid(),
                    'domain' => $alias->alias,
                    'subdomains' => [],
                    'type' => $alias->type ?? 'CNAME',
                    'cf_status' => $alias->cf_status ?? 'DNS Only (Grey Cloud)',
                    'cf_zone_id' => null,
                    'cf_zone_status' => 'pending',
                    'cf_nameservers' => [],
                ];
            }

            // Update tenants table
            DB::table('tenants')->where('id', $tenant->id)->update([
                'domains_json' => json_encode($domains)
            ]);
        }

        // 3. Drop old columns from tenants table
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropUnique('tenants_domain_unique');
            $table->dropColumn([
                'domain', 
                'cf_status', 
                'cf_zone_id', 
                'cf_zone_status', 
                'cf_nameservers',
                'auto_dns'
            ]);
        });

        // 4. Rename new json column to 'domains'
        Schema::table('tenants', function (Blueprint $table) {
            $table->renameColumn('domains_json', 'domains');
        });

        // 5. Drop domain_aliases table
        Schema::dropIfExists('domain_aliases');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive migration, but we can try to restore the basic schema.
        
        // Restore domain_aliases table
        Schema::create('domain_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('alias')->unique();
            $table->string('type')->default('CNAME');
            $table->string('cf_status')->default('DNS Only (Grey Cloud)');
            $table->string('ssl')->default('Active (TLS 1.3)');
            $table->timestamps();
        });

        // Restore columns to tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('domain')->nullable();
            $table->string('cf_status')->default('Proxied (Orange Cloud)');
            $table->string('cf_zone_id')->nullable();
            $table->string('cf_zone_status')->default('pending');
            $table->json('cf_nameservers')->nullable();
            $table->boolean('auto_dns')->default(true);
        });

        // Best effort to restore data
        $tenants = DB::table('tenants')->get();
        foreach ($tenants as $tenant) {
            $domains = json_decode($tenant->domains, true) ?? [];
            if (!empty($domains)) {
                $primary = array_shift($domains);
                DB::table('tenants')->where('id', $tenant->id)->update([
                    'domain' => $primary['domain'] ?? null,
                    'cf_status' => $primary['cf_status'] ?? 'Proxied (Orange Cloud)',
                    'cf_zone_id' => $primary['cf_zone_id'] ?? null,
                    'cf_zone_status' => $primary['cf_zone_status'] ?? 'pending',
                    'cf_nameservers' => json_encode($primary['cf_nameservers'] ?? []),
                ]);

                foreach ($domains as $alias) {
                    DB::table('domain_aliases')->insert([
                        'tenant_id' => $tenant->id,
                        'alias' => $alias['domain'],
                        'type' => $alias['type'] ?? 'CNAME',
                        'cf_status' => $alias['cf_status'] ?? 'DNS Only (Grey Cloud)',
                        'ssl' => 'Active (TLS 1.3)'
                    ]);
                }
            }
        }

        // Drop JSON column
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('domains');
            $table->unique('domain', 'tenants_domain_unique');
        });
    }
};
