<!-- Sidebar Component -->
<aside class="sidebar fixed left-0 top-0 w-[280px] md:w-sidebar h-screen bg-bg-secondary border-r border-border-color z-[1000] flex flex-col transition-transform duration-300 -translate-x-full md:translate-x-0 [&.open]:translate-x-0">
    <div class="h-header px-5 border-b border-border-color flex items-center justify-between">
        <a href="{{ url('/map') }}" class="flex items-center gap-3 no-underline text-text-primary">
            <div class="w-9 h-9 bg-accent rounded-custom flex items-center justify-center text-white text-[16px]">
                <i class="fas fa-globe"></i>
            </div>
            <div class="text-[18px] font-bold tracking-[-0.5px]">GPS<span class="text-accent">Track</span></div>
        </a>
        <!-- Close button for mobile -->
        <button class="md:hidden bg-transparent border-none cursor-pointer p-1.5 text-text-secondary rounded-custom hover:bg-bg-tertiary hover:text-text-primary [&>svg]:w-5 [&>svg]:h-5" onclick="toggleSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="flex-1 py-4 px-3 overflow-y-auto">
        {{-- Section: Monitoring --}}
        <div class="text-[11px] font-semibold uppercase tracking-[0.5px] text-text-secondary py-2 px-3 mt-2 first:mt-0">Monitoring</div>

        <a href="{{ url('/map') }}" class="flex items-center gap-2.5 py-2.5 px-3 rounded-lg no-underline text-text-secondary transition-all duration-150 mb-0.5 font-medium text-[14px] hover:bg-bg-tertiary hover:text-text-primary [&.active]:bg-accent-light [&.active]:text-accent [&.active_svg]:text-accent {{ request()->is('map') ? 'active' : '' }}">
            <span class="w-5 h-5 flex items-center justify-center shrink-0 text-[15px]"><i class="fas fa-map-marked-alt"></i></span>
            <span class="text-[14px] flex-1">Live Map</span>
        </a>

        {{-- Collapsible: Perangkat --}}
        <div class="nav-group mb-0.5 [&.open>.nav-group-toggle]:text-text-primary [&.open>.nav-group-toggle_.nav-group-chevron]:rotate-180 [&.open>.nav-group-children]:max-h-[300px] {{ request()->is('devices') || request()->is('history*') ? 'open' : '' }}">
            <button class="nav-group-toggle flex items-center gap-2.5 py-2.5 px-3 rounded-custom bg-transparent border-none cursor-pointer text-text-secondary transition-all duration-150 font-medium text-[14px] w-full text-left font-inherit hover:bg-bg-tertiary hover:text-text-primary" onclick="toggleNavGroup(this)">
                <span class="w-5 h-5 flex items-center justify-center shrink-0 text-[15px]"><i class="fas fa-microchip"></i></span>
                <span class="text-[14px] flex-1">Perangkat</span>
                <span class="ml-auto bg-accent-light text-accent text-[11px] font-semibold py-0.5 px-2 rounded-custom min-w-[20px] text-center" id="device-count">0</span>
                <span class="nav-group-chevron ml-auto w-4 h-4 flex items-center justify-center transition-transform duration-250 shrink-0 text-[12px]"><i class="fas fa-chevron-down"></i></span>
            </button>
            <div class="nav-group-children max-h-0 overflow-hidden transition-all duration-250 pl-5">
                <a href="{{ url('/devices') }}" class="flex items-center gap-2.5 py-2 px-3 rounded-custom no-underline text-text-secondary text-[13px] font-medium transition-all duration-150 mb-[1px] hover:text-text-primary hover:bg-bg-tertiary [&.active]:text-accent [&.active]:font-semibold group/child {{ request()->is('devices') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-border-color shrink-0 group-hover/child:bg-text-secondary group-[.active]/child:bg-accent"></span>
                    Daftar Perangkat
                </a>
                <a href="{{ url('/history') }}" class="flex items-center gap-2.5 py-2 px-3 rounded-custom no-underline text-text-secondary text-[13px] font-medium transition-all duration-150 mb-[1px] hover:text-text-primary hover:bg-bg-tertiary [&.active]:text-accent [&.active]:font-semibold group/child {{ request()->is('history*') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-border-color shrink-0 group-hover/child:bg-text-secondary group-[.active]/child:bg-accent"></span>
                    Riwayat Lokasi
                </a>
            </div>
        </div>

        {{-- Section: Service --}}
        <div class="text-[11px] font-semibold uppercase tracking-[0.5px] text-text-secondary py-2 px-3 mt-2 first:mt-0">Service Kendaraan</div>

        {{-- Collapsible: Kendaraan --}}
        <div class="nav-group mb-0.5 [&.open>.nav-group-toggle]:text-text-primary [&.open>.nav-group-toggle_.nav-group-chevron]:rotate-180 [&.open>.nav-group-children]:max-h-[300px] {{ request()->is('vehicles*') ? 'open' : '' }}">
            <button class="nav-group-toggle flex items-center gap-2.5 py-2.5 px-3 rounded-custom bg-transparent border-none cursor-pointer text-text-secondary transition-all duration-150 font-medium text-[14px] w-full text-left font-inherit hover:bg-bg-tertiary hover:text-text-primary" onclick="toggleNavGroup(this)">
                <span class="w-5 h-5 flex items-center justify-center shrink-0 text-[15px]"><i class="fas fa-car"></i></span>
                <span class="text-[14px] flex-1">Kendaraan</span>
                <span class="nav-group-chevron ml-auto w-4 h-4 flex items-center justify-center transition-transform duration-250 shrink-0 text-[12px]"><i class="fas fa-chevron-down"></i></span>
            </button>
            <div class="nav-group-children max-h-0 overflow-hidden transition-all duration-250 pl-5">
                <a href="{{ url('/vehicles') }}" class="flex items-center gap-2.5 py-2 px-3 rounded-custom no-underline text-text-secondary text-[13px] font-medium transition-all duration-150 mb-[1px] hover:text-text-primary hover:bg-bg-tertiary [&.active]:text-accent [&.active]:font-semibold group/child {{ request()->is('vehicles') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-border-color shrink-0 group-hover/child:bg-text-secondary group-[.active]/child:bg-accent"></span>
                    Daftar Kendaraan
                </a>
                <a href="{{ url('/vehicles/create') }}" class="flex items-center gap-2.5 py-2 px-3 rounded-custom no-underline text-text-secondary text-[13px] font-medium transition-all duration-150 mb-[1px] hover:text-text-primary hover:bg-bg-tertiary [&.active]:text-accent [&.active]:font-semibold group/child {{ request()->is('vehicles/create') ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-border-color shrink-0 group-hover/child:bg-text-secondary group-[.active]/child:bg-accent"></span>
                    Tambah Kendaraan
                </a>
            </div>
        </div>

        {{-- Notifikasi Service --}}
        <a href="{{ url('/service/notifications') }}" class="flex items-center gap-2.5 py-2.5 px-3 rounded-lg no-underline text-text-secondary transition-all duration-150 mb-0.5 font-medium text-[14px] hover:bg-bg-tertiary hover:text-text-primary [&.active]:bg-accent-light [&.active]:text-accent [&.active_svg]:text-accent {{ request()->is('service/notifications') ? 'active' : '' }}">
            <span class="w-5 h-5 flex items-center justify-center shrink-0 text-[15px]"><i class="fas fa-bell"></i></span>
            <span class="text-[14px] flex-1">Notifikasi Service</span>
            @php
            $totalAlerts = 0;
            foreach(\App\Models\Vehicle::all() as $v) { $totalAlerts += $v->getUrgentCount(); }
            @endphp
            @if($totalAlerts > 0)
            <span class="ml-auto bg-danger text-white text-[11px] font-semibold py-0.5 px-2 rounded-custom min-w-[20px] text-center">{{ $totalAlerts }}</span>
            @endif
        </a>

       
    </nav>
</aside>

<script>
    function toggleNavGroup(btn) {
        const group = btn.closest('.nav-group');
        group.classList.toggle('open');
    }
</script>