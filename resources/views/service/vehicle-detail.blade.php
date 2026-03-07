@extends('layouts.app')

@section('title', $vehicle->name . ' - Service Kendaraan')
@section('header-title', 'Detail Kendaraan')

@section('content')
@if(session('success'))
<div class="flex items-center gap-2 p-3.5 mb-5 bg-success-light border border-success/30 text-[13px] font-medium text-success rounded-xs">
    <i class="fas fa-check-circle"></i>
    {{ session('success') }}
</div>
@endif

{{-- Vehicle Info Card --}}
<div class="bg-bg-secondary border border-border-color rounded-xs p-5 mb-5">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 pb-4 border-b border-border-color">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-[20px] shadow-sm shrink-0 {{ $vehicle->type === 'motor' ? 'bg-blue-500' : 'bg-purple-500' }}">
                @if($vehicle->type === 'motor')
                <i class="fas fa-motorcycle"></i>
                @else
                <i class="fas fa-car"></i>
                @endif
            </div>
            <div>
                <h2 class="text-[18px] font-semibold m-0 mb-0.5 text-text-primary">{{ $vehicle->name }}</h2>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="/vehicles/{{ $vehicle->id }}/edit" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[13px] font-medium text-text-primary bg-bg-tertiary border border-border-color cursor-pointer transition-all duration-150 hover:bg-border-color/50 no-underline">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <a href="/vehicles/{{ $vehicle->id }}/service/create" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[13px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600 no-underline">
                <i class="fas fa-plus"></i>
                Tambah Riwayat Service
            </a>
            <form method="POST" action="/vehicles/{{ $vehicle->id }}" onsubmit="return confirm('Yakin ingin menghapus kendaraan {{ $vehicle->name }}? Semua riwayat service juga akan terhapus.')" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[13px] font-medium text-white bg-danger border-none cursor-pointer transition-all duration-150 hover:bg-red-600 no-underline">
                    <i class="fas fa-trash-alt"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-2">
        <div class="bg-bg-tertiary rounded-xs p-4 border border-border-color col-span-2 md:col-span-3 lg:col-span-full mb-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="text-[13px] font-medium text-text-secondary mb-1.5 flex items-center gap-1.5"><i class="fas fa-tachometer-alt opacity-70"></i> Odometer Saat Ini</div>
                    <div class="text-[24px] font-bold text-text-primary flex items-center gap-3">
                        {{ number_format($vehicle->current_odometer, 0, ',', '.') }} <span class="text-[14px] font-medium text-text-secondary relative top-0.5">KM</span>
                        @if($vehicle->hasGps())
                        <span class="inline-flex items-center gap-1 bg-accent-light/30 text-accent text-[11px] font-semibold py-0.5 px-1.5 rounded-xs relative bottom-1">
                            <i class="fas fa-satellite text-[10px]"></i>
                            Auto Update
                        </span>
                        @endif
                    </div>
                </div>

                <div class="sm:text-right">
                    <div class="text-[13px] font-medium text-text-secondary mb-1.5 block">Koneksi Perangkat GPS</div>
                    @if($vehicle->device_id)
                    @php $isOnline = $vehicle->isDeviceOnline(); @endphp
                    <span class="inline-flex items-center justify-center sm:justify-end gap-1.5 py-1 px-2.5 {{ $isOnline ? 'bg-success-light/30 border-success/30 text-success' : 'bg-danger-light/30 border-danger/30 text-danger' }} border rounded-xs text-[13px] font-bold"><i class="fas fa-satellite-dish"></i> {{ $vehicle->device_id }}</span>
                    @else
                    <span class="inline-flex items-center justify-center sm:justify-end py-1 px-2.5 bg-bg-secondary border border-border-color rounded-xs text-[12px] font-medium text-text-secondary italic">Tidak Terhubung</span>
                    @endif
                </div>
            </div>
        </div>

        @if($vehicle->plate_number)
        <div class="bg-bg-tertiary rounded-xs p-3.5 border border-border-color">
            <div class="text-[13px] font-medium text-text-secondary mb-1 opacity-80">Plat Nomor</div>
            <div class="font-mono font-bold text-[14px] text-text-primary tracking-wide">{{ $vehicle->plate_number }}</div>
        </div>
        @endif
        @if($vehicle->brand)
        <div class="bg-bg-tertiary rounded-xs p-3.5 border border-border-color">
            <div class="text-[13px] font-medium text-text-secondary mb-1 opacity-80">Merk</div>
            <div class="font-medium text-[14px] text-text-primary">{{ $vehicle->brand }}</div>
        </div>
        @endif
        @if($vehicle->model)
        <div class="bg-bg-tertiary rounded-xs p-3.5 border border-border-color">
            <div class="text-[13px] font-medium text-text-secondary mb-1 opacity-80">Model</div>
            <div class="font-medium text-[14px] text-text-primary">{{ $vehicle->model }}</div>
        </div>
        @endif
        @if($vehicle->year)
        <div class="bg-bg-tertiary rounded-xs p-3.5 border border-border-color">
            <div class="text-[13px] font-medium text-text-secondary mb-1 opacity-80">Tahun</div>
            <div class="font-medium text-[14px] text-text-primary">{{ $vehicle->year }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Service Status Card --}}
<div class="bg-bg-secondary border border-border-color rounded-xs p-5 mb-5">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[16px] font-semibold m-0 text-text-primary">Status Service</h2>
        <span class="text-[13px] text-text-secondary flex items-center gap-2">
            Berdasarkan odometer {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
            @if($vehicle->hasGps())
            <span class="inline-flex items-center gap-1 bg-accent-light/30 text-accent text-[11px] font-semibold py-0.5 px-1.5 rounded-xs">
                <i class="fas fa-satellite text-[10px]"></i>
                Auto Update
            </span>
            @endif
        </span>
    </div>

    <div class="flex flex-col gap-4">
        @foreach($serviceStatus as $status)
        <div class="bg-bg-tertiary border border-border-color rounded-xs p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold text-[14px] text-text-primary flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full {{ $status['status'] === 'danger' ? 'bg-danger' : ($status['status'] === 'warning' ? 'bg-warning' : 'bg-success') }}"></div>
                    {{ $status['component'] }}
                </div>
                <div class="text-right">
                    @if($status['km_remaining'] > 0)
                    <span class="font-semibold text-[12px] py-1 px-2.5 rounded-full whitespace-nowrap bg-warning-light border border-warning text-warning-dark">Sisa {{ number_format($status['km_remaining'], 0, ',', '.') }} KM</span>
                    @else
                    <span class="font-semibold text-[12px] py-1 px-2.5 rounded-full whitespace-nowrap bg-danger text-white">Terlambat {{ number_format(abs($status['km_remaining']), 0, ',', '.') }} KM</span>
                    @endif
                </div>
            </div>
            <div class="h-2 bg-border-color rounded-full overflow-hidden mb-3">
                <div class="h-full rounded-full transition-all duration-300 {{ $status['status'] === 'danger' ? 'bg-danger' : ($status['status'] === 'warning' ? 'bg-warning' : 'bg-success') }}" style="width: <?php echo $status['progress']; ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] font-medium text-text-secondary">
                <span>Interval: {{ number_format($status['interval_km'], 0, ',', '.') }} KM</span>
                <span>Terakhir service: {{ $status['last_service_km'] > 0 ? number_format($status['last_service_km'], 0, ',', '.') . ' KM' : 'Belum pernah' }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Service History Card --}}
<div class="bg-bg-secondary border border-border-color rounded-xs p-5">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[16px] font-semibold m-0 text-text-primary">Riwayat Service</h2>
        <span class="text-[13px] text-text-secondary">{{ $serviceRecords->count() }} catatan</span>
    </div>

    @if($serviceRecords->isEmpty())
    <div class="text-center py-10 px-5 text-text-secondary">
        <p class="text-[14px] m-0">Belum ada riwayat service untuk kendaraan ini.</p>
        <a href="/vehicles/{{ $vehicle->id }}/service/create" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[13px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600 no-underline mt-3">
            Tambah Riwayat Service Pertama
        </a>
    </div>
    @else
    <div class="flex flex-col gap-4">
        @foreach($serviceRecords as $record)
        <div class="bg-bg-tertiary border border-border-color rounded-xs p-4">
            <div class="flex items-start justify-between mb-4 pb-3 border-b border-border-color border-dashed">
                <div class="flex items-center gap-3.5">
                    <div class="bg-bg-secondary border border-border-color rounded-xs shrink-0 flex flex-col items-center justify-center w-[46px] h-[46px]">
                        <div class="font-bold text-[16px] text-text-primary leading-none mb-0.5">{{ $record->service_date->format('d') }}</div>
                        <div class="text-[12px] font-medium text-text-secondary">{{ $record->service_date->format('M Y') }}</div>
                    </div>
                    <div>
                        <div class="font-semibold text-[14px] text-text-primary">
                            {{ $record->workshop_name ?? 'Bengkel tidak dicatat' }}
                        </div>
                        <div class="text-[12px] text-text-secondary mt-0.5">
                            @if($record->technician_name)
                            Teknisi: {{ $record->technician_name }} &bull;
                            @endif
                            {{ number_format($record->odometer_at_service, 0, ',', '.') }} KM
                        </div>
                    </div>
                </div>
                <div class="font-bold text-[15px] text-accent mt-2 sm:mt-0">
                    Rp {{ number_format($record->total_cost, 0, ',', '.') }}
                </div>
            </div>

            <div class="flex flex-col gap-2 mb-3">
                @foreach($record->items as $item)
                <div class="flex items-center justify-between py-1.5 px-3 bg-bg-secondary rounded-xs border border-border-color text-[13px]">
                    <div class="font-medium text-text-primary flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-[13px] text-accent shrink-0"></i>
                        <span>{{ $item->component_name }}</span>
                        @if($item->description)
                        <span class="text-text-secondary text-[12px]">— {{ $item->description }}</span>
                        @endif
                    </div>
                    <div class="font-semibold text-text-secondary shrink-0">
                        Rp {{ number_format($item->cost, 0, ',', '.') }}
                    </div>
                </div>
                @endforeach
            </div>

            @if($record->notes)
            <div class="text-[13px] text-text-secondary bg-warning-light/30 border border-warning/20 rounded-xs p-3 mt-2">
                <strong class="font-medium text-warning-dark">Catatan:</strong> {{ $record->notes }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

<div class="mt-4">
    <a href="/vehicles" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[13px] font-medium text-text-primary bg-bg-tertiary border border-border-color cursor-pointer transition-all duration-150 hover:bg-border-color/50 no-underline">
        <i class="fas fa-chevron-left"></i>
        Kembali ke Daftar Kendaraan
    </a>
</div>
@endsection