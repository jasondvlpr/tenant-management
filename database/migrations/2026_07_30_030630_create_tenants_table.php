<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cluster_node_id')->constrained('cluster_nodes')->cascadeOnDelete();
            $table->string('remote_tenant_id')->nullable();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('database_name')->nullable();
            $table->string('status')->default('Active');
            $table->string('cf_status')->default('Proxied (Orange Cloud)');
            $table->string('cf_zone_id')->nullable();
            $table->string('cf_zone_status')->default('pending');
            $table->json('cf_nameservers')->nullable();
            $table->boolean('auto_dns')->default(true);
            $table->string('avatar')->default('T');
            $table->string('color')->default('indigo');
            $table->string('cpu')->default('10%');
            $table->string('storage')->default('50 GB / 250 GB');
            $table->integer('users')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
