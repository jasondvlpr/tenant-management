<x-layouts.admin title="Manajemen Berita & Informasi">
    <div x-data="{
        openModalCreate: false,
        openModalEdit: false,
        openModalDelete: false,
        activeNews: null,
        newsList: {{ $news->map(function($n) {
            return [
                'id' => $n->id,
                'title' => $n->title,
                'content' => $n->content,
                'status' => $n->is_active ? 'Active' : 'Inactive',
                'is_active' => $n->is_active,
                'date' => $n->created_at->format('d M Y, H:i'),
                'updateUrl' => route('news.update', $n->id),
                'deleteUrl' => route('news.destroy', $n->id)
            ];
        })->toJson() }},
        openEditManager(newsItem) {
            this.activeNews = newsItem;
            this.openModalEdit = true;
        },
        openDeleteManager(newsItem) {
            this.activeNews = newsItem;
            this.openModalDelete = true;
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

        <!-- Page Header -->
        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                    <span class="inline-block h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                    Sistem Informasi Terpusat
                </div>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white md:text-3xl">
                    Manajemen Berita & Pengumuman
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Buat dan kelola informasi publik yang nantinya bisa dibaca via API JSON secara langsung.
                </p>
            </div>
            
            <div class="flex flex-wrap gap-2.5">
                <button 
                    @click="openModalCreate = true" 
                    class="group relative flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition duration-200"
                >
                    <svg class="h-5 w-5 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tulis Berita Baru</span>
                </button>
            </div>
        </div>

        <!-- News Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-slate-900 mb-10">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/70 text-xs uppercase text-slate-500 dark:border-slate-800/80 dark:bg-slate-950/50 dark:text-slate-400 font-semibold tracking-wider">
                            <th class="px-6 py-4 border border-slate-200/80 dark:border-slate-800/60 w-1/3">Judul Berita</th>
                            <th class="px-6 py-4 border border-slate-200/80 dark:border-slate-800/60">Tanggal Publikasi</th>
                            <th class="px-6 py-4 border border-slate-200/80 dark:border-slate-800/60">Status</th>
                            <th class="px-6 py-4 text-right border border-slate-200/80 dark:border-slate-800/60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <template x-if="newsList.length === 0">
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center border border-slate-200/80 dark:border-slate-800/60">
                                    <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                        <svg class="h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                                        <p class="text-sm font-semibold">Belum ada berita</p>
                                        <p class="text-xs mt-1">Silakan klik "Tulis Berita Baru" untuk memulai.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-for="item in newsList" :key="item.id">
                            <tr class="group transition duration-150 hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                <td class="px-6 py-4 border border-slate-200/80 dark:border-slate-800/60">
                                    <span class="block font-bold text-slate-900 dark:text-white" x-text="item.title"></span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 truncate mt-1 w-64" x-text="item.content"></span>
                                </td>

                                <td class="px-6 py-4 border border-slate-200/80 dark:border-slate-800/60">
                                    <span class="text-xs text-slate-600 dark:text-slate-300 font-mono" x-text="item.date"></span>
                                </td>

                                <td class="px-6 py-4 border border-slate-200/80 dark:border-slate-800/60">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                        :class="item.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800/80 dark:text-slate-300'">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="item.is_active ? 'bg-emerald-500' : 'bg-slate-500'"></span>
                                        <span x-text="item.status"></span>
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right border border-slate-200/80 dark:border-slate-800/60">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEditManager(item)" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-indigo-600 dark:hover:bg-slate-800 dark:hover:text-indigo-400 transition" title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        <button @click="openDeleteManager(item)" class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400 transition" title="Hapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Create News -->
        <div x-show="openModalCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openModalCreate = false" x-transition.scale.95 class="w-full max-w-xl overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-500/30">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tulis Berita Baru</h3>
                            <p class="text-xs text-slate-500">Berita aktif akan dipublikasikan ke API Endpoint.</p>
                        </div>
                    </div>
                    <button @click="openModalCreate = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <form action="{{ route('news.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Judul Berita <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" placeholder="Contoh: Maintenance Terjadwal API" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Konten / Isi Berita <span class="text-rose-500">*</span></label>
                        <textarea name="content" rows="4" placeholder="Tuliskan isi berita atau pengumuman secara lengkap di sini..." class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white focus:ring-blue-500 focus:border-blue-500" required></textarea>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 dark:bg-slate-800/50 dark:border-slate-700/50 flex items-start gap-3.5 mt-2">
                        <input type="checkbox" name="is_active" value="1" id="news_active" class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" checked>
                        <label for="news_active" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <strong class="font-bold text-slate-900 dark:text-white block mb-0.5">Status Publikasi (Aktif)</strong>
                            Centang untuk mempublikasikan berita ini agar langsung bisa diakses melalui URL API endpoint.
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openModalCreate = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/20 hover:bg-blue-500 transition">Publikasikan Berita</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit News -->
        <div x-show="openModalEdit" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openModalEdit = false" x-transition.scale.95 class="w-full max-w-xl overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 shadow-sm border border-amber-500/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Berita</h3>
                            <p class="text-xs text-slate-500">Perbarui informasi berita ini.</p>
                        </div>
                    </div>
                    <button @click="openModalEdit = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <form :action="activeNews?.updateUrl" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Judul Berita <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" :value="activeNews?.title" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white focus:ring-amber-500 focus:border-amber-500" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300 mb-1">Konten / Isi Berita <span class="text-rose-500">*</span></label>
                        <textarea name="content" rows="4" x-text="activeNews?.content" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-white focus:ring-amber-500 focus:border-amber-500" required></textarea>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 dark:bg-slate-800/50 dark:border-slate-700/50 flex items-start gap-3.5 mt-2">
                        <input type="checkbox" name="is_active" value="1" id="edit_news_active" class="mt-1 h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500" :checked="activeNews?.is_active">
                        <label for="edit_news_active" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                            <strong class="font-bold text-slate-900 dark:text-white block mb-0.5">Status Publikasi (Aktif)</strong>
                            Centang untuk menjaga berita ini tetap aktif pada sistem.
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openModalEdit = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                        <button type="submit" class="rounded-xl bg-amber-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-500/20 hover:bg-amber-500 transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Delete Confirmation -->
        <div x-show="openModalDelete" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs" style="display: none;">
            <div @click.outside="openModalDelete = false" x-transition.scale.95 class="w-full max-w-md overflow-hidden rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 mb-4">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Hapus Berita Ini?</h3>
                <p class="text-xs text-slate-500 mt-1 mb-6">
                    Anda yakin ingin menghapus permanen berita <strong class="text-slate-800 dark:text-white" x-text="activeNews?.title"></strong>? Data yang dihapus tidak dapat dikembalikan.
                </p>

                <form :action="activeNews?.deleteUrl" method="POST" class="flex justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="openModalDelete = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">Batal</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/20 hover:bg-rose-500 transition">Ya, Hapus Permanen</button>
                </form>
            </div>
        </div>

    </div>
</x-layouts.admin>
