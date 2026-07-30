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
        Schema::create('cluster_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->string('ip_address')->unique();
            $table->string('endpoint_url');
            $table->string('api_secret')->nullable();
            $table->string('status')->default('Online');
            $table->string('latency')->default('0ms');
            $table->string('cpu')->default('0%');
            $table->string('ram')->default('0 GB / 0 GB');
            $table->string('storage')->default('0 GB / 0 GB');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cluster_nodes');
    }
};
