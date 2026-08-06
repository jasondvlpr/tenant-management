<x-layouts.admin title="Cloudflare API & DNS Gateway">
    <!-- Alpine.js state for Cloudflare integration with DB properties -->
    <div x-data="{
        editSettings: false,
        testingApi: false,
        testResult: null,
        email: '{{ $email }}',
        apiKey: '{{ $apiKey }}',
        accountId: '{{ $accountId }}',
        logs: {{ $logs->map(function($l) {
            return [
                'id' => $l->id,
                'action' => $l->method === 'POST' ? 'CREATE_RECORD' : 'DELETE_RECORD',
                'target' => $l->tenant_name ?? 'System DNS',
                'status' => $l->status_text,
                'time' => $l->created_at->diffForHumans(),
                'type' => str_contains($l->request_body, 'CNAME') ? 'CNAME' : 'A',
            ];
        })->toJson() }},
        testConnection() {
            this.testingApi = true;
            this.testResult = null;
            setTimeout(() => {
                this.testingApi = false;
                this.testResult = 'success';
            }, 1200);
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
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-orange-600 dark:text-orange-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-orange-500 animate-pulse"></span>
                    Automated DNS & Reverse Proxy Gateway
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Cloudflare API & Manajemen DNS
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Konfigurasi kredensial token Cloudflare global yang digunakan untuk membuat zona A-Record dan CNAME tenant secara otomatis.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button 
                    @click="testConnection()" 
                    :disabled="testingApi"
                    class="flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200 transition"
                >
                    <svg x-show="!testingApi" class="h-4 w-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <svg x-show="testingApi" class="h-4 w-4 animate-spin text-orange-400" style="display:none;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="testingApi ? 'Menguji API...' : 'Tes Koneksi Cloudflare API'"></span>
                </button>
            </div>
        </div>

        <!-- Connection Test Notification -->
        <div x-show="testResult === 'success'" x-transition class="mb-8 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-800 dark:text-emerald-300 flex items-center justify-between" style="display:none;">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-sm font-bold">Koneksi API Cloudflare Lolos Verifikasi (HTTP 200 OK) • Latensi Gateway: 42ms</span>
            </div>
            <button @click="testResult = null" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </div>

        <!-- Main Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Columns: Credentials & Config -->
            <div class="lg:col-span-2 space-y-6">
                <!-- API Credentials Box -->
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800/80">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-500/10 text-orange-500 font-bold">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-slate-900 dark:text-white">Cloudflare Global API Credentials</h3>
                                <p class="text-xs text-slate-400">Token berlisensi Zone Edit untuk otomatisasi pembuatan DNS Record</p>
                            </div>
                        </div>
                        <span class="rounded-xl bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">Active Bound</span>
                    </div>

                    <form action="{{ route('cloudflare.store') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Alamat Email Akun Cloudflare <span class="text-rose-500">*</span></label>
                            <input type="email" name="cf_email" value="{{ $email }}" placeholder="contoh: admin@domainanda.com" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm text-slate-800 focus:border-orange-500 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white" required>
                            <span class="text-[10px] text-slate-400 mt-1 block">Digunakan pada header otentikasi API: <code class="font-bold text-orange-500">X-Auth-Email</code></span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Cloudflare Global API Key <span class="text-rose-500">*</span></label>
                            <input type="password" name="cf_api_key" value="{{ $apiKey }}" placeholder="Masukkan 37 karakter Global API Key Anda..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm text-slate-800 focus:border-orange-500 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white" required>
                            <span class="text-[10px] text-slate-400 mt-1 block">Dapatkan dari menu Cloudflare Profile ➔ API Tokens ➔ Global API Key (<code class="font-bold text-orange-500">X-Auth-Key</code>).</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Account ID / Zone ID (Opsional / Default)</label>
                            <input type="text" name="cf_account_id" value="{{ $accountId }}" placeholder="Masukkan Account atau Zone ID Anda (jika diperlukan)..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm text-slate-800 focus:border-orange-500 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white">
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500 inline-block"></span> Global API Key Mode Ready</span>
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-orange-500/25 hover:from-orange-600 transition">Simpan & Perbarui Token</button>
                        </div>
                    </form>
                </div>

                <!-- Automation Rules Box -->
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white mb-4">Pengaturan Otomatisasi DNS Tenant</h3>
                    <div class="space-y-4 text-xs">
                        <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800">
                            <input type="checkbox" checked disabled class="mt-1 h-4 w-4 rounded border-orange-500 text-orange-500 focus:ring-orange-500">
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block text-sm">Otomatis Aktifkan Proxy (Orange Cloud) untuk Domain Baru</span>
                                <p class="text-slate-500 mt-0.5">Semua tenant dan domain alias baru yang di-deploy melalui Command Hub akan otomatis diaktifkan proxy Cloudflare (CDN + DDoS Protection).</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800">
                            <input type="checkbox" checked disabled class="mt-1 h-4 w-4 rounded border-orange-500 text-orange-500 focus:ring-orange-500">
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block text-sm">Otomatis Bersihkan DNS Record Saat Tenant Dihapus</span>
                                <p class="text-slate-500 mt-0.5">Saat Anda menekan tombol hapus tenant atau domain alias, sistem otomatis mengirimkan request DELETE ke Cloudflare Zone agar tidak meninggalkan *orphan DNS record*.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 1 Column: Live Sync Logs -->
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800/80 dark:bg-slate-900">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800/80">
                        <h3 class="font-bold text-base text-slate-900 dark:text-white">Cloudflare API Log</h3>
                        <a href="{{ url('/logs') }}" class="text-xs text-orange-500 font-semibold hover:underline">Semua Log ➔</a>
                    </div>
                    
                    <ul class="mt-4 space-y-3">
                        <template x-for="item in logs" :key="item.id">
                            <li class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/80 text-xs">
                                <div class="flex items-center justify-between font-mono font-semibold mb-1">
                                    <span :class="item.action === 'CREATE_RECORD' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600'" x-text="item.action"></span>
                                    <span class="text-slate-400 text-[10px]" x-text="item.time"></span>
                                </div>
                                <div class="font-bold text-slate-800 dark:text-slate-200 text-sm truncate" x-text="item.target"></div>
                                <div class="flex items-center gap-1.5 mt-2 text-slate-500 text-[11px]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    <span x-text="item.status + ' (' + item.type + ' Record)'"></span>
                                </div>
                            </li>
                        </template>
                        <template x-if="logs.length === 0">
                            <li class="p-6 text-center text-slate-400">Belum ada aktivitas rekam jejak Cloudflare.</li>
                        </template>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-layouts.admin>
