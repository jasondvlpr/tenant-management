<x-layouts.admin title="Manajemen Tenant & Domain">
    <!-- Alpine JS state for UI Modals and Live Data -->
    <div x-data="{ 
        openModalCreate: false, 
        openModalDomain: false,
        openModalEdit: false,
        openModalDelete: false,
        openModalNS: false,
        openModalDetail: false,
        selectedFilter: 'all', 
        searchQuery: '',
        activeTenant: null,
        activeTenantDetail: null,
        isLoadingDetail: false,
        tenants: {{ $tenants->map(function($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'remoteId' => $t->remote_tenant_id,
                'dbName' => $t->database_name,
                'domain' => $t->domain,
                'server' => $t->clusterNode ? $t->clusterNode->name : 'Unassigned',
                'serverIp' => $t->clusterNode ? $t->clusterNode->ip_address : 'N/A',
                'status' => $t->status,
                'cpu' => $t->cpu,
                'storage' => $t->storage,
                'users' => $t->users,
                'avatar' => $t->avatar,
                'color' => $t->color,
                'cfStatus' => $t->cf_status,
                'cfZoneId' => $t->cf_zone_id,
                'cfZoneStatus' => $t->cf_zone_status ?? 'pending',
                'cfNameServers' => $t->cf_nameservers ?? [],
                'checkCfUrl' => route('tenants.check-cloudflare', $t->id),
                'autoDns' => $t->auto_dns,
                'aliases' => $t->aliases->pluck('alias')->toArray(),
                'usersCount' => $t->users_count,
                'transactionsCount' => $t->transactions_count,
                'firstDepositAmount' => $t->first_deposit_amount,
                'redepositAmount' => $t->redeposit_amount,
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
        },
        openDetailManager(tenant) {
            this.activeTenant = tenant;
            this.activeTenantDetail = null;
            this.isLoadingDetail = true;
            this.openModalDetail = true;
            
            fetch('/api/central/v1/tenants/' + (tenant.remoteId || tenant.id) + '?with_stats=true&with_config=true')
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        this.activeTenantDetail = data.data;
                    }
                    this.isLoadingDetail = false;
                })
                .catch(err => {
                    this.isLoadingDetail = false;
                    console.error(err);
                });
        },
        openModalConfig: false,
        activeConfig: { settings: {}, api_configs: { game_api: {}, payment_api: {} } },
        isSavingConfig: false,
        openConfigManager() {
            let conf = this.activeTenantDetail ? this.activeTenantDetail.config : null;
            if (Array.isArray(conf)) conf = {};
            else if (typeof conf === 'string') { try { conf = JSON.parse(conf); } catch(e) { conf = {}; } }
            else if (conf) conf = JSON.parse(JSON.stringify(conf));
            else conf = {};

            if (!conf.settings || Array.isArray(conf.settings)) conf.settings = {};
            if (!conf.api_configs || Array.isArray(conf.api_configs)) conf.api_configs = {};
            if (!conf.api_configs.game_api || Array.isArray(conf.api_configs.game_api)) conf.api_configs.game_api = {};
            if (!conf.api_configs.payment_api || Array.isArray(conf.api_configs.payment_api)) conf.api_configs.payment_api = {};

            this.activeConfig = conf;
            this.openModalConfig = true;
        },
        saveConfig() {
            this.isSavingConfig = true;
            fetch('/tenants/' + this.activeTenant.id + '/config', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.activeConfig)
            }).then(res => res.json()).then(data => {
                this.isSavingConfig = false;
                if(data.success) {
                    alert('Konfigurasi berhasil disimpan!');
                    this.openModalConfig = false;
                    this.openModalDetail = false;
                } else {
                    alert('Gagal: ' + data.error);
                }
            }).catch(err => {
                this.isSavingConfig = false;
                alert('Terjadi kesalahan sistem.');
            });
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
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    Tenant Management
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Daftar Tenant Aktif
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola dan pantau semua instans tenant Anda dari satu halaman.
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
                    class="group relative flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:from-emerald-700 transition duration-200"
                >
                    <svg class="h-5 w-5 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Deploy Tenant & DNS Baru</span>
                </button>
            </div>
        </div>



        <!-- Filter & Search Toolbar -->
        <div class="mb-6 flex flex-col justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-slate-900 sm:flex-row sm:items-center">
            <div class="flex flex-wrap gap-2">
                <button @click="selectedFilter = 'all'" :class="selectedFilter === 'all' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800/60'" class="rounded-xl px-4 py-2 text-xs transition duration-200">
                    Semua Tenant ({{ $tenants->count() }})
                </button>
                <button @click="selectedFilter = 'Active'" :class="selectedFilter === 'Active' ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs transition duration-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Aktif & DNS Ready ({{ $tenants->where('status', 'Active')->count() }})
                </button>
                <button @click="selectedFilter = 'Deploying'" :class="selectedFilter === 'Deploying' ? 'bg-amber-500 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs transition duration-200">
                    <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span> Deploying & Sync ({{ $tenants->where('status', 'Deploying')->count() }})
                </button>
            </div>

            <div class="relative min-w-[280px]">
                <input type="text" x-model="searchQuery" placeholder="Cari tenant, domain, atau cluster IP..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-xs text-slate-800 focus:border-indigo-500 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>

        <!-- Tenant Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-slate-900 mb-10">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/70 text-xs uppercase text-slate-500 dark:border-slate-800/80 dark:bg-slate-950/50 dark:text-slate-400 font-semibold tracking-wider">
                            <th class="px-6 py-4">Nama Tenant & Domain Utama</th>
                            <th class="px-6 py-4">Lokasi Server Master Node</th>
                            <th class="px-6 py-4">Otomasi DNS Cloudflare</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Statistik Transaksi</th>
                            <th class="px-6 py-4 text-right">Aksi & Kendali</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <template x-for="item in tenants" :key="item.id">
                            <tr 
                                x-show="(selectedFilter === 'all' || item.status === selectedFilter) && (searchQuery === '' || item.name.toLowerCase().includes(searchQuery.toLowerCase()) || item.domain.toLowerCase().includes(searchQuery.toLowerCase()) || item.server.toLowerCase().includes(searchQuery.toLowerCase()))"
                                class="group transition duration-150 hover:bg-slate-50/70 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-bold shadow-sm transition-transform group-hover:scale-105 duration-200 text-sm"
                                            :class="item.color === 'indigo' ? 'bg-indigo-500/10 text-indigo-600 border border-indigo-500/20 dark:text-indigo-400' : (item.color === 'emerald' ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-purple-500/10 text-purple-600 border border-purple-500/20')"
                                            x-text="item.avatar"
                                        ></div>
                                        <div>
                                            <span class="block font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 transition" x-text="item.name"></span>
                                            <a :href="'http://' + item.domain" target="_blank" class="text-xs text-indigo-600 hover:underline dark:text-indigo-400 flex items-center gap-1 mt-0.5 font-mono">
                                                <span x-text="item.domain"></span>
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </a>
                                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5" x-show="item.remoteId || item.dbName">
                                                <span x-show="item.remoteId" class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold font-mono text-slate-700 dark:bg-slate-800/80 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700" x-text="'ID: ' + item.remoteId"></span>
                                                <span x-show="item.dbName" class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-[10px] font-semibold font-mono text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-500/20" x-text="'DB: ' + item.dbName"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                        <svg class="h-3.5 w-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                                        <span x-text="item.server"></span>
                                    </span>
                                    <span class="block text-[11px] font-mono text-slate-400 mt-1" x-text="item.serverIp"></span>
                                </td>

                                <td class="px-6 py-4">
                                    <button @click="openNSManager(item)" class="group flex items-center gap-2.5 text-left transition hover:opacity-85">
                                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                                            <span x-show="item.cfZoneStatus !== 'active' && !item.cfStatus.includes('Orange')" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="item.cfZoneStatus === 'active' ? 'bg-emerald-500 shadow-sm shadow-emerald-500/50' : (item.cfStatus.includes('Orange') ? 'bg-orange-500' : 'bg-amber-400')"></span>
                                        </span>
                                        <div>
                                            <span class="text-xs font-bold underline decoration-dotted underline-offset-4 block transition" :class="item.cfZoneStatus === 'active' ? 'text-emerald-600 dark:text-emerald-400' : (item.cfStatus.includes('Orange') ? 'text-orange-600 dark:text-orange-400' : 'text-amber-600 dark:text-amber-400')" x-text="item.cfZoneStatus === 'active' ? 'Active (WAF Protected)' : (item.cfStatus.includes('Orange') ? 'Proxied (Orange Cloud)' : 'Pending Name Server')"></span>
                                            <span class="text-[10px] font-semibold text-slate-400 group-hover:text-indigo-500 transition block mt-0.5">Klik untuk Info NS & Cek Live ➔</span>
                                        </div>
                                    </button>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold"
                                        :class="item.status === 'Active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="item.status === 'Active' ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse'"></span>
                                        <span x-text="item.status"></span>
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                            <span x-text="'Users: ' + item.usersCount"></span>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <span x-text="'Trx: ' + item.transactionsCount"></span>
                                        </div>
                                        <div class="text-xs font-semibold">
                                            <span class="text-emerald-600 dark:text-emerald-400" x-text="'F.Dep: Rp ' + new Intl.NumberFormat('id-ID').format(item.firstDepositAmount)"></span>
                                            <span class="text-slate-300 dark:text-slate-600 mx-1">•</span>
                                            <span class="text-indigo-600 dark:text-indigo-400" x-text="'Re.Dep: Rp ' + new Intl.NumberFormat('id-ID').format(item.redepositAmount)"></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a :href="'/tenants/' + item.id" class="rounded-xl bg-emerald-50 p-1.5 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20 transition shadow-xs" title="Lihat Detail Tenant">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        <button @click="openNSManager(item)" class="rounded-xl bg-orange-50 p-1.5 text-orange-600 hover:bg-orange-100 dark:bg-orange-500/10 dark:text-orange-400 dark:hover:bg-orange-500/20 transition shadow-xs" title="Lihat Name Server (NS) & Cek Koneksi">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                                        </button>
                                        <button @click="openDomainManager(item)" class="rounded-xl bg-slate-100 p-1.5 text-amber-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-amber-500 dark:hover:bg-slate-700/80 transition shadow-xs" title="Kelola DNS & Domain Alias">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                        </button>
                                        <button @click="openDeleteManager(item)" class="rounded-xl p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 transition" title="Nonaktifkan & Hapus Tenant"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
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
                        <a href="{{ url('/domains') }}" class="rounded-lg bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 transition">Buka Repository Global Domain ➔</a>
                    </div>

                    <ul class="space-y-2.5 max-h-48 overflow-y-auto pr-1 text-xs">
                        <template x-if="activeTenant && activeTenant.aliases && activeTenant.aliases.length > 0">
                            <template x-for="alias in activeTenant.aliases" :key="alias">
                                <li class="flex items-center justify-between rounded-xl bg-slate-50 p-3.5 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800 font-mono">
                                    <span class="font-bold text-slate-800 dark:text-slate-200" x-text="alias"></span>
                                    <span class="text-orange-500 text-[11px] font-sans font-semibold flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full bg-orange-500"></span> Proxied
                                    </span>
                                </li>
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
                                    <button @click="navigator.clipboard.writeText(ns); alert('Name Server berhasil disalin ke clipboard!');" type="button" class="rounded-lg bg-slate-800 hover:bg-slate-700 px-2.5 py-1 text-[11px] font-sans font-bold text-slate-300 transition flex items-center gap-1">
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

        <!-- Modal 5: Detail Tenant dari API -->
        <div x-show="openModalDetail" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" style="display: none;">
            <div @click.outside="openModalDetail = false" x-transition.scale.95 class="w-full max-w-2xl overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 shadow-sm">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 01-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="'Detail Tenant: ' + (activeTenant?.name || '')"></h3>
                            <p class="text-xs text-slate-500">Live Data Fetch dari Master Tenant Server (aaPanel Stats)</p>
                        </div>
                    </div>
                    <button @click="openModalDetail = false" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <div class="mt-5 min-h-[250px] relative">
                    <!-- Loading State -->
                    <div x-show="isLoadingDetail" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 dark:bg-slate-900/80 z-10 backdrop-blur-xs">
                        <svg class="h-8 w-8 animate-spin text-emerald-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 animate-pulse">Menarik data dari API Master Node...</p>
                    </div>

                    <!-- Content State -->
                    <div x-show="!isLoadingDetail && activeTenantDetail" style="display: none;">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/60 dark:bg-slate-950 dark:border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">ID Tenant (Remote)</span>
                                <span class="text-sm font-mono font-bold text-slate-900 dark:text-white" x-text="activeTenantDetail?.id"></span>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/60 dark:bg-slate-950 dark:border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Nama Database Backend</span>
                                <span class="text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="activeTenantDetail?.database"></span>
                            </div>
                        </div>

                        <!-- Stats from aaPanel -->
                        <div x-show="activeTenantDetail?.stats" class="mb-6">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                Live Statistik Server aaPanel
                            </span>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="rounded-xl p-3 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm text-center">
                                    <span class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Total Users</span>
                                    <span class="block text-lg font-black text-slate-900 dark:text-white" x-text="activeTenantDetail?.stats?.users_count"></span>
                                </div>
                                <div class="rounded-xl p-3 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm text-center">
                                    <span class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Transactions</span>
                                    <span class="block text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="activeTenantDetail?.stats?.transactions_count"></span>
                                </div>
                                <div class="col-span-2 rounded-xl p-3 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                                    <span class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Financial Metrics (IDR)</span>
                                    <div class="flex items-center justify-between mt-1">
                                        <div>
                                            <span class="block text-[9px] text-slate-400">First Deposit</span>
                                            <span class="block text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(activeTenantDetail?.stats?.first_deposit_amount || 0)"></span>
                                        </div>
                                        <div class="text-right">
                                            <span class="block text-[9px] text-slate-400">Re-deposit</span>
                                            <span class="block text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(activeTenantDetail?.stats?.redeposit_amount || 0)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Domains List -->
                        <div x-show="activeTenantDetail?.domains?.length > 0">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Registered Domains</span>
                            <div class="max-h-40 overflow-y-auto space-y-2 pr-2">
                                <template x-for="dom in activeTenantDetail?.domains" :key="dom.domain">
                                    <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800 font-mono text-xs">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="dom.domain"></span>
                                        </div>
                                        <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                              :class="dom.is_suspended ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400'"
                                              x-text="dom.is_suspended ? 'Suspended' : 'Active'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <button @click="openConfigManager()" type="button" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-indigo-500 shadow-md transition">Edit Konfigurasi (Config)</button>
                            <button @click="openModalDetail = false" type="button" class="rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-bold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 transition">Tutup Detail</button>
                        </div>
                    </div>
                    
                    <div x-show="!isLoadingDetail && !activeTenantDetail" style="display: none;" class="py-10 text-center">
                        <svg class="h-12 w-12 mx-auto text-rose-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">Gagal Mengambil Data API</p>
                        <p class="text-xs text-slate-500 mt-1">Pastikan server master dalam keadaan aktif atau remote ID valid.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 6: Edit Config -->
        <div x-show="openModalConfig" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" style="display: none;">
            <div @click.outside="openModalConfig = false" x-transition.scale.95 class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-500 border border-indigo-500/20 shadow-sm">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="'Konfigurasi: ' + (activeTenant?.name || '')"></h3>
                            <p class="text-xs text-slate-500">Edit pengaturan situs dan integrasi API.</p>
                        </div>
                    </div>
                    <button @click="openModalConfig = false" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <div class="mt-5 space-y-6">
                    <!-- General Settings -->
                    <div>
                        <h4 class="text-xs font-bold uppercase text-slate-500 mb-3 border-b border-slate-100 dark:border-slate-800 pb-1">Pengaturan Umum (Settings)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Nama Situs</label>
                                <input type="text" x-model="activeConfig.settings.site_name" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Maintenance Mode</label>
                                <select x-model="activeConfig.settings.maintenance_mode" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                    <option value="0">Normal (Nonaktif)</option>
                                    <option value="1">Maintenance (Aktif)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Minimal Deposit</label>
                                <input type="number" x-model="activeConfig.settings.min_deposit" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Minimal Withdraw</label>
                                <input type="number" x-model="activeConfig.settings.min_withdraw" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Alternatif URL (Opsional)</label>
                                <input type="text" x-model="activeConfig.settings.alt_url" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">AMP URL (Opsional)</label>
                                <input type="text" x-model="activeConfig.settings.amp_url" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- API Configs: Game API -->
                    <div>
                        <h4 class="text-xs font-bold uppercase text-slate-500 mb-3 border-b border-slate-100 dark:border-slate-800 pb-1">Integrasi Game API</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">API URL</label>
                                <input type="text" x-model="activeConfig.api_configs.game_api.api_url" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Agent Code</label>
                                <input type="text" x-model="activeConfig.api_configs.game_api.agent_code" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Agent Token</label>
                                <input type="text" x-model="activeConfig.api_configs.game_api.agent_token" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Secret Key</label>
                                <input type="text" x-model="activeConfig.api_configs.game_api.secret_key" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- API Configs: Payment API -->
                    <div>
                        <h4 class="text-xs font-bold uppercase text-slate-500 mb-3 border-b border-slate-100 dark:border-slate-800 pb-1">Integrasi Payment API</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">API URL</label>
                                <input type="text" x-model="activeConfig.api_configs.payment_api.api_url" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Agent Code</label>
                                <input type="text" x-model="activeConfig.api_configs.payment_api.agent_code" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">Secret Key</label>
                                <input type="text" x-model="activeConfig.api_configs.payment_api.secret_key" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                    <button @click="openModalConfig = false" type="button" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                    <button @click="saveConfig()" :disabled="isSavingConfig" :class="isSavingConfig ? 'opacity-75 cursor-wait' : 'hover:bg-indigo-500'" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/20 transition flex items-center justify-center min-w-[150px]">
                        <svg x-show="isSavingConfig" style="display: none;" class="mr-2 h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isSavingConfig ? 'Menyimpan...' : 'Simpan Konfigurasi'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
