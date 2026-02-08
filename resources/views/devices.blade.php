@extends('layouts.app')

@section('title', 'Daftar Perangkat - IoT GPS Tracker')
@section('header-title', 'Daftar Perangkat')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Perangkat Terdaftar</h2>
        <button class="btn btn-primary" onclick="location.reload()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                <path d="M3 3v5h5" />
                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                <path d="M16 16h5v5" />
            </svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/>
                                <path d="M12 18h.01"/>
                            </svg>
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