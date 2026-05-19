@extends('layouts.app')

@section('title', $vehicle->name . ' - Service Kendaraan')
@section('header-title', 'Detail Kendaraan')

@section('content')
@if(session('success'))
<div class="flex items-center gap-2 p-3.5 mb-5 bg-success-light border border-success/30 text-[15px] font-medium text-success rounded-none">
    <i class="fas fa-check-circle"></i>
    {{ session('success') }}
</div>
@endif

{{-- Vehicle Info Card --}}
<div class="bg-bg-secondary border border-border-color rounded-none p-5 mb-5">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 pb-4 border-b border-border-color">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-none flex items-center justify-center text-white text-[22px] shadow-sm shrink-0 {{ $vehicle->type === 'motor' ? 'bg-blue-500' : 'bg-purple-500' }}">
                @if($vehicle->type === 'motor')
                <i class="fas fa-motorcycle"></i>
                @else
                <i class="fas fa-car"></i>
                @endif
            </div>
            <div>
                <h2 class="text-[24px] font-semibold m-0 mb-0.5 text-text-primary">{{ $vehicle->name }}</h2>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="/vehicles/{{ $vehicle->id }}/edit" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-none text-[15px] font-medium text-text-primary bg-bg-tertiary border border-border-color cursor-pointer transition-all duration-150 hover:bg-border-color/50 no-underline">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <a href="/vehicles/{{ $vehicle->id }}/service/create" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-none text-[15px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600 no-underline">
                <i class="fas fa-plus"></i>
                Tambah Riwayat Service
            </a>
            <form method="POST" action="/vehicles/{{ $vehicle->id }}" onsubmit="return confirm('Yakin ingin menghapus kendaraan {{ $vehicle->name }}? Semua riwayat service juga akan terhapus.')" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-none text-[15px] font-medium text-white bg-danger border-none cursor-pointer transition-all duration-150 hover:bg-red-600 no-underline">
                    <i class="fas fa-trash-alt"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-2">
        <div class="bg-bg-tertiary rounded-none p-4 border border-border-color col-span-2 md:col-span-3 lg:col-span-full mb-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="sm:text-left">
                    <div class="text-[15px] font-bold text-text-secondary mb-1.5 block">Koneksi Perangkat GPS</div>
                @if($vehicle->device_id)
                    @php $isOnline = $vehicle->isDeviceOnline(); @endphp
                    <span class="inline-flex items-center gap-1.5 py-1 px-2.5 {{ $isOnline ? 'bg-success-light/30 border-success/30 text-success' : 'bg-danger-light/30 border-danger/30 text-danger' }} border rounded-none text-[15px] font-bold"><i class="fas fa-satellite-dish"></i> {{ $vehicle->device_id }}</span>
                    @else
                    <span class="inline-flex items-center py-1 px-2.5 bg-bg-secondary border border-border-color rounded-none text-[14px] font-medium text-text-secondary italic">Tidak Terhubung</span>
                    @endif
                </div>
            </div>
        </div>

        @if($vehicle->hasGps())
        @php
            $isOnline = $vehicle->isDeviceOnline();
            $gpsStats = $vehicle->getGpsStats();
        @endphp
        <div class="bg-bg-tertiary rounded-none p-4 border border-border-color col-span-2 md:col-span-3 lg:col-span-full mb-2">
            <div class="text-[15px] font-bold text-text-secondary mb-3 flex items-center gap-1.5">Data Tracking GPS</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Status Online/Offline --}}
                <div class="bg-bg-secondary rounded-none p-3 border border-border-color flex items-center gap-3">
                    <div class="w-10 h-10 rounded-none flex items-center justify-center text-[16px] {{ $isOnline ? 'bg-success-light text-success' : 'bg-danger-light text-danger' }}">
                        <i class="fas {{ $isOnline ? 'fa-wifi' : 'fa-wifi' }}"></i>
                    </div>
                    <div>
                        <div class="text-[13px] text-text-secondary font-medium mb-0.5">Status Perangkat</div>
                        <div class="text-[18px] font-bold {{ $isOnline ? 'text-success' : 'text-danger' }} flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-none {{ $isOnline ? 'bg-success animate-pulse' : 'bg-danger' }}"></span>
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </div>
                    </div>
                </div>

                {{-- Jarak Tempuh --}}
                <div class="bg-bg-secondary rounded-none p-3 border border-border-color flex items-center gap-3">
                    <div class="w-10 h-10 rounded-none flex items-center justify-center bg-accent-light text-accent text-[16px]">
                        <i class="fas fa-road"></i>
                    </div>
                    <div>
                        <div class="text-[13px] text-text-secondary font-medium mb-0.5">Jarak Tempuh GPS</div>
                        <div class="text-[18px] font-bold text-accent">
                            @if($gpsStats['total_distance_km'] >= 1)
                                {{ number_format($gpsStats['total_distance_km'], 2, ',', '.') }} <span class="text-[14px] font-medium text-text-secondary">KM</span>
                            @else
                                {{ number_format($gpsStats['total_distance_km'] * 1000, 0, ',', '.') }} <span class="text-[14px] font-medium text-text-secondary">meter</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($vehicle->plate_number)
        <div class="bg-bg-tertiary rounded-none p-3.5 border border-border-color">
            <div class="text-[14px] font-semibold text-text-secondary mb-1 opacity-80">Plat Nomor</div>
            <div class="font-bold text-[16px] text-text-primary">{{ $vehicle->plate_number }}</div>
        </div>
        @endif
        @if($vehicle->brand)
        <div class="bg-bg-tertiary rounded-none p-3.5 border border-border-color">
            <div class="text-[14px] font-semibold text-text-secondary mb-1 opacity-80">Merk</div>
            <div class="font-medium text-[16px] text-text-primary">{{ $vehicle->brand }}</div>
        </div>
        @endif
        @if($vehicle->model)
        <div class="bg-bg-tertiary rounded-none p-3.5 border border-border-color">
            <div class="text-[14px] font-semibold text-text-secondary mb-1 opacity-80">Model</div>
            <div class="font-medium text-[16px] text-text-primary">{{ $vehicle->model }}</div>
        </div>
        @endif
        @if($vehicle->year)
        <div class="bg-bg-tertiary rounded-none p-3.5 border border-border-color">
            <div class="text-[14px] font-semibold text-text-secondary mb-1 opacity-80">Tahun</div>
            <div class="font-medium text-[16px] text-text-primary">{{ $vehicle->year }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Service Status Card --}}
<div class="bg-bg-secondary border border-border-color rounded-none p-5 mb-5">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[20px] font-semibold m-0 text-text-primary">Status Service</h2>
        <span class="text-[15px] text-text-secondary flex items-center gap-2">
            @if($vehicle->hasGps())
            @php $currentKm = round($vehicle->getGpsStats()['total_distance_km']); @endphp
            Berdasarkan jarak GPS {{ number_format($currentKm, 0, ',', '.') }} KM
            <span class="inline-flex items-center gap-1 bg-accent-light/30 text-accent text-[13px] font-bold py-0.5 px-1.5 rounded-none">
                <i class="fas fa-satellite-dish text-[11px]"></i>
                Auto Update
            </span>
            @else
            Berdasarkan jarak {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
            @endif
        </span>
    </div>

    <div class="flex flex-col gap-4">
        @foreach($serviceStatus as $status)
        <div class="bg-bg-tertiary border border-border-color rounded-none p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-bold text-[16px] text-text-primary flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-none {{ $status['status'] === 'danger' ? 'bg-danger' : ($status['status'] === 'warning' ? 'bg-warning' : 'bg-success') }}"></div>
                    {{ $status['component'] }}
                </div>
                <div class="text-right">
                    @if($status['km_remaining'] > 0)
                    <span class="font-bold text-warning-dark whitespace-nowrap" style="font-size: 10pt;">Sisa {{ number_format($status['km_remaining'], 0, ',', '.') }} KM</span>
                    @else
                    <span class="font-bold text-danger whitespace-nowrap" style="font-size: 10pt;">Terlambat {{ number_format(abs($status['km_remaining']), 0, ',', '.') }} KM</span>
                    @endif
                </div>
            </div>
            <div class="h-2.5 bg-border-color rounded-none overflow-hidden mb-3">
                <div class="h-full rounded-none transition-all duration-300 {{ $status['status'] === 'danger' ? 'bg-danger' : ($status['status'] === 'warning' ? 'bg-warning' : 'bg-success') }}" style="width: <?php echo $status['progress']; ?>%"></div>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 text-[13px] font-medium text-text-secondary">
                <div class="flex items-center gap-3">
                    <span>Interval: {{ number_format($status['interval_km'], 0, ',', '.') }} KM</span>
                    <span>&bull;</span>
                    <span>Service berikutnya: {{ number_format(($status['last_service_km'] + $status['interval_km']), 0, ',', '.') }} KM</span>
                </div>
                <span>Terakhir service: {{ $status['last_service_km'] > 0 ? number_format($status['last_service_km'], 0, ',', '.') . ' KM' : 'Belum pernah' }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Service History Card --}}
<div class="bg-bg-secondary border border-border-color rounded-none p-5">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[20px] font-semibold m-0 text-text-primary">Riwayat Service</h2>
        <span class="text-[15px] text-text-secondary">{{ $serviceRecords->count() }} catatan</span>
    </div>

    @if($serviceRecords->isEmpty())
    <div class="text-center py-10 px-5 text-text-secondary">
        <p class="text-[16px] m-0">Belum ada riwayat service untuk kendaraan ini.</p>
        <a href="/vehicles/{{ $vehicle->id }}/service/create" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-none text-[15px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600 no-underline mt-3">
            Tambah Riwayat Service Pertama
        </a>
    </div>
    @else
    <div class="flex flex-col gap-4">
        @foreach($serviceRecords as $record)
        <div class="bg-bg-tertiary border border-border-color rounded-none p-4">
            <div class="flex items-start justify-between mb-3 pb-3 border-b border-border-color border-dashed">
                <div class="flex items-center gap-3.5">
                    {{-- Fixed size calendar block with no overflow --}}
                    <div class="bg-bg-secondary border border-border-color rounded-none shrink-0 flex flex-col items-center justify-center w-[54px] h-[54px] py-1">
                        <div class="font-bold text-[20px] text-accent leading-none mb-0.5">{{ $record->service_date->format('d') }}</div>
                        <div class="text-[11px] font-bold text-text-secondary uppercase tracking-wider">{{ $record->service_date->format('M') }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-[18px] text-text-primary">
                            {{ $record->workshop_name ?? 'Bengkel tidak dicatat' }}
                        </div>
                        <div class="text-[14px] text-text-secondary mt-1">
                            @if($record->technician_name)
                            Teknisi: {{ $record->technician_name }} &bull;
                            @endif
                            {{ number_format($record->odometer_at_service, 0, ',', '.') }} KM &bull;
                            {{ $record->service_date->format('Y') }}
                        </div>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="font-bold text-[18px] text-accent">
                        Rp {{ number_format($record->total_cost, 0, ',', '.') }}
                    </div>
                    <form method="POST" action="/service-records/{{ $record->id }}" onsubmit="return confirm('Apakah Anda yakin ingin me-rollback (menghapus) riwayat service ini? Notifikasi status service akan muncul kembali.')" class="mt-2 mb-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 py-1 px-2.5 bg-warning hover:bg-[#d97706] text-white border-none text-[12px] font-bold cursor-pointer transition-all duration-150 rounded-none">
                            <i class="fas fa-undo text-[11px]"></i>
                            Rollback
                        </button>
                    </form>
                </div>
            </div>

            {{-- Clean, modern borderless service items list --}}
            <div class="mt-3 mb-2 divide-y divide-border-color/50">
                @foreach($record->items as $item)
                <div class="flex items-center justify-between py-2 text-[15px]">
                    <div class="text-text-primary flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-accent rounded-none shrink-0"></span>
                        <span class="font-medium">{{ $item->component_name }}</span>
                        @if($item->description)
                        <span class="text-text-secondary text-[13px]">— {{ $item->description }}</span>
                        @endif
                    </div>
                    <div class="font-medium text-text-primary">
                        Rp {{ number_format($item->cost, 0, ',', '.') }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Sleek, premium note bar with left vertical border --}}
            @if($record->notes)
            <div class="text-[14px] text-text-secondary bg-bg-secondary border-l-2 border-accent/40 p-3 mt-3">
                <strong class="text-text-primary font-semibold">Catatan:</strong> {{ $record->notes }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

<div class="mt-4">
    <a href="/vehicles" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-5 rounded-none text-[15px] font-medium text-text-primary bg-bg-tertiary border border-border-color cursor-pointer transition-all duration-150 hover:bg-border-color/50 no-underline">
        <i class="fas fa-chevron-left"></i>
        Kembali ke Daftar Kendaraan
    </a>
</div>
@endsection