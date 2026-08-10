<x-layouts.admin title="Manajemen Tenant & Domain">
    <!-- Alpine JS state for UI Modals and Live Data -->
    <div x-data="{ 
        openModalCreate: false, 
        openModalDomain: false,
        openModalEdit: false,
        openModalDelete: false,
        openModalNS: false,
        selectedFilter: 'all', 
        searchQuery: '',
        activeTenant: null,
        tenants: {{ $tenants->map(function($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'remoteId' => $t->remote_tenant_id,
                'dbName' => $t->database_name,
                'domain' => $t->domains[0]['domain'] ?? 'N/A',
                'server' => $t->clusterNode ? $t->clusterNode->name : 'Unassigned',
                'serverIp' => $t->clusterNode ? $t->clusterNode->ip_address : 'N/A',
                'status' => $t->status,
                'cpu' => $t->cpu,
                'storage' => $t->storage,
                'users' => $t->users,
                'avatar' => $t->avatar,
                'color' => $t->color,
                'cfStatus' => $t->domains[0]['cf_status'] ?? 'pending',
                'cfZoneId' => $t->domains[0]['cf_zone_id'] ?? null,
                'cfZoneStatus' => $t->domains[0]['cf_zone_status'] ?? 'pending',
                'cfNameServers' => $t->domains[0]['cf_nameservers'] ?? [],
                'checkCfUrl' => route('tenants.check-cloudflare', $t->id),
                'aliases' => collect($t->domains ?? [])->skip(1)->pluck('domain')->toArray(),
                'deleteUrl' => route('tenants.destroy', $t->id)
            ];
        })->toJson() }},
        openDomainManager(tenant) {
            this.activeTenant = tenant;
            this.openModalDomain = true;
        },
        openEditManager(tenant) {
            this.activeTenant = tenant;
            this.openModalEdit = true;
        },
        openDeleteManager(tenant) {
            this.activeTenant = tenant;
            this.openModalDelete = true;
        },
        openNSManager(tenant) {
            this.activeTenant = tenant;
            this.openModalNS = true;
        }
    }">

        <!-- Notification Banner -->
        @if(session('success'))
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-500/30 p-4 text-sm font-semibold text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 flex items-center justify-between shadow-sm animate-pulse">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="event.target.closest('div').remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>
        @endif

        @if($errors->any() || session('error'))
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-500/30 p-4 text-sm font-semibold text-rose-800 dark:bg-rose-500/10 dark:text-rose-400 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
            <button @click="event.target.closest('div').remove()" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>
        @endif

        <!-- Page Header & Title Banner -->
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    Central Command Hub & Cloudflare Auto-DNS
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Multi-Server Tenant & Domain Registry
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola instans tenant di berbagai cluster server master dan otomatisasi DNS zona Cloudflare dalam satu kendali terpusat.
                </p>
            </div>
            
            <div class="flex flex-wrap gap-2.5">
                <form action="{{ route('tenants.sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="group relative flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800/90 px-4.5 py-2.5 text-sm font-semibold text-slate-200 shadow-sm hover:bg-slate-800 hover:text-white hover:border-emerald-500/50 transition duration-200">
                        <svg class="h-4 w-4 text-emerald-400 group-hover:rotate-180 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <span>Sync Remote API (Master Nodes)</span>
                    </button>
                </form>

                <button 
                    @click="openModalCreate = true" 
                    class="group relative flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:from-indigo-700 transition duration-200"
                >
                    <svg class="h-5 w-5 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Deploy Tenant & DNS Baru</span>
                </button>
            </div>
        </div>

        <!-- Infrastructure Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Tenant Aktif</span>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $tenants->count() }} <span class="text-xs font-medium text-emerald-500">Live Deployments</span></h3>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Master Node Clusters</span>
                <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $nodes->count() }} <span class="text-xs font-medium text-slate-400">Connected</span></h3>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Cloudflare Auto-Sync</span>
                <h3 class="text-2xl font-black text-orange-500 mt-1">Ready <span class="text-xs font-medium text-slate-400">API Gateway</span></h3>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Database Backend</span>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 uppercase mt-1">{{ config('database.default') }}</h3>
            </div>
        </div>



        <!-- Modal 1: Deploy Tenant Baru -->
        <div x-show="openModalCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openModalCreate = false" x-transition.scale.95 class="w-full max-w-xl overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-500/30">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Deploy Tenant Baru</h3>
                            <p class="text-xs text-slate-500">Otomatis kirim perintah API ke Server Master & buat DNS di Cloudflare</p>
                        </div>
                    </div>
                    <button @click="openModalCreate = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <form action="{{ route('tenants.store') }}" method="POST" class="mt-5 space-y-4" x-data="{ isDeploying: false, clientName: '', tenantId: '' }" @submit="isDeploying = true">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Nama Instans / Klien <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="clientName" @input="tenantId = clientName.replace(/[^a-zA-Z0-9]/g, '').toUpperCase()" placeholder="Contoh: Toko Maju Jaya" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">ID Unik Tenant API <span class="text-rose-500">*</span></label>
                            <input type="text" name="remote_tenant_id" x-model="tenantId" placeholder="Contoh: TOKOMAJUJAYA" class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm font-mono uppercase text-slate-500 cursor-not-allowed dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400" required readonly>
                            <span class="text-[10px] text-slate-400 mt-1 block">Tanpa spasi / simbol. Dipakai untuk nama DB aaPanel.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Domain Utama Klien</label>
                            <input type="text" name="domain" placeholder="megastore.com" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                            <span class="text-[10px] text-slate-400 mt-1 block">Tanpa http:// atau www.</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Pilih Server Master Node</label>
                            <select name="cluster_node_id" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                                @foreach($nodes as $node)
                                <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->location }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Cloudflare DNS Automation Toggle -->
                    <div class="p-4 rounded-2xl bg-orange-500/10 border border-orange-500/30 flex items-start gap-3.5 mt-2">
                        <input type="checkbox" name="auto_dns" value="1" id="cf_auto" class="mt-1 h-4 w-4 rounded border-orange-400 text-orange-600 focus:ring-orange-500" checked>
                        <label for="cf_auto" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <strong class="font-bold text-slate-900 dark:text-white block mb-0.5">Otomatisasi DNS Cloudflare (Orange Cloud Proxied)</strong>
                            Daftarkan zona A-Record & CNAME di akun Cloudflare pusat secara otomatis ke IP klaster yang dipilih saat deployment dilakukan via antrean.
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openModalCreate = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                        <button type="submit" :disabled="isDeploying" :class="isDeploying ? 'opacity-75 cursor-wait' : 'hover:bg-indigo-500'" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/20 transition flex items-center justify-center min-w-[240px]">
                            <svg x-show="isDeploying" style="display: none;" class="mr-2 h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isDeploying ? 'Mendeploy...' : 'Eksekusi API Deploy & DNS'">Eksekusi API Deploy & DNS</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2: DNS & Domain Alias Manager -->
        <div x-show="openModalDomain" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openModalDomain = false" x-transition.scale.95 class="w-full max-w-2xl overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="activeTenant ? 'Domain Alias: ' + activeTenant.name : 'Domain Manager'"></h3>
                            <p class="text-xs text-slate-500" x-text="activeTenant ? 'Primary Domain: ' + activeTenant.domain : ''"></p>
                        </div>
                    </div>
                    <button @click="openModalDomain = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <div class="mt-4">
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase text-slate-400">Daftar Domain Alias Aktif (Cloudflare Managed)</span>
                    </div>

                    <ul class="space-y-2.5 max-h-48 overflow-y-auto pr-1 text-xs">
                        <template x-if="activeTenant && activeTenant.aliases && activeTenant.aliases.length > 0">
                            <template x-for="alias in activeTenant.aliases" :key="alias">
                                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="alias"></span>
                                        <span class="text-[10px] uppercase font-bold text-slate-400">Alias</span>
                                    </div>
                                    <a :href="'http://' + alias" target="_blank" class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg dark:bg-indigo-500/10 dark:text-indigo-400 dark:hover:bg-indigo-500/20 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                </div>
                            </template>
                        </template>
                        <template x-if="!activeTenant || !activeTenant.aliases || activeTenant.aliases.length === 0">
                            <li class="p-6 text-center text-slate-400 font-sans border border-dashed rounded-xl border-slate-300 dark:border-slate-800">
                                Belum ada domain alias terdaftar untuk tenant ini.
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="mt-6 flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button @click="openModalDomain = false" class="rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-bold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900">Tutup Jendala</button>
                </div>
            </div>
        </div>

        <!-- Modal 3: Delete Confirmation -->
        <div x-show="openModalDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openModalDelete = false" x-transition.scale.95 class="w-full max-w-md overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 mb-4">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Nonaktifkan Tenant Ini?</h3>
                <p class="text-xs text-slate-500 mt-1 mb-6">
                    Anda yakin ingin menonaktifkan dan menghapus instans <strong class="text-slate-800 dark:text-white" x-text="activeTenant?.name"></strong>? Sistem akan mengirim perintah DELETE ke server master & menghapus record DNS Cloudflare.
                </p>

                <form :action="activeTenant?.deleteUrl" method="POST" class="flex justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="openModalDelete = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/20 hover:bg-rose-500 transition">Ya, Hapus Sekarang</button>
                </form>
            </div>
        </div>

        <!-- Modal 4: Cloudflare Name Servers (NS) & Status Inspector -->
        <div x-show="openModalNS" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" style="display: none;">
            <div @click.outside="openModalNS = false" x-transition.scale.95 class="w-full max-w-lg overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-500/10 text-orange-500 border border-orange-500/20 shadow-sm">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">Inspektur Cloudflare DNS</h3>
                            <p class="text-xs text-slate-500">Status Propagasi & Konfigurasi Name Server</p>
                        </div>
                    </div>
                    <button @click="openModalNS = false" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <!-- Status Card -->
                <div class="mt-5 rounded-2xl p-4.5 border transition duration-200"
                    :class="(activeTenant && activeTenant.cfZoneStatus === 'active') ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-900 dark:text-emerald-300' : 'bg-amber-500/10 border-amber-500/30 text-amber-900 dark:text-amber-300'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="relative flex h-3 w-3">
                                <span x-show="activeTenant && activeTenant.cfZoneStatus !== 'active'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3" :class="(activeTenant && activeTenant.cfZoneStatus === 'active') ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                            </span>
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider block opacity-80">Status Domain di Cloudflare</span>
                                <span class="text-base font-black uppercase tracking-tight block" x-text="activeTenant?.cfZoneStatus === 'active' ? 'Active (Protected by WAF)' : (activeTenant?.cfZoneStatus || 'Pending Verification')"></span>
                            </div>
                        </div>
                        <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-white/60 dark:bg-black/40 border border-current/20" x-text="activeTenant?.domain"></span>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed opacity-90" x-show="activeTenant && activeTenant.cfZoneStatus !== 'active'">
                        Domain Anda saat ini sedang menunggu propagasi Name Server (NS). Silakan salin kedua Name Server Cloudflare di bawah ini dan pasangkan pada panel registrar tempat Anda membeli domain (Niagahoster, Namecheap, GoDaddy, dll).
                    </p>
                    <p class="mt-3 text-xs leading-relaxed opacity-90" x-show="activeTenant && activeTenant.cfZoneStatus === 'active'">
                        Domain ini sudah resmi beroperasi 100% di atas jaringan Cloudflare! Seluruh fitur Reverse Proxy (Orange Cloud), proteksi DDoS, dan SSL/TLS berjalan aktif.
                    </p>
                </div>

                <!-- Nameserver Cards -->
                <div class="mt-5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Cloudflare Name Servers (NS Resmi)</span>
                    <template x-if="activeTenant && activeTenant.cfNameServers && activeTenant.cfNameServers.length > 0">
                        <div class="space-y-2.5">
                            <template x-for="(ns, index) in activeTenant.cfNameServers" :key="ns">
                                <div class="flex items-center justify-between rounded-xl bg-slate-900 px-4 py-3 text-white font-mono text-xs border border-slate-800 shadow-inner">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-md bg-orange-500/20 text-orange-400 font-sans font-black text-[10px]" x-text="'0' + (index + 1)"></span>
                                        <span class="text-emerald-400 font-bold tracking-wider" x-text="ns"></span>
                                    </div>
                                    <button @click="navigator.clipboard.writeText(ns); window.toast('Name Server berhasil disalin ke clipboard!');" type="button" class="rounded-lg bg-slate-800 hover:bg-slate-700 px-2.5 py-1 text-[11px] font-sans font-bold text-slate-300 transition flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                        <span>Salin</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!activeTenant || !activeTenant.cfNameServers || activeTenant.cfNameServers.length === 0">
                        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-800 p-5 text-center bg-slate-50 dark:bg-slate-950">
                            <svg class="h-8 w-8 mx-auto text-slate-400 mb-2 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 01-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Name Server belum ditarik dari Cloudflare.</p>
                            <p class="text-[11px] text-slate-500 mt-1">Klik tombol Cek & Verifikasi di bawah untuk memeriksa status & menarik Name Server dari API Cloudflare.</p>
                        </div>
                    </template>
                </div>

                <!-- Action Toolbar -->
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <span class="text-[11px] text-slate-400 font-mono" x-show="activeTenant && activeTenant.cfZoneId" x-text="'Zone ID: ' + activeTenant?.cfZoneId"></span>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                        <button @click="openModalNS = false" type="button" class="rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition">Tutup</button>
                        <form :action="activeTenant?.checkCfUrl" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-orange-500/25 hover:opacity-95 transition">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                <span>Cek & Verifikasi Live Status ⚡</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
