<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: false, sidebarOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tenant Management Dashboard' }} | TailAdmin</title>
    <meta name="description" content="Sistem Manajemen Tenant Berbasis TailAdmin dan Laravel">

    <!-- Alpine.js for interactive dashboard elements and modal toggles -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Toastify JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

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
<script>
    window.toast = function(message, type = 'success') {
        let bgColor = "#10b981"; // success: emerald-500
        if (type === 'error') bgColor = "#ef4444"; // red-500
        else if (type === 'info') bgColor = "#3b82f6"; // blue-500
        else if (type === 'warning') bgColor = "#f59e0b"; // amber-500
        
        Toastify({
            text: message,
            duration: 3000,
            close: true,
            gravity: "top", // top or bottom
            position: "right", // left, center or right
            stopOnFocus: true, // Prevents dismissing of toast on hover
            style: {
                background: bgColor,
                borderRadius: "8px",
                padding: "12px 20px",
                boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)",
                fontWeight: "500",
                fontSize: "14px",
                zIndex: 99999
            },
        }).showToast();
    };

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            window.toast("{{ session('success') }}", 'success');
        @endif

        @if(session('error'))
            window.toast("{{ session('error') }}", 'error');
        @endif
        
        @if($errors->any())
            window.toast("{{ $errors->first() }}", 'error');
        @endif
    });
</script>
</html>
