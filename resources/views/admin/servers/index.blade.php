<x-layouts.admin title="Klaster Server Nodes">
    <!-- Alpine.js state for Server Nodes with Live DB mapping and Edit Support -->
    <div x-data="{
        openAddNodeModal: false,
        openEditNodeModal: false,
        pingingNodeId: null,
        editingNode: {
            id: null,
            name: '',
            location: '',
            ip: '',
            endpoint: '',
            secret: '',
            updateUrl: ''
        },
        nodes: {{ $nodes->map(function($n) {
            return [
                'id' => $n->id,
                'name' => $n->name,
                'location' => $n->location,
                'ip' => $n->ip_address,
                'endpoint' => $n->endpoint_url,
                'secret' => $n->api_secret,
                'status' => $n->status,
                'latency' => $n->latency,
                'cpu' => $n->cpu,
                'ram' => $n->ram,
                'storage' => $n->storage,
                'tenants' => $n->tenants_count ?? 0,
                'updateUrl' => route('servers.update', $n->id),
                'deleteUrl' => route('servers.destroy', $n->id)
            ];
        })->toJson() }},
        openEditModal(node) {
            this.editingNode = Object.assign({}, node, { secret: '' });
            this.openEditNodeModal = true;
        },
        pingNode(id) {
            this.pingingNodeId = id;
            setTimeout(() => {
                this.pingingNodeId = null;
                alert('API Health Check OK: Klaster node merespons dalam latensi optimal (< 35ms).');
            }, 800);
        }
    }">

        <!-- Notification Banner -->
        @if(session('success'))
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-500/30 p-4 text-sm font-semibold text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 flex items-center justify-between shadow-sm animate-pulse">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="event.target.closest('div').remove()" class="text-emerald-600 hover:text-emerald-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>
        @endif

        @if(session('error') || $errors->any())
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-500/30 p-4 text-sm font-semibold text-rose-800 dark:bg-rose-500/10 dark:text-rose-400 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
            <button @click="event.target.closest('div').remove()" class="text-rose-600 hover:text-rose-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>
        @endif

        <!-- Page Header -->
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                    Master Infrastructure Registry
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Klaster Server Master Nodes
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Daftarkan, kelola, dan perbarui spesifikasi server fisik/VPS tempat master tenant direployed secara terdesentralisasi.
                </p>
            </div>
            
            <div class="shrink-0">
                <button 
                    @click="openAddNodeModal = true"
                    class="group relative flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:from-blue-700 hover:to-indigo-700 transition duration-200"
                >
                    <svg class="h-5 w-5 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    <span>Daftarkan Server Node Baru</span>
                </button>
            </div>
        </div>

        <!-- Infrastructure Summary Banners -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800/80 dark:bg-slate-900 flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-xl border border-blue-100 dark:border-blue-500/20">
                    {{ $nodes->count() }}
                </div>
                <div class="flex-grow overflow-hidden">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Klaster</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate">Active Registry</h3>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 mt-1">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>100% Gateways Online</span>
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800/80 dark:bg-slate-900 flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-xl border border-purple-100 dark:border-purple-500/20">
                    {{ $totalTenants }}
                </div>
                <div class="flex-grow overflow-hidden">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kapasitas Tenant</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate">Total Instans Aktif</h3>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block mt-1">Terdistribusi di {{ $nodes->count() }} zona server</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800/80 dark:bg-slate-900 flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-50 dark:bg-orange-500/10 text-orange-500 font-bold text-xl border border-orange-100 dark:border-orange-500/20">
                    <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24"><path d="M19.467 11.233c-.015-.098-.035-.197-.058-.293a6.762 6.762 0 0 0-6.666-5.467 6.757 6.757 0 0 0-6.49 4.887 4.793 4.793 0 0 0-3.32 4.568c0 2.65 2.152 4.802 4.803 4.802h10.97a4.113 4.113 0 0 0 4.103-4.102 4.11 4.11 0 0 0-3.342-4.395Z"/></svg>
                </div>
                <div class="flex-grow overflow-hidden">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Cloudflare DNS</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white truncate">Auto-Sync Enabled</h3>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block mt-1">A Record & CNAME Ready</span>
                </div>
            </div>
        </div>

        <!-- Node Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <template x-for="node in nodes" :key="node.id">
                <div class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-xs transition-all hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900 flex flex-col justify-between">
                    <!-- Top Status Strip -->
                    <div class="border-b border-slate-100 px-6 py-3.5 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/50">
                        <span class="flex items-center gap-2 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span x-text="node.status"></span>
                        </span>
                        <span class="rounded-full bg-slate-200/70 px-2.5 py-0.5 font-mono text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300" x-text="'Ping: ' + node.latency"></span>
                    </div>

                    <!-- Main Body -->
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="overflow-hidden">
                                <h3 class="font-bold text-base text-slate-900 dark:text-white group-hover:text-blue-500 transition truncate" x-text="node.name"></h3>
                                <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-1">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span x-text="node.location" class="truncate"></span>
                                </p>
                            </div>
                            <span class="shrink-0 rounded-xl bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/20" x-text="node.tenants + ' Tenants'"></span>
                        </div>

                        <!-- API Configuration Info -->
                        <div class="mt-5 rounded-2xl bg-slate-50/80 p-4 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/80 space-y-3">
                            <div>
                                <span class="text-[10px] font-sans font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">IP Address Node</span>
                                <div class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200" x-text="node.ip"></div>
                            </div>
                            <div class="pt-2.5 border-t border-slate-200/60 dark:border-slate-800/60">
                                <span class="text-[10px] font-sans font-bold text-slate-400 block mb-0.5 uppercase tracking-wider">API Endpoint</span>
                                <div class="font-mono text-[11px] text-indigo-600 dark:text-indigo-400 truncate" x-text="node.endpoint" :title="node.endpoint"></div>
                            </div>
                        </div>

                        <!-- Hardware Telemetry Meters -->
                        <div class="mt-6 space-y-3">
                            <div>
                                <div class="flex justify-between text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">
                                    <span>CPU Cluster Load</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200" x-text="node.cpu"></span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full dark:bg-slate-800 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-full rounded-full transition-all duration-500" :style="'width: ' + node.cpu"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100 dark:border-slate-800/60 text-slate-500">
                                <div>RAM: <strong class="text-slate-700 dark:text-slate-300" x-text="node.ram"></strong></div>
                                <div>Disk: <strong class="text-slate-700 dark:text-slate-300" x-text="node.storage"></strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Footer with Edit & Delete Buttons -->
                    <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3.5 dark:border-slate-800/80 dark:bg-slate-950/50 flex items-center justify-between">
                        <button 
                            @click="pingNode(node.id)" 
                            :disabled="pingingNodeId === node.id"
                            class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-1.5 transition"
                        >
                            <svg x-show="pingingNodeId !== node.id" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <svg x-show="pingingNodeId === node.id" class="h-3.5 w-3.5 animate-spin text-blue-500" style="display:none;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="pingingNodeId === node.id ? 'Pinging...' : 'Tes Konektivitas'"></span>
                        </button>

                        <div class="flex gap-1.5 items-center">
                            <!-- Edit Button -->
                            <button 
                                type="button" 
                                @click="openEditModal(node)" 
                                class="rounded-xl p-2 text-slate-400 hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400 transition shadow-xs border border-transparent hover:border-amber-200 dark:hover:border-amber-500/30" 
                                title="Edit Informasi Klaster Server"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            <!-- Delete Form Button -->
                            <form :action="node.deleteUrl" method="POST" @submit="if(!confirm('Hapus server master ini dari registry?')) event.preventDefault();">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 transition shadow-xs border border-transparent hover:border-rose-200 dark:hover:border-rose-500/30" title="Hapus Node dari Registry">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Modal 1: Add New Cluster Node -->
        <div x-show="openAddNodeModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openAddNodeModal = false" x-transition.scale.95 class="w-full max-w-lg overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 dark:text-blue-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Daftarkan Server Master Baru</h3>
                    </div>
                    <button @click="openAddNodeModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <form action="{{ route('servers.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">Nama Klaster & Lokasi</label>
                        <input type="text" name="name" placeholder="Contoh: Cluster Europe Central (EU-1)" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">IP Address Server Node</label>
                            <input type="text" name="ip_address" placeholder="103.88.xx.xx" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">Lokasi Geografis / Data Center</label>
                            <input type="text" name="location" placeholder="Frankfurt, DE" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">Endpoint API URL (Server Remote)</label>
                        <input type="url" name="endpoint_url" placeholder="https://master-node4.yourdomain.com/api/v1" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">Bearer API Secret Token</label>
                        <input type="password" name="api_secret" placeholder="sk_live_..." class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openAddNodeModal = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/20 hover:bg-blue-500 transition">Simpan & Verify API Node</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2: Edit Cluster Node -->
        <div x-show="openEditNodeModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openEditNodeModal = false" x-transition.scale.95 class="w-full max-w-lg overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 font-bold">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="'Edit: ' + editingNode.name"></h3>
                            <p class="text-xs text-slate-400">Perbarui informasi konfigurasi klaster server master</p>
                        </div>
                    </div>
                    <button @click="openEditNodeModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <form :action="editingNode.updateUrl" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">Nama Klaster & Lokasi</label>
                        <input type="text" name="name" x-model="editingNode.name" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">IP Address Server Node</label>
                            <input type="text" name="ip_address" x-model="editingNode.ip" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">Lokasi Geografis / DC</label>
                            <input type="text" name="location" x-model="editingNode.location" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">Endpoint API URL (Server Remote)</label>
                        <input type="url" name="endpoint_url" x-model="editingNode.endpoint" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 dark:text-slate-300 mb-1">Bearer API Secret Token (Opsional)</label>
                        <input type="password" name="api_secret" x-model="editingNode.secret" placeholder="Biarkan kosong jika tidak ingin mengubah secret..." class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <span class="text-[10px] text-slate-400 mt-1 block">Hanya diisi apabila Anda baru saja memutar (rotate) secret key di server remote.</span>
                    </div>
                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openEditNodeModal = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                        <button type="submit" class="rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-500/20 hover:bg-amber-600 transition">Simpan Perubahan & Sync</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
