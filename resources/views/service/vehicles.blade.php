@extends('layouts.app')

@section('title', 'Daftar Kendaraan - IoT GPS Tracker')
@section('header-title', 'Daftar Kendaraan')

@section('content')
<div class="bg-bg-secondary border border-border-color rounded-custom p-5">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[16px] font-semibold m-0">Kendaraan Terdaftar</h2>
        <a href="/vehicles/create" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[14px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600 no-underline">
            <i class="fas fa-plus"></i>
            Tambah Kendaraan
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 p-3.5 mb-5 bg-success-light border border-success/30 text-[13px] font-medium text-success rounded-xs">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($vehicles->isEmpty())
    <div class="text-center py-[60px] px-5 text-text-secondary">
        <i class="fas fa-wrench text-[48px] mb-4 opacity-50"></i>
        <p class="text-[15px] font-medium m-0">Belum ada kendaraan terdaftar</p>
        <p class="text-[13px] mt-1 mb-0">Klik tombol "Tambah Kendaraan" untuk menambahkan kendaraan baru.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($vehicles as $vehicle)
        <a href="/vehicles/{{ $vehicle->id }}" class="flex flex-col bg-bg-secondary border border-border-color rounded-xs transition-all duration-200 cursor-pointer overflow-hidden no-underline text-inherit">
            <div class="flex items-center justify-between p-4 border-b border-border-color bg-bg-tertiary">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-[14px] shadow-sm {{ $vehicle->type === 'motor' ? 'bg-blue-500' : 'bg-purple-500' }}">
                        @if($vehicle->type === 'motor')
                        <i class="fas fa-motorcycle"></i>
                        @else
                        <i class="fas fa-car"></i>
                        @endif
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-[15px] font-bold text-text-primary m-0 whitespace-nowrap overflow-hidden text-ellipsis">{{ $vehicle->name }}</h3>
                        @if($vehicle->plate_number)
                        <span class="text-[12px] text-text-secondary font-mono mt-0.5 whitespace-nowrap">{{ $vehicle->plate_number }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-4 flex-1 flex flex-col justify-center">
                <div class="mb-3">
                    <span class="text-[11px] text-text-secondary font-medium uppercase tracking-wider mb-1 block">Koneksi Perangkat GPS</span>
                    @if($vehicle->device_id)
                    @php $isOnline = $vehicle->isDeviceOnline(); @endphp
                    <span class="inline-flex items-center gap-1.5 py-1 px-2.5 {{ $isOnline ? 'bg-success-light/30 border-success/30 text-success' : 'bg-danger-light/30 border-danger/30 text-danger' }} border rounded-xs text-[13px] font-bold"><i class="fas fa-satellite-dish"></i> {{ $vehicle->device_id }}</span>
                    @else
                    <span class="inline-block py-1 px-2.5 bg-bg-tertiary border border-border-color rounded-xs text-[12px] font-medium text-text-secondary italic">Tidak Terhubung</span>
                    @endif
                </div>

                <div class="pt-3 border-t border-border-color/50 text-[12px] flex gap-3 flex-wrap text-text-secondary">
                    @if($vehicle->brand || $vehicle->model)
                    <span class="flex items-center gap-1"><i class="fas fa-tag opacity-60"></i> {{ $vehicle->brand }} {{ $vehicle->model }}</span>
                    @endif
                    @if($vehicle->year)
                    <span class="flex items-center gap-1"><i class="fas fa-calendar-alt opacity-60"></i> {{ $vehicle->year }}</span>
                    @endif
                </div>
            </div>

            <div class="p-3 px-4 bg-bg-tertiary border-t border-border-color flex items-center justify-between mt-auto">
                <div class="text-[12px] font-medium text-text-secondary flex items-center gap-1.5">
                    <i class="fas fa-tachometer-alt text-[13px]"></i>
                    {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
                </div>
                @php $urgentCount = $vehicle->getUrgentCount(); @endphp
                @if($urgentCount > 0)
                <div class="inline-flex items-center gap-1.5 bg-danger-light text-danger text-[11px] font-semibold py-1 px-2.5 rounded-xs">
                    <i class="fas fa-exclamation-triangle text-[11px]"></i>
                    {{ $urgentCount }} perlu service
                </div>
                @elseif($vehicle->needsService())
                <div class="inline-flex items-center gap-1.5 bg-warning-light text-warning text-[11px] font-semibold py-1 px-2.5 rounded-xs">
                    <i class="fas fa-exclamation-circle text-[11px]"></i>
                    Mendekati service
                </div>
                @else
                <div class="inline-flex items-center gap-1.5 bg-success-light text-success text-[11px] font-semibold py-1 px-2.5 rounded-xs">
                    <i class="fas fa-check-circle text-[11px]"></i>
                    Semua aman
                </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection