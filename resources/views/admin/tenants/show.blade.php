<x-layouts.admin title="Detail Tenant: {{ $tenant->name }}">
    <div class="px-4 py-8 sm:px-6 lg:px-8" x-data="{
        openModalDomain: false,
        isDeploying: false,
        activeTab: 'domains',
        isLoadingConfig: false,
        isCheckingCF: false,
        cfZoneStatus: '{{ $domains[0]['cf_zone_status'] ?? 'pending' }}',
        cfStatus: '{{ $domains[0]['cf_status'] ?? 'pending' }}',
        cfNameservers: JSON.parse(atob('{!! base64_encode(json_encode(is_string($domains[0]['cf_nameservers'] ?? []) ? json_decode($domains[0]['cf_nameservers'] ?? '[]', true) : (empty($domains[0]['cf_nameservers']) ? [] : $domains[0]['cf_nameservers']))) !!}')),
        checkCF() {
            this.isCheckingCF = true;
            fetch('{{ route('tenants.check-cloudflare', $tenant->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.isCheckingCF = false;
                if(data.success) {
                    this.cfZoneStatus = data.cf_zone_status || 'pending';
                    this.cfStatus = data.cf_status || 'Pending Check';
                    if(data.cf_nameservers && data.cf_nameservers.length > 0) {
                        this.cfNameservers = data.cf_nameservers;
                    }
                    alert(data.message);
                } else {
                    alert('Gagal: ' + (data.error || 'Terjadi kesalahan'));
                }
            })
            .catch(err => {
                this.isCheckingCF = false;
                alert('Terjadi kesalahan jaringan.');
                console.error(err);
            });
        },
        configLoaded: false,
        activeConfig: { settings: {}, api_configs: { game_api: {}, payment_api: {} } },
        isSavingConfig: false,
        loadConfig() {
            if(this.configLoaded) return;
            this.isLoadingConfig = true;
            fetch('/api/central/v1/tenants/{{ $tenant->remote_tenant_id ?: $tenant->id }}?with_config=true')
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success' && data.data && data.data.config) {
                        let conf = data.data.config;
                        if (Array.isArray(conf)) conf = {};
                        else if (typeof conf === 'string') { try { conf = JSON.parse(conf); } catch(e) { conf = {}; } }
                        else if (conf) conf = JSON.parse(JSON.stringify(conf));
                        else conf = {};

                        if (!conf.settings || Array.isArray(conf.settings)) conf.settings = {};
                        if (!conf.api_configs || Array.isArray(conf.api_configs)) conf.api_configs = {};
                        if (!conf.api_configs.game_api || Array.isArray(conf.api_configs.game_api)) conf.api_configs.game_api = {};
                        if (!conf.api_configs.payment_api || Array.isArray(conf.api_configs.payment_api)) conf.api_configs.payment_api = {};

                        this.activeConfig = conf;
                    }
                    this.configLoaded = true;
                    this.isLoadingConfig = false;
                })
                .catch(err => {
                    this.isLoadingConfig = false;
                    console.error(err);
                });
        },
        saveConfig() {
            this.isSavingConfig = true;
            fetch('{{ route('tenants.update-config', $tenant->id) }}', {
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
                } else {
                    alert('Gagal: ' + data.error);
                }
            }).catch(err => {
                this.isSavingConfig = false;
                alert('Terjadi kesalahan jaringan.');
                console.error(err);
            });
        }
    }">
        <!-- Header & Breadcrumbs -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex text-sm text-slate-500 mb-2 font-medium" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2">
                        <li class="inline-flex items-center">
                            <a href="{{ route('tenants.index') }}" class="hover:text-indigo-600 transition-colors">Tenants</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-slate-400 mx-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                <span class="text-slate-800 dark:text-slate-200">{{ $tenant->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-{{ $tenant->color }}-100 dark:bg-{{ $tenant->color }}-500/20 text-{{ $tenant->color }}-600 dark:text-{{ $tenant->color }}-400 flex items-center justify-center font-bold text-lg shadow-sm">
                        {{ $tenant->avatar }}
                    </span>
                    {{ $tenant->name }}
                </h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('tenants.index') }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 shadow-sm transition">
                    Kembali
                </a>
                <button @click="openModalDomain = true" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Domain
                </button>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 p-4 border border-rose-200 dark:bg-rose-500/10 dark:border-rose-500/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-rose-800 dark:text-rose-300">Terdapat kesalahan:</h3>
                    <div class="mt-2 text-sm text-rose-700 dark:text-rose-400">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Info Panel -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Informasi Tenant</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">ID Unik Tenant</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm font-medium text-slate-800 dark:text-slate-200">{{ $tenant->remote_tenant_id }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">API ID</span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Status Server</span>
                            @if(in_array($tenant->status, ['Active', 'Deployed', 'Online']))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @elseif($tenant->status === 'Deploying')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20 shadow-sm">
                                    <svg class="animate-spin w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Deploying
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> {{ $tenant->status }}
                                </span>
                            @endif
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-200 dark:border-slate-700 mt-2">
                            <div class="mt-2">
                                <button @click="checkCF()" :disabled="isCheckingCF" type="button" class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition disabled:opacity-50">
                                    <svg x-show="!isCheckingCF" class="h-3.5 w-3.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    <svg x-show="isCheckingCF" class="h-3.5 w-3.5 text-orange-500 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    <span x-text="isCheckingCF ? 'Memeriksa DNS...' : 'Cek Status DNS ke Cloudflare'"></span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Master Node</span>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                                <span class="text-sm text-slate-800 dark:text-slate-200 font-medium">{{ $tenant->clusterNode->name ?? 'Unknown Node' }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Database Name</span>
                            <span class="font-mono text-sm text-slate-700 dark:text-slate-300">{{ $tenant->database_name }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Terdaftar Sejak</span>
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $tenant->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Domain Panel -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-end">
                        <div class="flex gap-6 border-b border-transparent">
                            <button @click="activeTab = 'domains'" :class="activeTab === 'domains' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'" class="pb-2 border-b-2 font-bold text-sm sm:text-base transition-colors">
                                Daftar Domain
                            </button>
                            <button @click="activeTab = 'config'; loadConfig()" :class="activeTab === 'config' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'" class="pb-2 border-b-2 font-bold text-sm sm:text-base transition-colors">
                                Konfigurasi API
                            </button>
                        </div>
                        <span x-show="activeTab === 'domains'" class="bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-400 text-xs font-bold px-2.5 py-1 rounded-full mb-1">
                            {{ count($domains) }} Domain
                        </span>
                    </div>
                    
                    <div x-show="activeTab === 'domains'" class="p-0 overflow-x-auto">
                        <table class="w-full text-left border-collapse border border-slate-200 dark:border-slate-700">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                                    <th class="border border-slate-200 dark:border-slate-700 py-3 px-6 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nama Domain</th>
                                    <th class="border border-slate-200 dark:border-slate-700 py-3 px-6 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Subdomain</th>
                                    <th class="border border-slate-200 dark:border-slate-700 py-3 px-6 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status CF</th>
                                    <th class="border border-slate-200 dark:border-slate-700 py-3 px-6 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tipe Record</th>
                                    <th class="border border-slate-200 dark:border-slate-700 py-3 px-6 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @foreach($domains as $dom)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                        <td class="border border-slate-200 dark:border-slate-700 py-4 px-6 align-middle">
                                            <div class="flex items-center gap-2">
                                                <a href="http://{{ $dom['domain'] }}" target="_blank" class="font-mono text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                                    {{ $dom['domain'] }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="border border-slate-200 dark:border-slate-700 py-4 px-6 align-middle">
                                            <div class="flex flex-col gap-1">
                                                @foreach(($dom['subdomains'] ?? []) as $sub)
                                                    <div class="flex items-center gap-2 group">
                                                        <a href="http://{{ $sub }}.{{ $dom['domain'] }}" target="_blank" class="text-[11px] font-mono text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 transition">
                                                            {{ $sub }}
                                                        </a>
                                                        <form action="{{ route('domains.subdomains.destroy', ['tenant' => $tenant->id, 'domainId' => $dom['id'], 'subdomain' => $sub]) }}" method="POST" onsubmit="return confirm('Hapus subdomain {{ $sub }}.{{ $dom['domain'] }}?');" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="opacity-0 group-hover:opacity-100 p-0.5 text-rose-400 hover:text-rose-600 dark:hover:text-rose-300 transition" title="Hapus Subdomain">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                                <form action="{{ route('domains.subdomains.store', ['tenant' => $tenant->id, 'domainId' => $dom['id']]) }}" method="POST" class="mt-2 flex items-center gap-1">
                                                    @csrf
                                                    <input type="text" name="subdomain" placeholder="subdomain" class="w-24 text-[11px] rounded bg-slate-100 px-2 py-1 text-slate-700 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-0 dark:bg-slate-800 dark:text-slate-300" required>
                                                    <button type="submit" class="shrink-0 p-1 rounded text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/20 dark:hover:text-indigo-400 transition" title="Tambah Subdomain">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="border border-slate-200 dark:border-slate-700 py-4 px-6 align-middle">
                                            @if(str_contains(strtolower($dom['cf_status']), 'proxied'))
                                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600 dark:text-orange-400">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.43,8.08c-0.27-2.61-2.48-4.66-5.18-4.66c-2.1,0-3.93,1.25-4.78,3.06C7.03,6.38,6.53,6.31,6,6.31 C4.34,6.31,3,7.66,3,9.31c0,0.27,0.04,0.53,0.11,0.78C2.42,10.74,2,11.83,2,13c0,2.21,1.79,4,4,4h13c1.66,0,3-1.34,3-3 C22,11.45,20.02,9.33,17.43,8.08z"/></svg>
                                                    Proxied
                                                </span>
                                            @elseif(str_contains(strtolower($dom['cf_status']), 'pending'))
                                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                                    Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                                                    {{ $dom['cf_status'] }}
                                                </span>
                                            @endif
                                            @if(!empty($dom['cf_nameservers']))
                                                <div class="mt-2 text-xs font-mono text-slate-400">
                                                    <div>NS:</div>
                                                    @foreach($dom['cf_nameservers'] as $ns)
                                                    <div class="flex items-center justify-between">
                                                        <span>{{ $ns }}</span>
                                                        <button @click="navigator.clipboard.writeText('{{ $ns }}'); alert('Name Server disalin ke clipboard!');" class="text-indigo-500 hover:text-indigo-700 transition">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                        </button>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="border border-slate-200 dark:border-slate-700 py-4 px-6 align-middle">
                                            <span class="text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 dark:text-slate-400 px-2 py-1 rounded border border-slate-200 dark:border-slate-700">
                                                {{ $dom['type'] ?? 'CNAME' }}
                                            </span>
                                        </td>
                                        <td class="border border-slate-200 dark:border-slate-700 py-4 px-6 align-middle text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                    <form action="{{ route('domains.destroy', $dom['id']) }}" method="POST" onsubmit="return confirm('Hapus domain {{ $dom['domain'] }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg dark:text-rose-400 dark:hover:bg-rose-500/10 transition" title="Hapus Domain">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- TAB 2: CONFIG -->
                    <div x-show="activeTab === 'config'" style="display: none;" class="p-6">
                        <div x-show="isLoadingConfig" class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <svg class="animate-spin h-8 w-8 mb-4 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span class="text-sm font-medium">Memuat konfigurasi dari node...</span>
                        </div>
                        
                        <div x-show="!isLoadingConfig && configLoaded" class="space-y-6">
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
                            
                            <!-- Save Button -->
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                                <button type="button" @click="saveConfig()" :disabled="isSavingConfig" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition">
                                    <svg x-show="isSavingConfig" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    <span x-text="isSavingConfig ? 'Menyimpan...' : 'Simpan Konfigurasi'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Domain Modal -->
        <div x-show="openModalDomain" class="relative z-50" style="display: none;">
            <div x-show="openModalDomain" x-transition.opacity class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="openModalDomain" x-transition class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Tambah Domain Alias</h3>
                            <button @click="openModalDomain = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="px-6 py-5">
                            <form action="{{ route('domains.store') }}" method="POST" class="space-y-4" @submit="isDeploying = true">
                                @csrf
                                <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                                
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Nama Domain Alias</label>
                                    <input type="text" name="alias" placeholder="contoh.com" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Daftar Subdomain (Opsional)</label>
                                    <input type="text" name="subdomains" placeholder="contoh: www, api, admin" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                    <span class="text-[10px] text-slate-400 mt-1 block">Pisahkan dengan koma. Ini hanya untuk pendataan di panel dan belum otomatis dibuatkan DNS/VHost terpisah.</span>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/50">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="cf_sync" value="1" class="mt-1 h-4 w-4 rounded border-orange-400 text-orange-600 focus:ring-orange-500" checked>
                                        <div>
                                            <span class="block text-sm font-bold text-slate-800 dark:text-slate-200">Sinkronisasi Cloudflare (Auto)</span>
                                            <span class="block text-xs text-slate-500 mt-0.5">Sistem akan mendaftarkan zone dan menambah DNS Record ke IP Node Server secara otomatis.</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="mt-6 flex gap-3">
                                    <button type="button" @click="openModalDomain = false" class="flex-1 px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                        Batal
                                    </button>
                                    <button type="submit" :disabled="isDeploying" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed shadow-sm shadow-indigo-600/20">
                                        <span x-show="!isDeploying">Tambah & Sinkron</span>
                                        <span x-show="isDeploying" class="flex items-center gap-2">
                                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            Memproses...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
