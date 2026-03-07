<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IoT GPS Tracker')</title>

    <!-- Google Fonts - Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-primary': '#f8fafc',
                        'bg-secondary': '#ffffff',
                        'bg-tertiary': '#f1f5f9',
                        'text-primary': '#0f172a',
                        'text-secondary': '#64748b',
                        'accent': '#3b82f6',
                        'accent-light': '#eff6ff',
                        'success': '#22c55e',
                        'success-light': '#f0fdf4',
                        'warning': '#f59e0b',
                        'warning-light': '#fffbeb',
                        'danger': '#ef4444',
                        'danger-light': '#fef2f2',
                        'purple': '#8b5cf6',
                        'purple-light': '#f5f3ff',
                        'border-color': '#e2e8f0',
                    },
                    spacing: {
                        'sidebar': '260px',
                        'header': '60px',
                        'footer': '48px',
                    },
                    fontFamily: {
                        sans: ['Outfit', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                    },
                    borderRadius: {
                        'custom': '2px',
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .sidebar-overlay {
                @apply hidden fixed inset-0 bg-black/40 z-[999];
            }
            .sidebar-overlay.active {
                @apply block;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans bg-bg-primary text-text-primary min-h-screen overflow-x-hidden">
    <script>
        // Mobile sidebar toggle - defined early so onclick handlers work
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }
    </script>

    <!-- Sidebar Overlay (for mobile) -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Header -->
    @include('layouts.header')

    <!-- Main Content -->
    <main class="ml-0 md:ml-sidebar mt-header min-h-[calc(100vh-108px)] p-4 md:p-6 pb-[calc(48px+16px)] md:pb-[calc(48px+24px)] bg-bg-primary transition-all duration-300">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.footer')

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Update time
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Update device count
        fetch('/api/devices')
            .then(res => res.json())
            .then(data => {
                document.getElementById('device-count').textContent = data.length;
            })
            .catch(() => {});

        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }
    </script>

    @stack('scripts')
</body>

</html>