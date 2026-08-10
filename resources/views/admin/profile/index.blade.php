<x-layouts.admin title="Profil Akun">
    <div class="px-4 py-8 sm:px-6 lg:px-8" x-data="{ activeTab: localStorage.getItem('profileTab') || 'informasi' }" x-init="$watch('activeTab', value => localStorage.setItem('profileTab', value))">
        
        <!-- Header Section -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Profil Akun</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola pengaturan profil dan preferensi keamanan Anda.</p>
            </div>
            <div class="h-16 w-16 overflow-hidden rounded-full ring-4 ring-white shadow-lg dark:ring-slate-800">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ urlencode($user->name) }}" alt="Avatar" class="h-full w-full object-cover bg-indigo-50 dark:bg-indigo-900/20">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            
            <!-- Left Column: Navigation / Info -->
            <div class="md:col-span-1">
                <nav class="flex flex-col gap-2">
                    <button @click="activeTab = 'informasi'" :class="activeTab === 'informasi' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200'" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition text-left">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Informasi Dasar
                    </button>
                    <button @click="activeTab = 'keamanan'" :class="activeTab === 'keamanan' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200'" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition text-left">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Keamanan
                    </button>
                </nav>
            </div>

            <!-- Right Column: Forms -->
            <div class="md:col-span-2 space-y-8">
                
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Informasi Dasar Card -->
                    <div x-show="activeTab === 'informasi'" x-transition.opacity id="informasi-dasar" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" style="display: none;">
                        <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Informasi Dasar</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Perbarui data diri dan alamat email yang terhubung dengan akun ini.</p>
                        </div>
                        
                        <div class="px-6 py-6 space-y-5">
                            
                            <!-- Nama Lengkap -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="block w-full rounded-xl border-slate-300 py-2.5 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500" required>
                                </div>
                            </div>
                            
                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Username</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-slate-400 sm:text-sm">@</span>
                                    </div>
                                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" class="block w-full rounded-xl border-slate-300 py-2.5 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Alamat Email</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    </div>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="block w-full rounded-xl border-slate-300 py-2.5 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500" required>
                                </div>
                            </div>

                        </div>
                        <div class="bg-slate-50 px-6 py-4 dark:bg-slate-800/50 flex justify-end border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all dark:focus:ring-offset-slate-900">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>

                    <!-- Keamanan Card -->
                    <div x-show="activeTab === 'keamanan'" x-transition.opacity id="keamanan" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" style="display: none;">
                        <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Keamanan & Kata Sandi</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kosongkan jika Anda tidak ingin mengubah kata sandi.</p>
                        </div>
                        
                        <div class="px-6 py-6 space-y-5" x-data="{ showPassword: false }">
                            
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Kata Sandi Saat Ini</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    </div>
                                    <input :type="showPassword ? 'text' : 'password'" name="current_password" id="current_password" class="block w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500">
                                </div>
                                @error('current_password')
                                    <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Kata Sandi Baru</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    </div>
                                    <input :type="showPassword ? 'text' : 'password'" name="password" id="password" class="block w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                                        <!-- Eye open icon (shows when password is hidden) -->
                                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <!-- Eye closed icon (shows when password is shown) -->
                                        <svg x-show="showPassword" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Confirm New Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Konfirmasi Kata Sandi Baru</label>
                                <div class="relative mt-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    </div>
                                    <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" class="block w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-indigo-500">
                                </div>
                                @error('password_confirmation')
                                    <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                        <div class="bg-slate-50 px-6 py-4 dark:bg-slate-800/50 flex justify-end border-t border-slate-100 dark:border-slate-800">
                            <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all dark:focus:ring-offset-slate-900">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-layouts.admin>
