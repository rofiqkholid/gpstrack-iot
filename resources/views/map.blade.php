@extends('layouts.app')

@section('title', 'Live Map - IoT GPS Tracker')
@section('header-title', 'Live Tracking Map')

@section('content')
@push('styles')
<style>
    .leaflet-popup-content-wrapper {
        border-radius: 0.125rem !important;
        /* rounded-xs */
    }
</style>
@endpush
<div class="flex flex-wrap gap-4 mb-6">
    <div class="flex-1 min-w-[180px] bg-bg-secondary border border-border-color rounded-xs px-5 py-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xs flex items-center justify-center bg-accent-light text-accent text-[18px]">
            <i class="fas fa-satellite-dish w-5 h-5 flex items-center justify-center"></i>
        </div>
        <div class="flex flex-col gap-0.5">
            <h3 id="total-devices" class="text-[22px] font-bold m-0 order-last">0</h3>
            <p class="text-[12px] text-text-secondary font-medium m-0 order-first">Total Perangkat</p>
        </div>
    </div>
    <div class="flex-1 min-w-[180px] bg-bg-secondary border border-border-color rounded-xs px-5 py-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xs flex items-center justify-center bg-success-light text-success text-[18px]">
            <i class="fas fa-check-circle w-5 h-5 flex items-center justify-center"></i>
        </div>
        <div class="flex flex-col gap-0.5">
            <h3 id="active-devices" class="text-[22px] font-bold m-0 order-last">0</h3>
            <p class="text-[12px] text-text-secondary font-medium m-0 order-first">Perangkat Aktif</p>
        </div>
    </div>
    <div class="flex-1 min-w-[180px] bg-bg-secondary border border-border-color rounded-xs px-5 py-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xs flex items-center justify-center bg-warning-light text-warning text-[18px]">
            <i class="fas fa-car w-5 h-5 flex items-center justify-center"></i>
        </div>
        <div class="flex flex-col gap-0.5">
            <h3 class="text-[22px] font-bold m-0 order-last">{{ $totalVehicles }}</h3>
            <p class="text-[12px] text-text-secondary font-medium m-0 order-first">Total Kendaraan</p>
        </div>
    </div>
    <div class="flex-1 min-w-[180px] bg-bg-secondary border border-border-color rounded-xs px-5 py-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xs flex items-center justify-center {{ $serviceAlertCount > 0 ? 'bg-danger-light text-danger' : 'bg-purple-light text-purple' }} text-[18px]">
            <i class="fas fa-bell w-5 h-5 flex items-center justify-center"></i>
        </div>
        <div class="flex flex-col gap-0.5">
            <h3 class="text-[22px] font-bold m-0 order-last">{{ $serviceAlertCount }}</h3>
            <p class="text-[12px] text-text-secondary font-medium m-0 order-first">Notifikasi Service</p>
        </div>
    </div>
</div>

<div class="h-[calc(100vh-288px)] min-h-[400px] rounded-xs overflow-hidden border border-border-color relative z-0">
    <div id="map" class="h-full w-full"></div>

    {{-- Legend Panel --}}
    <div id="map-legend" class="absolute top-2.5 right-2.5 z-[1000] bg-white/95 backdrop-blur-sm border border-border-color rounded-xs shadow-md w-[250px] overflow-hidden flex flex-col transition-all duration-300">
        <div class="flex items-center justify-between py-2.5 px-3.5 bg-bg-tertiary border-b border-border-color font-semibold text-[13px] text-text-primary">
            <div class="flex items-center gap-2">
                <i class="fas fa-list-ul text-[13px]"></i>
                <span>Perangkat & Kendaraan</span>
            </div>
            <button class="bg-transparent border-none cursor-pointer text-text-secondary hover:text-text-primary px-1" onclick="toggleLegend()" title="Toggle Legend">
                <i class="fas fa-chevron-down transition-transform duration-300" id="legend-chevron"></i>
            </button>
        </div>
        <div class="max-h-[300px] overflow-y-auto transition-all duration-300" id="legend-body">
            <div class="p-5 text-center text-[13px] text-text-secondary">
                <i class="fas fa-spinner fa-spin"></i> Memuat...
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="application/json" id="device-vehicle-data">
    <?php echo json_encode($deviceVehicleMap); ?>
</script>
<script>
    // Device -> Vehicle mapping from server
    var deviceVehicleMap = JSON.parse(document.getElementById('device-vehicle-data').textContent);

    // Initialize Map
    var map = L.map('map', {
        zoomControl: false
    }).setView([-6.20695, 107.29205], 14);

    L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: '&copy; Google',
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }).addTo(map);

    var markers = {};
    var polylines = {};
    var deviceColors = {};
    var colorPalette = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
    var colorIndex = 0;

    function getDeviceColor(deviceId) {
        if (!deviceColors[deviceId]) {
            deviceColors[deviceId] = colorPalette[colorIndex % colorPalette.length];
            colorIndex++;
        }
        return deviceColors[deviceId];
    }

    function createCustomIcon(color, vehicleType) {
        var iconClass = vehicleType === 'motor' ? 'fa-motorcycle' : 'fa-car';
        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                background: ${color};
                width: 36px;
                height: 36px;
                border-radius: 50%;
                border: 3px solid white;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 14px;
            "><i class="fas ${iconClass}"></i></div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });
    }

    function createDefaultIcon(color) {
        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                background: ${color};
                width: 28px;
                height: 28px;
                border-radius: 50%;
                border: 3px solid white;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 11px;
            "><i class="fas fa-microchip"></i></div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
    }

    function updateMap() {
        fetch('/api/latest-locations')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-devices').textContent = data.length;
                document.getElementById('active-devices').textContent = data.length;

                var allBounds = [];

                data.forEach(location => {
                    const lat = parseFloat(location.latitude);
                    const lng = parseFloat(location.longitude);
                    const deviceId = location.device_id;
                    const color = getDeviceColor(deviceId);
                    const speed = location.speed ?? 0;
                    const vehicle = deviceVehicleMap[deviceId] || null;

                    allBounds.push([lat, lng]);

                    // Build popup with vehicle info
                    var vehicleName = vehicle ? vehicle.name : deviceId;
                    var vehicleInfo = '';
                    if (vehicle) {
                        vehicleInfo = `
                            <div style="background: var(--accent-light, #eff6ff); border-radius: 2px; padding: 6px 8px; margin-bottom: 8px;">
                                <div style="font-weight: 600; font-size: 11px; color: var(--accent, #3b82f6); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                                    <i class="fas ${vehicle.type === 'motor' ? 'fa-motorcycle' : 'fa-car'}" style="margin-right:4px;"></i>
                                    ${vehicle.type === 'motor' ? 'Motor' : 'Mobil'}
                                </div>
                                <div style="font-size: 12px; color: #64748b;">
                                    ${vehicle.plate ? vehicle.plate + ' &bull; ' : ''}${Number(vehicle.odometer).toLocaleString('id-ID')} KM
                                </div>
                            </div>
                        `;
                    }

                    const popupContent = `
                        <div style="font-family: Outfit, sans-serif; min-width: 200px;">
                            <strong style="font-size: 15px; display: block; margin-bottom: 6px;">${vehicleName}</strong>
                            ${vehicleInfo}
                            <div style="font-size: 12px; color: #64748b;">
                                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                                    <span><i class="fas fa-microchip" style="width:14px;"></i> Device</span>
                                    <span style="font-weight:500;">${deviceId}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                                    <span><i class="fas fa-map-pin" style="width:14px;"></i> Lat</span>
                                    <span style="font-weight:500;">${lat.toFixed(6)}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                                    <span><i class="fas fa-map-pin" style="width:14px;"></i> Lng</span>
                                    <span style="font-weight:500;">${lng.toFixed(6)}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                                    <span><i class="fas fa-tachometer-alt" style="width:14px;"></i> Kecepatan</span>
                                    <span style="font-weight:500;">${speed.toFixed(1)} km/h</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span><i class="fas fa-clock" style="width:14px;"></i> Update</span>
                                    <span style="font-weight:500;">${new Date(location.created_at).toLocaleTimeString('id-ID')}</span>
                                </div>
                            </div>
                        </div>
                    `;

                    var icon = vehicle ?
                        createCustomIcon(color, vehicle.type) :
                        createDefaultIcon(color);

                    if (markers[deviceId]) {
                        markers[deviceId].setLatLng([lat, lng]);
                        markers[deviceId].setIcon(icon);
                        markers[deviceId].getPopup().setContent(popupContent);
                    } else {
                        markers[deviceId] = L.marker([lat, lng], {
                                icon: icon
                            })
                            .addTo(map)
                            .bindPopup(popupContent);
                    }
                });

                // Fit bounds on first load
                if (allBounds.length > 0 && !window._mapFitted) {
                    map.fitBounds(allBounds, {
                        padding: [40, 40],
                        maxZoom: 14
                    });
                    window._mapFitted = true;
                }

                // Update legend
                updateLegend(data);
            })
            .catch(err => console.error('Error:', err));
    }

    // Draw route trails for each device
    function loadTrails() {
        fetch('/api/device-trails')
            .then(res => res.json())
            .then(trails => {
                Object.keys(trails).forEach(deviceId => {
                    const color = getDeviceColor(deviceId);
                    const points = trails[deviceId].map(p => [p.lat, p.lng]);

                    if (points.length < 2) return;

                    if (polylines[deviceId]) {
                        polylines[deviceId].setLatLngs(points);
                    } else {
                        polylines[deviceId] = L.polyline(points, {
                            color: color,
                            weight: 5,
                            opacity: 0.9,
                            smoothFactor: 1
                        }).addTo(map);
                    }
                });
            })
            .catch(err => console.error('Trail error:', err));
    }

    // Update legend panel
    function updateLegend(devices) {
        var body = document.getElementById('legend-body');
        if (!devices || devices.length === 0) {
            body.innerHTML = '<div class="p-5 text-center text-[13px] text-text-secondary flex flex-col items-center gap-2"><i class="fas fa-signal text-[18px]"></i> Tidak ada perangkat aktif</div>';
            return;
        }

        var html = '';
        devices.forEach(location => {
            var deviceId = location.device_id;
            var color = getDeviceColor(deviceId);
            var vehicle = deviceVehicleMap[deviceId] || null;
            var name = vehicle ? vehicle.name : 'Tidak terhubung';
            var plate = vehicle ? (vehicle.plate || '-') : '-';
            var typeIcon = vehicle ?
                (vehicle.type === 'motor' ? 'fa-motorcycle' : 'fa-car') :
                'fa-microchip';
            var speed = location.speed ? parseFloat(location.speed).toFixed(1) : '0.0';

            var statusIcon = vehicle ? 'fa-link' : 'fa-unlink';
            var statusColor = vehicle ? 'text-success' : 'text-danger';
            var statusText = vehicle ? 'Terhubung (' + vehicle.name + ')' : 'Tidak terhubung';

            html += `
                <div class="flex items-center gap-3 py-2.5 px-3.5 border-b border-border-color cursor-pointer transition-colors duration-150 hover:bg-bg-tertiary last:border-b-0" onclick="focusDevice('${deviceId}')">
                    <div class="flex-1 overflow-hidden">
                        <div class="font-semibold text-[13px] text-text-primary mb-0.5 whitespace-nowrap overflow-hidden text-ellipsis flex items-center">
                            ${deviceId}
                        </div>
                        <div class="text-[11px] ${statusColor} whitespace-nowrap overflow-hidden text-ellipsis flex items-center gap-1">
                            <i class="fas ${statusIcon} text-[10px]"></i>
                            ${statusText}
                        </div>
                    </div>
                </div>
            `;
        });

        body.innerHTML = html;
    }

    function focusDevice(deviceId) {
        if (markers[deviceId]) {
            var latlng = markers[deviceId].getLatLng();
            map.setView(latlng, 16, {
                animate: true
            });
            markers[deviceId].openPopup();
        }
    }

    function toggleLegend() {
        var body = document.getElementById('legend-body');
        var chevron = document.getElementById('legend-chevron');
        if (body.style.maxHeight === '0px') {
            body.style.maxHeight = '300px';
        } else {
            body.style.maxHeight = '0px';
        }
        chevron.classList.toggle('fa-chevron-down');
        chevron.classList.toggle('fa-chevron-up');
    }

    // Initial load
    updateMap();
    loadTrails();

    // Refresh every 3 seconds
    setInterval(updateMap, 3000);
    setInterval(loadTrails, 10000);
</script>
@endpush