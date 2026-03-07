<!-- Header Component -->
<header class="fixed top-0 left-0 md:left-sidebar right-0 h-header bg-bg-secondary border-b border-border-color z-[999] flex items-center justify-between px-4 md:px-6 transition-all duration-300">
    <div class="flex items-center">
        <button class="inline-flex md:hidden p-2 bg-transparent border-none cursor-pointer text-text-primary rounded-custom hover:bg-bg-tertiary mr-3" onclick="toggleSidebar()">
            <i class="fas fa-bars text-[22px]"></i>
        </button>
        <h1 class="text-[16px] md:text-[18px] font-semibold m-0">@yield('header-title', 'Dashboard')</h1>
    </div>

    <div class="flex items-center gap-2 md:gap-4">
        <div class="flex items-center gap-2 py-1.5 px-3 bg-success-light rounded-custom text-[13px] font-medium text-success">
            <span class="w-2 h-2 bg-success rounded-full animate-pulse"></span>
            <span class="hidden md:inline">Sistem Aktif</span>
        </div>
        <div class="hidden md:block text-[13px] font-medium text-text-secondary bg-bg-tertiary py-1.5 px-3 rounded-custom" id="current-time"></div>
    </div>
</header>