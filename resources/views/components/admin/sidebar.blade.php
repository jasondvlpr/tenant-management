@php
    $totalNodes = \App\Models\ClusterNode::count();
    $totalTenants = \App\Models\Tenant::count();
    $totalDomains = \App\Models\DomainAlias::count();
    $totalJobs = \Illuminate\Support\Facades\Schema::hasTable('jobs') ? \Illuminate\Support\Facades\DB::table('jobs')->count() : 0;
@endphp

<!-- Mobile Sidebar Overlay -->
<div 
    x-show="sidebarOpen" 
    @click="sidebarOpen = false"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs lg:hidden"
    style="display: none;"
></div>

<!-- Sidebar Container -->
<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed left-0 top-0 z-50 flex h-screen w-72 flex-col justify-between overflow-y-auto bg-slate-900 text-slate-300 border-r border-slate-800 shadow-2xl transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 dark:bg-slate-950 dark:border-slate-800/80 shrink-0"
>
    <div>
        <!-- Logo & Header Sidebar -->
        <div class="flex items-center justify-between gap-2 px-6 py-5 border-b border-slate-800/80">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-500 text-white shadow-lg shadow-indigo-500/30 transition-transform group-hover:scale-105 duration-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="truncate">
                    <span class="text-lg font-bold tracking-tight text-white flex items-center gap-1">
                        Tenant<span class="text-indigo-400">Master</span>
                    </span>
                    <span class="block text-[11px] font-semibold tracking-wide uppercase text-slate-400">Control Plane OS</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="block lg:hidden text-slate-400 hover:text-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="mt-4 px-3 py-3 space-y-6 text-sm">
            <!-- Section 1: Control Plane -->
            <div>
                <h3 class="mb-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Control Plane</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ url('/') }}" class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 font-medium text-slate-400 transition-all duration-200 hover:bg-slate-800/80 hover:text-white {{ request()->is('/') ? 'bg-indigo-600/15 text-indigo-400 font-semibold border border-indigo-500/20' : '' }}">
                            <div class="flex items-center gap-3 truncate">
                                <svg class="h-5 w-5 shrink-0 text-indigo-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                <span class="truncate">Dashboard Master</span>
                            </div>
                            <span class="shrink-0 ml-2 rounded-full bg-indigo-500/20 px-2 py-0.5 text-[10px] font-bold text-indigo-300 border border-indigo-500/30">Live</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/servers') }}" class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 font-medium text-slate-400 transition-all duration-200 hover:bg-slate-800/80 hover:text-white {{ request()->is('servers*') ? 'bg-blue-600/15 text-blue-400 font-semibold border border-blue-500/20' : '' }}">
                            <div class="flex items-center gap-3 truncate">
                                <svg class="h-5 w-5 shrink-0 text-blue-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                                <span class="truncate">Klaster Server</span>
                            </div>
                            <span class="shrink-0 ml-2 rounded-md bg-blue-500/10 px-2.5 py-0.5 text-[11px] font-bold text-blue-400 border border-blue-500/20">{{ $totalNodes }} Node</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Section 2: Tenant & Domains -->
            <div>
                <h3 class="mb-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Manajemen Tenant</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ url('/tenants') }}" class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 font-medium text-slate-400 transition-all duration-200 hover:bg-slate-800/80 hover:text-white {{ request()->is('tenants*') ? 'bg-emerald-600/15 text-emerald-400 font-semibold border border-emerald-500/20' : '' }}">
                            <div class="flex items-center gap-3 truncate">
                                <svg class="h-5 w-5 shrink-0 text-emerald-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                <span class="truncate">Daftar Tenant</span>
                            </div>
                            <span class="shrink-0 ml-2 rounded-md bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-bold text-emerald-400 border border-emerald-500/20">{{ $totalTenants }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/domains') }}" class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 font-medium text-slate-400 transition-all duration-200 hover:bg-slate-800/80 hover:text-white {{ request()->is('domains*') ? 'bg-amber-500/15 text-amber-400 font-semibold border border-amber-500/20' : '' }}">
                            <div class="flex items-center gap-3 truncate">
                                <svg class="h-5 w-5 shrink-0 text-amber-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate">Domain Alias</span>
                            </div>
                            <span class="shrink-0 ml-2 rounded-md bg-amber-500/10 px-2.5 py-0.5 text-[11px] font-bold text-amber-400 border border-amber-500/20">{{ $totalDomains }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Section 3: Integrasi API -->
            <div>
                <h3 class="mb-2.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Integrasi & DNS</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ url('/cloudflare') }}" class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 font-medium text-slate-400 transition-all duration-200 hover:bg-slate-800/80 hover:text-white {{ request()->is('cloudflare*') ? 'bg-orange-500/15 text-orange-400 font-semibold border border-orange-500/20' : '' }}">
                            <div class="flex items-center gap-3 truncate">
                                <svg class="h-5 w-5 shrink-0 text-orange-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                <span class="truncate">Cloudflare & DNS</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/logs') }}" class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 font-medium text-slate-400 transition-all duration-200 hover:bg-slate-800/80 hover:text-white {{ request()->is('logs*') ? 'bg-slate-700/50 text-white font-semibold border border-slate-600/50' : '' }}">
                            <div class="flex items-center gap-3 truncate">
                                <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span class="truncate">Log Request API</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/queues') }}" class="group flex items-center justify-between rounded-xl px-3.5 py-2.5 font-medium text-slate-400 transition-all duration-200 hover:bg-slate-800/80 hover:text-white {{ request()->is('queues*') ? 'bg-rose-500/15 text-rose-400 font-semibold border border-rose-500/20' : '' }}">
                            <div class="flex items-center gap-3 truncate">
                                <svg class="h-5 w-5 shrink-0 text-rose-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="truncate">Antrean & Sync</span>
                            </div>
                            <span class="shrink-0 ml-2 rounded-md bg-rose-500/10 px-2 py-0.5 text-[11px] font-bold text-rose-400 border border-rose-500/20">{{ $totalJobs }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Footer Banner in Sidebar -->
    <div class="mx-3 my-4 p-4 rounded-2xl bg-slate-950 border border-slate-800/80 relative overflow-hidden text-xs">
        <div class="flex items-center gap-2 mb-2">
            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <p class="font-bold text-slate-200">API Gateway Ready</p>
        </div>
        <p class="text-[11px] text-slate-400 mb-3 leading-relaxed">Koneksi ke {{ $totalNodes }} klaster server master terverifikasi aktif (< 45ms).</p>
        <button class="w-full rounded-xl bg-indigo-600 py-2 text-[11px] font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all border border-indigo-400/20 flex items-center justify-center gap-1.5">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            <span>Tes Koneksi API</span>
        </button>
    </div>
</aside>
