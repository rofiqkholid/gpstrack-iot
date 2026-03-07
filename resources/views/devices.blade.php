@extends('layouts.app')

@section('title', 'Daftar Perangkat - IoT GPS Tracker')
@section('header-title', 'Daftar Perangkat')

@section('content')
<div class="bg-bg-secondary border border-border-color rounded-xs p-5">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[16px] font-semibold m-0">Perangkat Terdaftar</h2>
        <button class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[13px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i>
            Refresh
        </button>
    </div>

    <div class="flex flex-col gap-2" id="device-list">
        <p class="text-text-secondary text-center p-10 m-0">
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
                        <p class="text-text-secondary text-center p-10 m-0">
                            Belum ada perangkat terdaftar. Jalankan simulator untuk menambahkan perangkat.
                        </p>
                    `;
                    return;
                }

                container.innerHTML = devices.map(device => {
                    const statusClass = device.is_active ?
                        'text-success bg-success-light' : 'text-text-secondary bg-bg-tertiary';
                    const dotClass = device.is_active ?
                        'w-2 h-2 bg-success rounded-full animate-pulse' : 'w-2 h-2 bg-danger rounded-full';

                    return `
                    <a href="/history?device=${device.device_id}" class="flex items-center gap-3.5 p-3.5 px-4 bg-bg-tertiary rounded-xs transition-all duration-150 cursor-pointer text-inherit border border-transparent no-underline hover:border-border-color">
                        <div class="w-10 h-10 bg-accent-light rounded-xs flex items-center justify-center text-accent">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-[14px] font-semibold mb-0.5">${device.device_id}</div>
                            <div class="text-[12px] text-text-secondary">
                                Terakhir update: ${new Date(device.last_update).toLocaleString('id-ID')}
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 text-[12px] font-medium px-2.5 py-1 rounded-full ${statusClass}">
                            <span class="${dotClass}"></span>
                            ${device.is_active ? 'Online' : 'Offline'}
                        </div>
                    </a>
                `
                }).join('');
            })
            .catch(err => {
                console.error('Error:', err);
                document.getElementById('device-list').innerHTML = `
                    <p class="text-danger text-center p-10 m-0">
                        Gagal memuat data perangkat.
                    </p>
                `;
            });
    }

    loadDevices();
    setInterval(loadDevices, 5000);
</script>
@endpush