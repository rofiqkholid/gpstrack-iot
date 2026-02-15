<!-- Sidebar Component -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ url('/map') }}" class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="sidebar-logo-text">GPS<span>Track</span></div>
        </a>
        <!-- Close button for mobile -->
        <button class="sidebar-close-btn" onclick="toggleSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        {{-- Section: Monitoring --}}
        <div class="nav-section-title">Monitoring</div>

        <a href="{{ url('/map') }}" class="nav-item {{ request()->is('map') ? 'active' : '' }}">
            <span class="nav-item-icon"><i class="fas fa-map-marked-alt"></i></span>
            <span class="nav-item-text">Live Map</span>
        </a>

        {{-- Collapsible: Perangkat --}}
        <div class="nav-group {{ request()->is('devices') || request()->is('history*') ? 'open' : '' }}">
            <button class="nav-group-toggle" onclick="toggleNavGroup(this)">
                <span class="nav-item-icon"><i class="fas fa-microchip"></i></span>
                <span class="nav-item-text">Perangkat</span>
                <span class="nav-item-badge" id="device-count">0</span>
                <span class="nav-group-chevron"><i class="fas fa-chevron-down"></i></span>
            </button>
            <div class="nav-group-children">
                <a href="{{ url('/devices') }}" class="nav-child-item {{ request()->is('devices') ? 'active' : '' }}">
                    <span class="nav-child-dot"></span>
                    Daftar Perangkat
                </a>
                <a href="{{ url('/history') }}" class="nav-child-item {{ request()->is('history*') ? 'active' : '' }}">
                    <span class="nav-child-dot"></span>
                    Riwayat Lokasi
                </a>
            </div>
        </div>

        {{-- Section: Service --}}
        <div class="nav-section-title">Service Kendaraan</div>

        {{-- Collapsible: Kendaraan --}}
        <div class="nav-group {{ request()->is('vehicles*') ? 'open' : '' }}">
            <button class="nav-group-toggle" onclick="toggleNavGroup(this)">
                <span class="nav-item-icon"><i class="fas fa-car"></i></span>
                <span class="nav-item-text">Kendaraan</span>
                <span class="nav-group-chevron"><i class="fas fa-chevron-down"></i></span>
            </button>
            <div class="nav-group-children">
                <a href="{{ url('/vehicles') }}" class="nav-child-item {{ request()->is('vehicles') ? 'active' : '' }}">
                    <span class="nav-child-dot"></span>
                    Daftar Kendaraan
                </a>
                <a href="{{ url('/vehicles/create') }}" class="nav-child-item {{ request()->is('vehicles/create') ? 'active' : '' }}">
                    <span class="nav-child-dot"></span>
                    Tambah Kendaraan
                </a>
            </div>
        </div>

        {{-- Notifikasi Service --}}
        <a href="{{ url('/service/notifications') }}" class="nav-item {{ request()->is('service/notifications') ? 'active' : '' }}">
            <span class="nav-item-icon"><i class="fas fa-bell"></i></span>
            <span class="nav-item-text">Notifikasi Service</span>
            @php
            $totalAlerts = 0;
            foreach(\App\Models\Vehicle::all() as $v) { $totalAlerts += $v->getUrgentCount(); }
            @endphp
            @if($totalAlerts > 0)
            <span class="nav-item-badge danger">{{ $totalAlerts }}</span>
            @endif
        </a>

        {{-- Section: Pengaturan --}}
        <div class="nav-section-title">Pengaturan</div>

        <a href="#" class="nav-item">
            <span class="nav-item-icon"><i class="fas fa-cog"></i></span>
            <span class="nav-item-text">Konfigurasi</span>
        </a>
    </nav>
</aside>

<script>
    function toggleNavGroup(btn) {
        const group = btn.closest('.nav-group');
        group.classList.toggle('open');
    }
</script>