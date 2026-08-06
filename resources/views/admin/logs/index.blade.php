<x-layouts.admin title="Log Request API & Audit Trail">
    <!-- Alpine JS state for API Logs with DB binding -->
    <div x-data="{
        openPayloadModal: false,
        selectedLog: null,
        filterCluster: 'all',
        filterStatus: 'all',
        logs: {{ $logs->map(function($l) {
            return [
                'id' => $l->id,
                'time' => $l->created_at->format('Y-m-d H:i:s'),
                'method' => $l->method,
                'endpoint' => $l->endpoint,
                'cluster' => $l->cluster_name,
                'tenant' => $l->tenant_name ?? 'System',
                'status' => $l->status_text,
                'statusCode' => $l->status_code,
                'latency' => $l->latency_ms,
                'payload' => $l->request_body ?? '{}',
                'response' => $l->response_body ?? '{}',
            ];
        })->toJson() }},
        viewDetails(item) {
            this.selectedLog = item;
            this.openPayloadModal = true;
        },
        formatJson(str) {
            if (!str) return '';
            try {
                let parsed = typeof str === 'string' ? JSON.parse(str) : str;
                return JSON.stringify(parsed, null, 2);
            } catch(e) {
                return str;
            }
        }
    }">

        <!-- Notification Banner -->
        @if(session('success'))
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-500/30 p-4 text-sm font-semibold text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="event.target.closest('div').remove()" class="text-emerald-600 hover:text-emerald-800"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>
        @endif

        <!-- Page Header -->
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-purple-600"></span>
                    Distributed Telemetry & Audit Trail
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Log Request API & Telemetri
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Rekam jejak real-time lalu lintas komunikasi HTTP REST API dari Central Command Hub ke setiap server master dan Cloudflare.
                </p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('logs.destroy') }}" method="POST" @submit="if(!confirm('Bersihkan semua riwayat log telemetri dari database?')) event.preventDefault();">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 transition">
                        Bersihkan Riwayat Log
                    </button>
                </form>
            </div>
        </div>

        <!-- Telemetry Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Transaksi API</span>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $totalRequests }} <span class="text-xs font-semibold text-purple-500">Logged</span></h3>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Tingkat Keberhasilan</span>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $successRate }} <span class="text-xs font-medium text-slate-400">Success Rate</span></h3>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Rata-rata Latensi</span>
                <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1">32ms <span class="text-xs font-medium text-slate-400">Low Delay</span></h3>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Engine Keamanan</span>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">TLS 1.3 <span class="text-xs font-medium text-emerald-500">Encrypted</span></h3>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 flex flex-col justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-slate-900 sm:flex-row sm:items-center">
            <div class="flex flex-wrap gap-2">
                <button @click="filterCluster = 'all'" :class="filterCluster === 'all' ? 'bg-purple-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'" class="rounded-xl px-4 py-1.5 text-xs transition">
                    Semua Target Klaster
                </button>
                <button @click="filterCluster = 'Cluster'" :class="filterCluster === 'Cluster' ? 'bg-purple-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'" class="rounded-xl px-4 py-1.5 text-xs transition">
                    Master Tenant Nodes
                </button>
                <button @click="filterCluster = 'Cloudflare'" :class="filterCluster === 'Cloudflare' ? 'bg-orange-500 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400'" class="rounded-xl px-4 py-1.5 text-xs transition">
                    Cloudflare API Only
                </button>
            </div>
        </div>

        <!-- API Logs Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-slate-900 mb-10">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/70 text-xs uppercase text-slate-500 dark:border-slate-800/80 dark:bg-slate-950/50 dark:text-slate-400 font-semibold tracking-wider">
                            <th class="px-6 py-4">Waktu & Target Klaster</th>
                            <th class="px-6 py-4">HTTP Method & Endpoint</th>
                            <th class="px-6 py-4">Objek Tenant / Referensi</th>
                            <th class="px-6 py-4">Status & Latensi</th>
                            <th class="px-6 py-4 text-right">Payload Inspeksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono text-xs">
                        <template x-for="item in logs" :key="item.id">
                            <tr 
                                x-show="filterCluster === 'all' || item.cluster.includes(filterCluster)"
                                class="group transition duration-150 hover:bg-slate-50/70 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-6 py-4 font-sans">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block text-sm" x-text="item.cluster"></span>
                                    <span class="text-[11px] text-slate-400 mt-0.5 block" x-text="item.time"></span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-lg px-2 py-0.5 text-[11px] font-bold"
                                            :class="item.method === 'POST' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400'"
                                            x-text="item.method"
                                        ></span>
                                        <span class="text-slate-700 dark:text-slate-300 truncate max-w-xs block" x-text="item.endpoint" :title="item.endpoint"></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-sans font-bold text-slate-800 dark:text-slate-200" x-text="item.tenant"></td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold font-sans"
                                        :class="item.statusCode >= 200 && item.statusCode < 300 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400'"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="item.statusCode >= 200 && item.statusCode < 300 ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                        <span x-text="item.status"></span>
                                    </span>
                                    <span class="block text-[11px] text-slate-400 mt-1" x-text="'Latensi: ' + item.latency"></span>
                                </td>

                                <td class="px-6 py-4 text-right font-sans">
                                    <button @click="viewDetails(item)" class="rounded-xl bg-purple-50 px-3.5 py-1.5 text-xs font-bold text-purple-700 hover:bg-purple-100 dark:bg-purple-500/10 dark:text-purple-400 dark:hover:bg-purple-500/20 transition">
                                        Detail JSON
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal: Detail Payload JSON -->
        <div x-show="openPayloadModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openPayloadModal = false" x-transition.scale.95 class="w-full max-w-3xl overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="selectedLog ? 'Telemetri API: ' + selectedLog.cluster : 'Detail API'"></h3>
                        <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="selectedLog ? selectedLog.method + ' ' + selectedLog.endpoint : ''"></p>
                    </div>
                    <button @click="openPayloadModal = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Request Body (Outbound Payload)</span>
                        <pre class="overflow-x-auto rounded-2xl bg-slate-900 p-4 text-xs font-mono text-emerald-400 max-h-80 border border-slate-800 leading-relaxed" x-text="selectedLog ? formatJson(selectedLog.payload) : ''"></pre>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-500 block mb-1.5">Response Data (Inbound Result)</span>
                        <pre class="overflow-x-auto rounded-2xl bg-slate-900 p-4 text-xs font-mono text-blue-400 max-h-80 border border-slate-800 leading-relaxed" x-text="selectedLog ? formatJson(selectedLog.response) : ''"></pre>
                    </div>
                </div>

                <div class="mt-6 flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button @click="openPayloadModal = false" class="rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-bold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900">Tutup Inspektur</button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
