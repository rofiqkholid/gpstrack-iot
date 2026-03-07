@extends('layouts.app')

@section('title', 'Riwayat Lokasi - IoT GPS Tracker')
@section('header-title', 'Riwayat Lokasi (7 Hari Terakhir)')

@section('content')
<div class="bg-bg-secondary border border-border-color rounded-custom p-5 mb-6">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[16px] font-semibold m-0">Pilih Perangkat</h2>
    </div>
    <div class="flex gap-3 flex-wrap items-center">
        <div
            x-data="{ 
                open: false, 
                selected: '',
                selectedLabel: 'Pilih Perangkat',
                devices: [],
                async init() {
                    const res = await fetch('/api/devices');
                    this.devices = await res.json();
                    
                    // Check URL params
                    const urlParams = new URLSearchParams(window.location.search);
                    const deviceId = urlParams.get('device');
                    if (deviceId) {
                        this.selected = deviceId;
                        this.selectedLabel = deviceId;
                        this.$nextTick(() => loadHistory());
                    }
                },
                selectOption(value) {
                    this.selected = value;
                    this.selectedLabel = value;
                    this.open = false;
                    document.getElementById('device-select').value = value;
                }
            }"
            class="relative min-w-[200px]"
            @click.away="open = false">
            <button
                type="button"
                @click="open = !open"
                class="flex items-center justify-between w-full py-2 px-3.5 bg-bg-primary border border-border-color rounded-md text-[14px] text-text-primary text-left cursor-pointer transition-all duration-200 outline-none hover:border-text-secondary focus:ring-2 focus:ring-accent/20"
                :class="{ 'border-accent ring-2 ring-accent/20': open }">
                <span x-text="selectedLabel"></span>
                <i class="fas fa-chevron-down text-text-secondary text-[12px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="absolute top-full left-0 right-0 mt-1 bg-bg-secondary border border-border-color rounded-md shadow-lg z-50 overflow-hidden">
                <div class="max-h-[250px] overflow-y-auto w-full">
                    <template x-for="device in devices" :key="device.device_id">
                        <button
                            type="button"
                            class="block w-full text-left py-2 px-3.5 text-[14px] text-text-primary bg-transparent border-none cursor-pointer transition-colors duration-150 hover:bg-bg-tertiary hover:text-accent"
                            :class="{ 'bg-accent-light text-accent font-medium': selected === device.device_id }"
                            @click="selectOption(device.device_id)"
                            x-text="device.device_id"></button>
                    </template>
                </div>
            </div>

            <input type="hidden" id="device-select" :value="selected">
        </div>

        <button class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[14px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600" onclick="loadHistory()">
            <i class="fas fa-chart-line"></i>
            Tampilkan Riwayat
        </button>
    </div>
</div>

<div class="bg-bg-secondary border border-border-color rounded-custom p-5">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[16px] font-semibold m-0">Data Riwayat</h2>
        <span id="history-count" class="text-text-secondary text-[14px]"></span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse" id="history-table">
            <thead>
                <tr>
                    <th class="py-3 px-4 text-left border-b border-border-color text-[11px] font-semibold uppercase tracking-[0.5px] text-text-secondary bg-bg-tertiary rounded-l-md">Waktu</th>
                    <th class="py-3 px-4 text-left border-b border-border-color text-[11px] font-semibold uppercase tracking-[0.5px] text-text-secondary bg-bg-tertiary">Latitude</th>
                    <th class="py-3 px-4 text-left border-b border-border-color text-[11px] font-semibold uppercase tracking-[0.5px] text-text-secondary bg-bg-tertiary">Longitude</th>
                    <th class="py-3 px-4 text-left border-b border-border-color text-[11px] font-semibold uppercase tracking-[0.5px] text-text-secondary bg-bg-tertiary rounded-r-md">Kecepatan</th>
                </tr>
            </thead>
            <tbody id="history-body">
                <tr>
                    <td colspan="4" class="text-center text-text-secondary p-10 border-b border-border-color text-[13px]">
                        Pilih perangkat untuk melihat riwayat.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-bg-secondary border border-border-color rounded-custom p-5 mt-6">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[16px] font-semibold m-0">Peta Riwayat</h2>
    </div>
    <div id="history-map" class="h-[400px] rounded-lg z-0 relative"></div>
</div>
@endsection

@push('scripts')
<script>
    var historyMap = L.map('history-map', {
        zoomControl: false
    }).setView([-6.20695, 107.29205], 13);
    L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: '&copy; Google',
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }).addTo(historyMap);

    var polyline = null;
    var historyMarkers = [];

    // Device selection now handled by Alpine.js component above

    function loadHistory() {
        const deviceId = document.getElementById('device-select').value;
        if (!deviceId) {
            alert('Pilih perangkat terlebih dahulu!');
            return;
        }

        fetch(`/api/history/${deviceId}`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('history-body');
                document.getElementById('history-count').textContent = `${data.length} data`;

                if (data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-text-secondary p-10 border-b border-border-color text-[13px]">
                                Tidak ada data untuk perangkat ini dalam 7 hari terakhir.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = data.map(row => `
                    <tr class="hover:bg-bg-tertiary transition-colors">
                        <td class="py-3 px-4 border-b border-border-color text-[13px]">${new Date(row.created_at).toLocaleString('id-ID')}</td>
                        <td class="py-3 px-4 border-b border-border-color text-[13px]">${parseFloat(row.latitude).toFixed(6)}</td>
                        <td class="py-3 px-4 border-b border-border-color text-[13px]">${parseFloat(row.longitude).toFixed(6)}</td>
                        <td class="py-3 px-4 border-b border-border-color text-[13px]">${row.speed ? row.speed.toFixed(1) + ' km/h' : '-'}</td>
                    </tr>
                `).join('');

                // Draw on map
                historyMarkers.forEach(m => historyMap.removeLayer(m));
                historyMarkers = [];
                if (polyline) historyMap.removeLayer(polyline);

                const latlngs = data.map(row => [parseFloat(row.latitude), parseFloat(row.longitude)]);

                if (latlngs.length > 0) {
                    polyline = L.polyline(latlngs, {
                        color: '#3b82f6',
                        weight: 4,
                        opacity: 0.8
                    }).addTo(historyMap);

                    // Start marker
                    historyMarkers.push(
                        L.marker(latlngs[0], {
                            icon: L.divIcon({
                                className: 'custom-marker',
                                html: '<div class="bg-success w-[18px] h-[18px] rounded-full border-[3px] border-white shadow-sm"></div>',
                                iconSize: [18, 18],
                                iconAnchor: [9, 9]
                            })
                        }).addTo(historyMap).bindPopup('<strong>Start</strong>')
                    );

                    // End marker
                    historyMarkers.push(
                        L.marker(latlngs[latlngs.length - 1], {
                            icon: L.divIcon({
                                className: 'custom-marker',
                                html: '<div class="bg-danger w-[18px] h-[18px] rounded-full border-[3px] border-white shadow-sm"></div>',
                                iconSize: [18, 18],
                                iconAnchor: [9, 9]
                            })
                        }).addTo(historyMap).bindPopup('<strong>End</strong>')
                    );

                    historyMap.fitBounds(polyline.getBounds(), {
                        padding: [50, 50]
                    });
                }
            })
            .catch(err => {
                console.error('Error:', err);
                document.getElementById('history-body').innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger p-10 border-b border-border-color text-[13px]">
                            Gagal memuat data riwayat.
                        </td>
                    </tr>
                `;
            });
    }
</script>
@endpush