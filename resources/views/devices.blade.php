@extends('layouts.app')

@section('title', 'Daftar Perangkat - IoT GPS Tracker')
@section('header-title', 'Daftar Perangkat')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Perangkat Terdaftar</h2>
        <button class="btn btn-primary" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i>
            Refresh
        </button>
    </div>

    <div class="device-list" id="device-list">
        <p style="color: var(--text-secondary); text-align: center; padding: 40px;">
            Memuat data perangkat...
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function loadDevices() {
        fetch('/api/devices')
            .then(res => res.json())
            .then(devices => {
                const container = document.getElementById('device-list');

                if (devices.length === 0) {
                    container.innerHTML = `
                        <p style="color: var(--text-secondary); text-align: center; padding: 40px;">
                            Belum ada perangkat terdaftar. Jalankan simulator untuk menambahkan perangkat.
                        </p>
                    `;
                    return;
                }

                container.innerHTML = devices.map(device => `
                    <a href="/history?device=${device.device_id}" class="device-item">
                        <div class="device-icon">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <div class="device-info">
                            <div class="device-name">${device.device_id}</div>
                            <div class="device-meta">
                                Terakhir update: ${new Date(device.last_update).toLocaleString('id-ID')}
                            </div>
                        </div>
                        <div class="device-status ${device.is_active ? '' : 'offline'}">
                            <span class="status-dot"></span>
                            ${device.is_active ? 'Online' : 'Offline'}
                        </div>
                    </a>
                `).join('');
            })
            .catch(err => {
                console.error('Error:', err);
                document.getElementById('device-list').innerHTML = `
                    <p style="color: var(--danger); text-align: center; padding: 40px;">
                        Gagal memuat data perangkat.
                    </p>
                `;
            });
    }

    loadDevices();
    setInterval(loadDevices, 5000);
</script>
@endpush