<x-layouts.admin title="Global Domain & Alias Registry">
    <!-- Alpine JS state for Global Domains with Live DB mapping -->
    <div x-data="{
        openModalAlias: false,
        searchQuery: '',
        filterProxy: 'all',
        domains: {{ $domains->map(function($d) {
            return [
                'id' => $d->id,
                'alias' => $d->alias,
                'tenant' => $d->tenant ? $d->tenant->name : 'Unassociated',
                'tenantDomain' => $d->tenant ? $d->tenant->domain : 'N/A',
                'cluster' => ($d->tenant && $d->tenant->clusterNode) ? $d->tenant->clusterNode->name : 'N/A',
                'proxy' => $d->cf_status,
                'ssl' => $d->ssl,
                'type' => $d->type,
                'deleteUrl' => route('domains.destroy', $d->id)
            ];
        })->toJson() }}
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

        @if($errors->any())
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-500/30 p-4 text-sm font-semibold text-rose-800 dark:bg-rose-500/10 dark:text-rose-400 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ $errors->first() }}</span>
            </div>
            <button @click="event.target.closest('div').remove()" class="text-rose-600 hover:text-rose-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>
        @endif

        <!-- Page Header -->
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                    Central Virtual Hosts & Reverse Proxy
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Repository Domain Alias Global
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola seluruh domain alias dan virtual hosts dari seluruh tenant di satu dasbor, lengkap dengan auto-sync ke Cloudflare DNS.
                </p>
            </div>
            
            <div class="shrink-0">
                <button 
                    @click="openModalAlias = true"
                    class="group relative flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:from-amber-600 transition duration-200"
                >
                    <svg class="h-5 w-5 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    <span>Daftarkan Domain Alias Baru</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="mb-6 flex flex-col justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-slate-900 sm:flex-row sm:items-center">
            <div class="flex flex-wrap gap-2">
                <button @click="filterProxy = 'all'" :class="filterProxy === 'all' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'" class="rounded-xl px-4 py-2 text-xs transition">
                    Semua Domain ({{ $domains->count() }})
                </button>
                <button @click="filterProxy = 'Proxied'" :class="filterProxy === 'Proxied' ? 'bg-orange-500 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs transition">
                    <span class="h-2 w-2 rounded-full bg-orange-200"></span> Proxied Orange Cloud ({{ $domains->filter(fn($d) => str_contains($d->cf_status, 'Orange'))->count() }})
                </button>
                <button @click="filterProxy = 'DNS Only'" :class="filterProxy === 'DNS Only' ? 'bg-slate-700 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'" class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs transition">
                    <span class="h-2 w-2 rounded-full bg-slate-400"></span> DNS Only Grey Cloud ({{ $domains->filter(fn($d) => !str_contains($d->cf_status, 'Orange'))->count() }})
                </button>
            </div>

            <div class="relative min-w-[280px]">
                <input type="text" x-model="searchQuery" placeholder="Cari nama domain alias atau tenant..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-xs text-slate-800 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>

        <!-- Domain Repository Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-slate-900 mb-10">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/70 text-xs uppercase text-slate-500 dark:border-slate-800/80 dark:bg-slate-950/50 dark:text-slate-400 font-semibold tracking-wider">
                            <th class="px-6 py-4">Domain Alias & Tipe Record</th>
                            <th class="px-6 py-4">Target Tenant & Domain Utama</th>
                            <th class="px-6 py-4">Status Cloudflare DNS</th>
                            <th class="px-6 py-4">Keamanan SSL/TLS</th>
                            <th class="px-6 py-4 text-right">Aksi & Kontrol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <template x-for="item in domains" :key="item.id">
                            <tr 
                                x-show="(filterProxy === 'all' || item.proxy.includes(filterProxy)) && (searchQuery === '' || item.alias.toLowerCase().includes(searchQuery.toLowerCase()) || item.tenant.toLowerCase().includes(searchQuery.toLowerCase()))"
                                class="group transition duration-150 hover:bg-slate-50/70 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 font-bold border border-amber-500/20 text-xs font-mono" x-text="item.type.substring(0,2)"></div>
                                        <div>
                                            <a :href="'http://' + item.alias" target="_blank" class="font-bold font-mono text-slate-900 dark:text-white hover:text-amber-500 flex items-center gap-1">
                                                <span x-text="item.alias"></span>
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </a>
                                            <span class="block text-[11px] text-slate-400 mt-0.5 font-sans" x-text="'Tipe: ' + item.type + ' (Auto VHost)'"></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block" x-text="item.tenant"></span>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-mono mt-0.5 block" x-text="'➔ ' + item.tenantDomain"></span>
                                    <span class="text-[10px] text-slate-400 block mt-1" x-text="'Hosted on ' + item.cluster"></span>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold"
                                        :class="item.proxy.includes('Orange') ? 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400 border border-orange-500/20' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700'"
                                    >
                                        <span class="h-2 w-2 rounded-full" :class="item.proxy.includes('Orange') ? 'bg-orange-500 shadow-sm shadow-orange-500/50' : 'bg-slate-400'"></span>
                                        <span x-text="item.proxy"></span>
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                        <span x-text="item.ssl"></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form :action="item.deleteUrl" method="POST" @submit="if(!confirm('Hapus domain alias ini dari Cloudflare dan Virtual Host?')) event.preventDefault();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 transition" title="Hapus Domain Alias">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal: Daftar Domain Alias Baru -->
        <div x-show="openModalAlias" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openModalAlias = false" x-transition.scale.95 class="w-full max-w-lg overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white shadow-md shadow-amber-500/30">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Daftarkan Domain Alias Baru</h3>
                            <p class="text-xs text-slate-500">Hubungkan domain kustom ke tenant master pilihan</p>
                        </div>
                    </div>
                    <button @click="openModalAlias = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <form action="{{ route('domains.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Pilih Target Tenant Utama</label>
                        <select name="tenant_id" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                            @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->domain }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Nama Domain Alias / Host Baru</label>
                        <input type="text" name="alias" placeholder="Contoh: toko.domainkustom.id" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-mono text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Tipe DNS Record</label>
                            <select name="type" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="CNAME">CNAME Record</option>
                                <option value="A Record">A Record (Direct IP)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Mode Cloudflare Proxy</label>
                            <select name="cf_proxy" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="Proxied (Orange Cloud)">Proxied (Orange Cloud)</option>
                                <option value="DNS Only (Grey Cloud)">DNS Only (Grey Cloud)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openModalAlias = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                        <button type="submit" class="rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-500/20 hover:bg-amber-600 transition">Daftarkan & Sync DNS</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.admin>
