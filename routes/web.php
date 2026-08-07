<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CloudflareController;
use App\Http\Controllers\ClusterNodeController;
use App\Http\Controllers\DomainAliasController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// Authentication & Session Gateway
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Control Plane Protected Routes (Requires 'auth' Middleware)
Route::middleware('auth')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants', [TenantController::class, 'list'])->name('tenants.list');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::post('/tenants/sync', [TenantController::class, 'sync'])->name('tenants.sync');
    Route::post('/tenants/{tenant}/check-cloudflare', [TenantController::class, 'checkCloudflareStatus'])->name('tenants.check-cloudflare');
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');

    // Server Master Nodes
    Route::get('/servers', [ClusterNodeController::class, 'index'])->name('servers.index');
    Route::post('/servers', [ClusterNodeController::class, 'store'])->name('servers.store');
    Route::put('/servers/{server}', [ClusterNodeController::class, 'update'])->name('servers.update');
    Route::delete('/servers/{server}', [ClusterNodeController::class, 'destroy'])->name('servers.destroy');

    // Domain Alias & Virtual Hosts
    Route::get('/domains', [DomainAliasController::class, 'index'])->name('domains.index');
    Route::post('/domains', [DomainAliasController::class, 'store'])->name('domains.store');
    Route::delete('/domains/{domain}', [DomainAliasController::class, 'destroy'])->name('domains.destroy');

    // Cloudflare DNS Control
    Route::get('/cloudflare', [CloudflareController::class, 'index'])->name('cloudflare.index');
    Route::post('/cloudflare', [CloudflareController::class, 'store'])->name('cloudflare.store');

    // Audit Logs
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::delete('/logs', [LogController::class, 'destroy'])->name('logs.destroy');

    // Queue Daemon & Workers
    Route::get('/queues', [QueueController::class, 'index'])->name('queues.index');
    Route::post('/queues/restart', [QueueController::class, 'restart'])->name('queues.restart');
});
