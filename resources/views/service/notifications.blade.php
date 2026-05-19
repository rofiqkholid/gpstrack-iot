@extends('layouts.app')

@section('title', 'Notifikasi Service - IoT GPS Tracker')
@section('header-title', 'Notifikasi Service')

@section('content')
@php
$totalDanger = 0;
$totalWarning = 0;
$alertList = [];

foreach ($alerts as $alert) {
    $vehicle = $alert['vehicle'];
    foreach ($alert['items'] as $item) {
        if ($item['status'] === 'danger') {
            $totalDanger++;
        } else {
            $totalWarning++;
        }
        $alertList[] = [
            'vehicle' => $vehicle,
            'item' => $item
        ];
    }
}
@endphp

<!-- Stats Cards (At the Top) -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 mb-6">
    <!-- Card 1: Terlambat Service -->
    <div class="bg-bg-secondary border border-border-color rounded-none p-3 md:px-5 md:py-4 flex items-center gap-2.5 md:gap-3.5">
        <div class="w-9 h-9 md:w-10 md:h-10 rounded-none flex items-center justify-center bg-danger-light text-danger text-[16px] md:text-[18px] shrink-0">
            <i class="fas fa-exclamation-triangle w-4 h-4 md:w-5 md:h-5 flex items-center justify-center"></i>
        </div>
        <div class="flex flex-col gap-0.5 min-w-0">
            <h3 class="text-[18px] md:text-[24px] font-bold m-0 order-last truncate">{{ $totalDanger }}</h3>
            <p class="text-[12px] md:text-[14px] text-text-secondary font-semibold m-0 order-first truncate">Terlambat Service</p>
            <span class="text-[11px] text-danger font-medium mt-0.5 leading-none">Perlu penanganan segera</span>
        </div>
    </div>

    <!-- Card 2: Mendekati Service -->
    <div class="bg-bg-secondary border border-border-color rounded-none p-3 md:px-5 md:py-4 flex items-center gap-2.5 md:gap-3.5">
        <div class="w-9 h-9 md:w-10 md:h-10 rounded-none flex items-center justify-center bg-warning-light text-warning text-[16px] md:text-[18px] shrink-0">
            <i class="fas fa-clock w-4 h-4 md:w-5 md:h-5 flex items-center justify-center"></i>
        </div>
        <div class="flex flex-col gap-0.5 min-w-0">
            <h3 class="text-[18px] md:text-[24px] font-bold m-0 order-last truncate">{{ $totalWarning }}</h3>
            <p class="text-[12px] md:text-[14px] text-text-secondary font-semibold m-0 order-first truncate">Mendekati Service</p>
            <span class="text-[11px] text-warning-dark font-medium mt-0.5 leading-none">Komponen mendekati batas</span>
        </div>
    </div>

    <!-- Card 3: Kendaraan Perlu Service -->
    <div class="bg-bg-secondary border border-border-color rounded-none p-3 md:px-5 md:py-4 flex items-center gap-2.5 md:gap-3.5">
        <div class="w-9 h-9 md:w-10 md:h-10 rounded-none flex items-center justify-center bg-accent-light text-accent text-[16px] md:text-[18px] shrink-0">
            <i class="fas fa-car w-4 h-4 md:w-5 md:h-5 flex items-center justify-center"></i>
        </div>
        <div class="flex flex-col gap-0.5 min-w-0">
            <h3 class="text-[18px] md:text-[24px] font-bold m-0 order-last truncate">{{ count($alerts) }}</h3>
            <p class="text-[12px] md:text-[14px] text-text-secondary font-semibold m-0 order-first truncate">Kendaraan Perlu Service</p>
            <span class="text-[11px] text-accent font-medium mt-0.5 leading-none">Total unit terpengaruh</span>
        </div>
    </div>
</div>

<!-- Alerts Notification Feed -->
<div class="space-y-4">
    @if(empty($alertList))
    <div class="bg-bg-secondary border border-border-color rounded-none text-center py-[60px] px-5 shadow-sm">
        <i class="fas fa-check-circle text-[48px] text-success mb-4"></i>
        <h3 class="text-[18px] font-semibold mb-1.5 text-success">Semua Kendaraan Aman!</h3>
        <p class="text-[14px] text-text-secondary m-0">Tidak ada komponen yang perlu di-service saat ini.</p>
    </div>
    @else
        @foreach($alertList as $alertItem)
        @php
            $vehicle = $alertItem['vehicle'];
            $item = $alertItem['item'];
        @endphp
        <div class="bg-bg-secondary border border-border-color p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm rounded-none">
            <div class="flex items-start gap-3.5">
                <!-- Vehicle & Status Icon -->
                <div class="w-10 h-10 rounded-none flex items-center justify-center shrink-0 border {{ $vehicle->type === 'motor' ? 'bg-blue-50 border-blue-100 text-blue-500' : 'bg-purple-50 border-purple-100 text-purple-500' }}">
                    @if($vehicle->type === 'motor')
                    <i class="fas fa-motorcycle text-[18px]"></i>
                    @else
                    <i class="fas fa-car text-[18px]"></i>
                    @endif
                </div>
                
                <div>
                    <!-- Vehicle Info Header -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-[16px] text-text-primary">{{ $vehicle->name }}</span>
                        @if($vehicle->plate_number)
                        <span class="text-[13px] text-text-secondary font-medium">({{ $vehicle->plate_number }})</span>
                        @endif
                    </div>
                    
                    <!-- Alert Component name -->
                    <div class="font-bold text-[15px] text-accent mt-0.5">
                        {{ $item['component'] }}
                    </div>
                    
                    <!-- Description -->
                    <div class="text-[13px] text-text-secondary mt-1 leading-normal">
                        {{ $item['description'] }} &bull; Interval: {{ number_format($item['interval_km'], 0, ',', '.') }} KM
                    </div>
                </div>
            </div>
            
            <!-- Status Badge and Action Button -->
            <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-3 shrink-0 border-t border-border-color/50 pt-3 md:border-t-0 md:pt-0">
                <div>
                    @if($item['km_remaining'] <= 0)
                    <span class="text-danger whitespace-nowrap" style="font-size: 10pt;">Terlambat {{ number_format(abs($item['km_remaining']), 0, ',', '.') }} KM</span>
                    @else
                    <span class="font-bold text-warning-dark whitespace-nowrap" style="font-size: 10pt;">Sisa {{ number_format($item['km_remaining'], 0, ',', '.') }} KM</span>
                    @endif
                </div>
                
                <a href="/vehicles/{{ $vehicle->id }}/service/create?component={{ urlencode($item['component']) }}" class="inline-flex items-center justify-center gap-1.5 py-1.5 px-4 rounded-none text-[13px] font-bold text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600 no-underline">
                    <i class="fas fa-wrench"></i>
                    Service Sekarang
                </a>
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection