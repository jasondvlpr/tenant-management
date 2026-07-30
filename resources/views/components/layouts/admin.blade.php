<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: false, sidebarOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tenant Management Dashboard' }} | TailAdmin</title>
    <meta name="description" content="Sistem Manajemen Tenant Berbasis TailAdmin dan Laravel">

    <!-- Alpine.js for interactive dashboard elements and modal toggles -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Smooth Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.6); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Component -->
        <x-admin.sidebar />

        <!-- Main Content Wrapper -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- Header Component -->
            <x-admin.header />

            <!-- Main Content Area -->
            <main class="grow duration-300 ease-in-out">
                <div class="w-full p-4 md:p-6 2xl:p-10">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>
