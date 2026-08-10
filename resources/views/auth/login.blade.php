<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otorisasi Sistem | Super Central Command Hub</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .glow-effect {
            box-shadow: 0 0 50px -10px rgba(99, 102, 241, 0.3);
        }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex items-center justify-center relative overflow-hidden bg-grid-pattern selection:bg-indigo-500 selection:text-white">
    
    <!-- Background Decorative Gradients -->
    <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-gradient-to-tr from-indigo-600/30 to-purple-600/30 blur-3xl pointer-events-none animate-pulse"></div>
    <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-gradient-to-tl from-blue-600/30 to-emerald-600/20 blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md px-6 py-12 relative z-10">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center h-16 w-16 rounded-3xl bg-gradient-to-tr from-indigo-600 via-blue-600 to-purple-600 text-white shadow-xl shadow-indigo-500/30 mb-4 transform hover:scale-105 transition-transform duration-300">
                <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <div class="flex items-center justify-center gap-2 text-xs font-bold uppercase tracking-widest text-indigo-400 mb-1">
                <span class="h-2 w-2 rounded-full bg-indigo-500 animate-ping"></span>
                Restricted Admin Gateway
            </div>
            <h1 class="text-3xl font-black tracking-tight text-white">
                Tenant Command Hub
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Masuk untuk mengendalikan server master & Cloudflare DNS
            </p>
        </div>

        <!-- Login Card Wrapper -->
        <div class="rounded-3xl border border-slate-800/80 bg-slate-900/80 backdrop-blur-xl p-8 shadow-2xl glow-effect relative overflow-hidden">
            <!-- Subtle accent top strip -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-amber-500"></div>

            @if(session('status'))
            <div class="mb-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 p-4 text-xs font-semibold text-emerald-300 flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 rounded-2xl bg-rose-500/10 border border-rose-500/30 p-4 text-xs font-semibold text-rose-300 flex items-start gap-3">
                <svg class="h-5 w-5 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div class="leading-relaxed">{{ $errors->first() }}</div>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Username atau Email Kredensial</label>
                    <div class="relative">
                        <input type="text" name="login" value="{{ old('login', 'admin') }}" required autofocus placeholder="admin atau admin@master-hub.com" class="w-full rounded-2xl border border-slate-700/80 bg-slate-950/70 py-3.5 pl-11 pr-4 text-sm font-semibold text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                        <svg class="absolute left-3.5 top-3.5 h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Kata Sandi (Password)</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" name="password" value="password" required placeholder="••••••••" class="w-full rounded-2xl border border-slate-700/80 bg-slate-950/70 py-3.5 pl-11 pr-11 text-sm font-semibold text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                        <svg class="absolute left-3.5 top-3.5 h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        <button type="button" @click="show = !show" class="absolute right-3.5 top-3.5 text-slate-500 hover:text-slate-300 transition focus:outline-none">
                            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z" /></svg>
                            <svg x-show="show" class="h-5 w-5" style="display:none;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.574-2.827m4.992-4.993A9.954 9.954 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.99 9.99 0 01-2.037 3.364M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Verifikasi Keamanan</label>
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="w-full sm:w-1/2 shrink-0 flex justify-center bg-slate-800/80 rounded-2xl border border-slate-700/80 p-2 overflow-hidden shadow-inner">
                            <img src="{{ captcha_src('flat') }}" alt="Captcha" class="w-full h-auto rounded-lg cursor-pointer hover:opacity-80 transition mix-blend-screen" onclick="this.src='{{ captcha_src('flat') }}&_='+Math.random()" title="Klik untuk mengubah kode Captcha">
                        </div>
                        <div class="w-full sm:w-1/2">
                            <input type="text" name="captcha" required placeholder="Ketik Captcha" class="w-full rounded-2xl border border-slate-700/80 bg-slate-950/70 py-3.5 px-4 text-center text-sm font-bold text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 text-center mt-1">Klik gambar jika kode sulit terbaca.</p>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                        <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500/30 h-4 w-4" checked>
                        <span>Ingat Sesi Saya</span>
                    </label>
                    <span class="text-slate-500 font-mono">256-Bit SSL Encrypted</span>
                </div>

                <button type="submit" class="w-full mt-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-600 py-4 font-extrabold text-sm text-white shadow-xl shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:from-indigo-500 transform active:scale-[0.98] transition-all duration-200 uppercase tracking-wider flex items-center justify-center gap-2">
                    <span>Otorisasi Masuk & Buka Console</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-800/80 text-center">
                <div class="text-[11px] text-slate-500 font-mono space-y-1">
                    <div>Kredensial Bawaan Sistem (Seeded MySQL):</div>
                    <div class="text-indigo-400">Username: <strong class="text-white">admin</strong> <span class="text-slate-500">|</span> Email: <strong class="text-white">admin@master-hub.com</strong></div>
                    <div>Kata Sandi (Password): <strong class="text-slate-300">password</strong></div>
                </div>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-slate-600 font-mono">
            &copy; {{ date('Y') }} Distributed Central Engine OS • Laravel MySQL Driver
        </p>
    </div>
</body>
</html>
