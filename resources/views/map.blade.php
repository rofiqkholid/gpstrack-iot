@extends('layouts.app')

@section('title', 'Live Map - IoT GPS Tracker')
@section('header-title', 'Live Tracking Map')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-satellite-dish"></i>
        </div>
        <div class="stat-info">
            <h3 id="total-devices">0</h3>
            <p>Total Perangkat</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3 id="active-devices">0</h3>
            <p>Perangkat Aktif</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-car"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $totalVehicles }}</h3>
            <p>Total Kendaraan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $serviceAlertCount > 0 ? 'red' : 'purple' }}">
            <i class="fas fa-bell"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $serviceAlertCount }}</h3>
            <p>Notifikasi Service</p>
        </div>
    </div>
</div>

<div class="map-container" style="position: relative;">
    <div id="map"></div>

    {{-- Legend Panel --}}
    <div class="map-legend" id="map-legend">
        <div class="map-legend-header">
            <i class="fas fa-list-ul" style="font-size:13px;"></i>
            <span>Perangkat & Kendaraan</span>
            <button class="map-legend-toggle" onclick="toggleLegend()" title="Toggle Legend">
                <i class="fas fa-chevron-down" id="legend-chevron"></i>
            </button>
        </div>
        <div class="map-legend-body" id="legend-body">
            <div class="map-legend-loading">
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
    var map = L.map('map').setView([-6.20695, 107.29205], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
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
                            <div style="background: var(--accent-light, #eff6ff); border-radius: 6px; padding: 6px 8px; margin-bottom: 8px;">
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
                            weight: 3,
                            opacity: 0.6,
                            dashArray: '8, 6',
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
            body.innerHTML = '<div class="map-legend-empty"><i class="fas fa-signal"></i> Tidak ada perangkat aktif</div>';
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

            html += `
                <div class="map-legend-item" onclick="focusDevice('${deviceId}')">
                    <div class="legend-color-dot" style="background:${color};"></div>
                    <div class="legend-item-info">
                        <div class="legend-item-name">
                            <i class="fas ${typeIcon}" style="font-size:11px;color:${color};margin-right:4px;"></i>
                            ${name}
                        </div>
                        <div class="legend-item-meta">
                            ${deviceId}${plate !== '-' ? ' &bull; ' + plate : ''}
                        </div>
                        <div class="legend-item-speed">
                            <i class="fas fa-tachometer-alt"></i> ${speed} km/h
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
        body.classList.toggle('collapsed');
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