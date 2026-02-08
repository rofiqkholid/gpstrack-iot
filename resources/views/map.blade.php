@extends('layouts.app')

@section('title', 'Live Map - IoT GPS Tracker')
@section('header-title', 'Live Tracking Map')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                <path d="M2 12h20" />
            </svg>
        </div>
        <div class="stat-info">
            <h3 id="total-devices">0</h3>
            <p>Total Perangkat</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <path d="m9 11 3 3L22 4" />
            </svg>
        </div>
        <div class="stat-info">
            <h3 id="active-devices">0</h3>
            <p>Perangkat Aktif</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z" />
            </svg>
        </div>
        <div class="stat-info">
            <h3 id="avg-speed">0 km/h</h3>
            <p>Kecepatan Rata-rata</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                <circle cx="12" cy="10" r="3" />
            </svg>
        </div>
        <div class="stat-info">
            <h3 id="total-locations">0</h3>
            <p>Total Data Lokasi</p>
        </div>
    </div>
</div>

<div class="map-container">
    <div id="map"></div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize Map
    var map = L.map('map').setView([-6.20695, 107.29205], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    var markers = {};
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

    function createCustomIcon(color) {
        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                background: ${color};
                width: 28px;
                height: 28px;
                border-radius: 50%;
                border: 3px solid white;
                box-shadow: 0 2px 12px rgba(0,0,0,0.2);
            "></div>`,
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

                let totalSpeed = 0;
                data.forEach(location => {
                    const lat = parseFloat(location.latitude);
                    const lng = parseFloat(location.longitude);
                    const deviceId = location.device_id;
                    const color = getDeviceColor(deviceId);
                    const speed = location.speed ?? 0;
                    totalSpeed += speed;

                    const popupContent = `
                        <div style="font-family: Outfit, sans-serif; min-width: 180px;">
                            <strong style="font-size: 15px; display: block; margin-bottom: 8px;">${deviceId}</strong>
                            <div style="font-size: 13px; color: #64748b;">
                                <div style="margin-bottom: 4px;"><strong>Lat:</strong> ${lat.toFixed(6)}</div>
                                <div style="margin-bottom: 4px;"><strong>Lng:</strong> ${lng.toFixed(6)}</div>
                                <div style="margin-bottom: 4px;"><strong>Speed:</strong> ${speed.toFixed(1)} km/h</div>
                                <div><strong>Update:</strong> ${new Date(location.created_at).toLocaleTimeString('id-ID')}</div>
                            </div>
                        </div>
                    `;

                    if (markers[deviceId]) {
                        markers[deviceId].setLatLng([lat, lng]);
                        markers[deviceId].getPopup().setContent(popupContent);
                    } else {
                        markers[deviceId] = L.marker([lat, lng], {
                                icon: createCustomIcon(color)
                            })
                            .addTo(map)
                            .bindPopup(popupContent);
                    }
                });

                const avgSpeed = data.length > 0 ? (totalSpeed / data.length).toFixed(1) : 0;
                document.getElementById('avg-speed').textContent = avgSpeed + ' km/h';
            })
            .catch(err => console.error('Error:', err));
    }

    // Fetch total locations count
    fetch('/api/stats')
        .then(res => res.json())
        .then(stats => {
            document.getElementById('total-locations').textContent = stats.total_locations || 0;
        })
        .catch(() => {});

    setInterval(updateMap, 2000);
    updateMap();
</script>
@endpush