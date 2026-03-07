@extends('layouts.app')

@section('title', 'Notifikasi Service - IoT GPS Tracker')
@section('header-title', 'Notifikasi Service')

@section('content')
@php
$totalDanger = 0;
$totalWarning = 0;
foreach ($alerts as $alert) {
foreach ($alert['items'] as $item) {
if ($item['status'] === 'danger') $totalDanger++;
else $totalWarning++;
}
}
@endphp

{{-- Summary --}}
<div class="flex flex-wrap md:flex-nowrap gap-3 mb-5">
    <div class="bg-bg-secondary border border-border-color rounded-xs flex-1 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-danger-light text-danger rounded-xs flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation-triangle text-[20px]"></i>
            </div>
            <div>
                <div class="text-[12px] text-text-secondary font-medium mb-1">Terlambat Service</div>
                <div class="text-[24px] font-bold text-text-primary leading-none">{{ $totalDanger }}</div>
            </div>
        </div>
    </div>
    <div class="bg-bg-secondary border border-border-color rounded-xs flex-1 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-warning-light text-warning rounded-xs flex items-center justify-center shrink-0">
                <i class="fas fa-clock text-[20px]"></i>
            </div>
            <div>
                <div class="text-[12px] text-text-secondary font-medium mb-1">Mendekati Service</div>
                <div class="text-[24px] font-bold text-text-primary leading-none">{{ $totalWarning }}</div>
            </div>
        </div>
    </div>
    <div class="bg-bg-secondary border border-border-color rounded-xs flex-1 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-accent-light text-accent rounded-xs flex items-center justify-center shrink-0">
                <i class="fas fa-car text-[20px]"></i>
            </div>
            <div>
                <div class="text-[12px] text-text-secondary font-medium mb-1">Kendaraan Perlu Service</div>
                <div class="text-[24px] font-bold text-text-primary leading-none">{{ count($alerts) }}</div>
            </div>
        </div>
    </div>
</div>

@if(empty($alerts))
<div class="bg-bg-secondary border border-border-color rounded-xs text-center py-[60px] px-5">
    <i class="fas fa-check-circle text-[48px] text-success mb-4"></i>
    <h3 class="text-[16px] font-semibold mb-1.5 text-success">Semua Kendaraan Aman!</h3>
    <p class="text-[13px] text-text-secondary m-0">Tidak ada komponen yang perlu di-service saat ini.</p>
</div>
@else

{{-- Alerts per vehicle --}}
@foreach($alerts as $alert) 
@php $vehicle = $alert['vehicle']; @endphp
<div class="bg-bg-secondary border border-border-color rounded-xs mb-4 overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-bg-tertiary border-b border-border-color p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-[14px] shrink-0 {{ $vehicle->type === 'motor' ? 'bg-blue-500' : 'bg-purple-500' }}">
                @if($vehicle->type === 'motor')
                <i class="fas fa-motorcycle text-[16px]"></i>
                @else
                <i class="fas fa-car text-[16px]"></i>
                @endif
            </div>
            <div>
                <div class="font-semibold text-[15px] mb-0.5 text-text-primary">{{ $vehicle->name }}</div>
                <div class="text-[12px] text-text-secondary flex items-center gap-1.5 flex-wrap">
                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full border border-current {{ $vehicle->type === 'motor' ? 'text-blue-500' : 'text-purple-500' }}">{{ ucfirst($vehicle->type) }}</span>
                    @if($vehicle->plate_number) &bull; {{ $vehicle->plate_number }} @endif
                    &bull; {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
                </div>
            </div>
        </div>
        <a href="/vehicles/{{ $vehicle->id }}/service/create" class="inline-flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-xs text-[12px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600 no-underline shrink-0 w-max">
            <i class="fas fa-wrench"></i>
            Service Sekarang
        </a>
    </div>

    <div class="flex flex-col">
        @foreach($alert['items'] as $item)
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 py-3 px-4 border-b border-border-color last:border-b-0 {{ $item['status'] === 'danger' ? 'bg-danger-light/30' : '' }}">
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-xs flex items-center justify-center shrink-0 {{ $item['status'] === 'danger' ? 'bg-danger-light text-danger' : 'bg-warning-light text-warning' }}">
                    @if($item['status'] === 'danger')
                    <i class="fas fa-exclamation-triangle text-[14px]"></i>
                    @else
                    <i class="fas fa-clock text-[14px]"></i>
                    @endif
                </div>
                <div>
                    <div class="font-semibold text-[14px] text-text-primary mb-0.5">{{ $item['component'] }}</div>
                    <div class="text-[12px] text-text-secondary">{{ $item['description'] }} &bull; Interval {{ number_format($item['interval_km'], 0, ',', '.') }} KM</div>
                </div>
            </div>
            <div class="flex items-center shrink-0 sm:ml-3">
                @if($item['km_remaining'] <= 0)
                    <span class="font-semibold text-[12px] py-1 px-2.5 rounded-full whitespace-nowrap bg-danger text-white">Terlambat {{ number_format(abs($item['km_remaining']), 0, ',', '.') }} KM</span>
                    @else
                    <span class="font-semibold text-[12px] py-1 px-2.5 rounded-full whitespace-nowrap bg-warning-light border border-warning text-warning-dark">Sisa {{ number_format($item['km_remaining'], 0, ',', '.') }} KM</span>
                    @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endif
@endsection