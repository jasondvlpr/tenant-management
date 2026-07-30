<header class="sticky top-0 z-30 flex w-full border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/80 transition-colors duration-300">
    <div class="flex flex-grow items-center justify-between px-4 py-3 md:px-6 2xl:px-10">
        <!-- Left Section: Hamburger & Search -->
        <div class="flex items-center gap-4 lg:gap-6">
            <button 
                @click="sidebarOpen = !sidebarOpen" 
                class="block rounded-lg p-1.5 text-slate-600 hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-slate-800"
                aria-label="Toggle Sidebar"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </button>
            
            <!-- Quick Search Bar -->
            <div class="hidden sm:block relative w-64 md:w-96">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4 w-4 text-slate-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input 
                    type="text" 
                    placeholder="Cari tenant, subdomain, atau ID tagihan... (⌘K)" 
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-indigo-500/60"
                >
                <span class="absolute right-3 top-1/2 -translate-y-1/2 rounded bg-slate-200/60 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">ESC</span>
            </div>
        </div>

        <!-- Right Section: Actions & Profile -->
        <div class="flex items-center gap-3 md:gap-4">
            <!-- Dark Mode Toggle Button -->
            <button 
                @click="darkMode = !darkMode" 
                class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/80 bg-slate-100/60 text-slate-600 transition hover:bg-slate-200/60 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300 dark:hover:bg-slate-800"
                title="Toggle Dark/Light Mode"
            >
                <!-- Sun icon -->
                <svg x-show="darkMode" class="h-5 w-5 text-amber-400 transition duration-300" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <!-- Moon icon -->
                <svg x-show="!darkMode" class="h-5 w-5 text-indigo-500 transition duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
            </button>

            <!-- Notification Button with badge -->
            <button class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/80 bg-slate-100/60 text-slate-600 transition hover:bg-slate-200/60 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300 dark:hover:bg-slate-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                <span class="absolute right-2 top-2 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                </span>
            </button>

            <!-- User Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button 
                    @click="open = !open" 
                    @click.outside="open = false"
                    class="flex items-center gap-3 rounded-xl py-1 pl-2 pr-3 transition hover:bg-slate-100 dark:hover:bg-slate-800/60"
                >
                    <div class="relative h-9 w-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 p-[2px]">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" alt="Admin" class="h-full w-full rounded-full bg-slate-900 object-cover">
                        <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span>
                    </div>
                    <div class="hidden text-left sm:block">
                        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-200">{{ auth()->user()->name ?? 'Administrator' }}</span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">Super Admin</span>
                    </div>
                    <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>

                <!-- Dropdown menu -->
                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    class="absolute right-0 mt-2.5 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-500/10 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40"
                    style="display: none;"
                >
                    <div class="border-b border-slate-100 px-3 py-2 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                        Masuk sebagai <strong class="text-slate-700 dark:text-slate-300">{{ auth()->user()->email ?? 'admin@master-hub.com' }}</strong>
                    </div>
                    <ul class="py-1 text-sm text-slate-700 dark:text-slate-300">
                        <li>
                            <a href="#" class="flex items-center gap-2.5 rounded-xl px-3 py-2 transition hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Profil Akun
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/cloudflare') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 transition hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
                                Pengaturan Cloudflare
                            </a>
                        </li>
                    </ul>
                    <div class="mt-1 border-t border-slate-100 pt-1 dark:border-slate-800">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                Keluar Sistem (Logout)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
