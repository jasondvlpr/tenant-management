<x-layouts.admin title="Monitor Antrean & Sync Asinkron">
    <!-- Alpine JS state for Queues with DB Binding -->
    <div x-data="{
        workersRunning: true,
        jobs: {{ json_encode($jobs) }},
        restartWorkers() {
            this.workersRunning = false;
            setTimeout(() => {
                this.workersRunning = true;
                window.toast('Worker pool berhasil di-restart dan koneksi antrean telah diperbaharui!');
            }, 1000);
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
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-blue-500 animate-ping"></span>
                    Non-Blocking Asynchronous Engine
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Monitor Antrean & Worker Jobs
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Pantau kinerja worker background yang mengeksekusi pembuatan tenant di server jarak jauh dan sinkronisasi Cloudflare tanpa memblokir UI.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="{{ route('queues.restart') }}" method="POST">
                    @csrf
                    <button 
                        type="submit"
                        class="flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 transition"
                    >
                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <span>Restart Worker Pool</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Worker Pool Status Strip -->
        <div class="mb-8 rounded-3xl border border-slate-200/80 bg-gradient-to-r from-slate-900 to-indigo-950 p-6 text-white shadow-xl dark:border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md border border-white/15">
                        <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-sm font-extrabold uppercase tracking-wider text-emerald-400">4 Worker Processes Online</span>
                        </div>
                        <h3 class="text-lg font-bold mt-0.5">Queue Driver: MySQL Database</h3>
                        <p class="text-xs text-slate-300 mt-0.5">Kapasitas throughput antrean stabil hingga 150 job/menit per server master node.</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 border-t border-white/10 pt-4 md:border-t-0 md:pt-0">
                    <div class="text-right">
                        <span class="text-2xl font-black text-white">0.4s</span>
                        <span class="block text-[10px] uppercase text-slate-400 font-bold">Rata-rata Eksekusi</span>
                    </div>
                    <div class="h-8 w-[1px] bg-white/15"></div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-emerald-400">0</span>
                        <span class="block text-[10px] uppercase text-slate-400 font-bold">Failed Jobs</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jobs Queue Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-slate-900 mb-10">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Riwayat Eksekusi Job (Recent Discarded & Active)</h3>
                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Auto-refresh (Live Sync)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/70 text-xs uppercase text-slate-500 dark:border-slate-800/80 dark:bg-slate-950/50 dark:text-slate-400 font-semibold tracking-wider">
                            <th class="px-6 py-3.5">Job ID & Class Name</th>
                            <th class="px-6 py-3.5">Target Objek / Referensi</th>
                            <th class="px-6 py-3.5">Klaster Tujuan</th>
                            <th class="px-6 py-3.5">Percobaan (Attempts)</th>
                            <th class="px-6 py-3.5 text-right">Status & Durasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono text-xs">
                        <template x-for="job in jobs" :key="job.id">
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block text-sm" x-text="job.name"></span>
                                    <span class="text-[11px] text-slate-400 block mt-0.5" x-text="job.id"></span>
                                </td>
                                <td class="px-6 py-4 font-sans font-bold text-slate-800 dark:text-slate-200" x-text="job.target"></td>
                                <td class="px-6 py-4 font-sans text-xs text-indigo-600 dark:text-indigo-400 font-semibold" x-text="job.cluster"></td>
                                <td class="px-6 py-4 font-sans">
                                    <span class="rounded-md bg-slate-100 dark:bg-slate-800 px-2 py-1 font-mono text-xs font-bold text-slate-700 dark:text-slate-300" x-text="job.attempt + ' / ' + job.maxAttempts"></span>
                                </td>
                                <td class="px-6 py-4 text-right font-sans">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span x-text="job.status"></span>
                                    </span>
                                    <span class="block text-[10px] text-slate-400 font-mono mt-0.5" x-text="job.runtime"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.admin>
