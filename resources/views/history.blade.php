@extends('layouts.app')

@section('title', 'Riwayat Lokasi - IoT GPS Tracker')
@section('header-title', 'Riwayat Lokasi (7 Hari Terakhir)')

@section('content')
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h2 class="card-title">Pilih Perangkat</h2>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
        <select id="device-select" class="btn btn-secondary" style="min-width: 220px;">
            <option value="">-- Pilih Perangkat --</option>
        </select>
        <button class="btn btn-primary" onclick="loadHistory()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18" />
                <path d="m19 9-5 5-4-4-3 3" />
            </svg>
            Tampilkan Riwayat
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Data Riwayat</h2>
        <span id="history-count" style="color: var(--text-secondary); font-size: 14px;"></span>
    </div>

    <div style="overflow-x: auto;">
        <table class="history-table" id="history-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Kecepatan</th>
                </tr>
            </thead>
            <tbody id="history-body">
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                        Pilih perangkat untuk melihat riwayat.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h2 class="card-title">Peta Riwayat</h2>
    </div>
    <div id="history-map" style="height: 400px; border-radius: 8px;"></div>
</div>
@endsection

@push('scripts')
<script>
    var historyMap = L.map('history-map').setView([-6.20695, 107.29205], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19
    }).addTo(historyMap);

    var polyline = null;
    var historyMarkers = [];

    // Load devices for dropdown
    fetch('/api/devices')
        .then(res => res.json())
        .then(devices => {
            const select = document.getElementById('device-select');
            devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.device_id;
                option.textContent = device.device_id;
                select.appendChild(option);
            });

            // Check if device_id is in URL
            const urlParams = new URLSearchParams(window.location.search);
            const deviceId = urlParams.get('device');
            if (deviceId) {
                select.value = deviceId;
                loadHistory();
            }
        });

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
                            <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 40px;">
                                Tidak ada data untuk perangkat ini dalam 7 hari terakhir.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = data.map(row => `
                    <tr>
                        <td>${new Date(row.created_at).toLocaleString('id-ID')}</td>
                        <td>${parseFloat(row.latitude).toFixed(6)}</td>
                        <td>${parseFloat(row.longitude).toFixed(6)}</td>
                        <td>${row.speed ? row.speed.toFixed(1) + ' km/h' : '-'}</td>
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
                                html: '<div style="background:#22c55e; width:18px; height:18px; border-radius:50%; border:3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"></div>',
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
                                html: '<div style="background:#ef4444; width:18px; height:18px; border-radius:50%; border:3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"></div>',
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
                        <td colspan="4" style="text-align: center; color: var(--danger); padding: 40px;">
                            Gagal memuat data riwayat.
                        </td>
                    </tr>
                `;
            });
    }
</script>
@endpush