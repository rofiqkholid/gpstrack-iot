<!-- Header Component -->
<header class="header">
    <div style="display: flex; align-items: center;">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="header-title">@yield('header-title', 'Dashboard')</h1>
    </div>

    <div class="header-actions">
        <div class="header-status">
            <span class="status-dot"></span>
            <span>Sistem Aktif</span>
        </div>
        <div class="header-time" id="current-time"></div>
    </div>
</header>