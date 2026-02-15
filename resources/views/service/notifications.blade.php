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
<div style="display: flex; gap: 12px; margin-bottom: 20px;">
    <div class="card" style="flex: 1; padding: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; background: var(--danger-light); color: var(--danger); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-exclamation-triangle" style="font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size: 24px; font-weight: 700; color: var(--danger);">{{ $totalDanger }}</div>
                <div style="font-size: 12px; color: var(--text-secondary); font-weight: 500;">Terlambat Service</div>
            </div>
        </div>
    </div>
    <div class="card" style="flex: 1; padding: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; background: var(--warning-light); color: var(--warning); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-clock" style="font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size: 24px; font-weight: 700; color: var(--warning);">{{ $totalWarning }}</div>
                <div style="font-size: 12px; color: var(--text-secondary); font-weight: 500;">Mendekati Service</div>
            </div>
        </div>
    </div>
    <div class="card" style="flex: 1; padding: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; background: var(--accent-light); color: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-car" style="font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size: 24px; font-weight: 700; color: var(--accent);">{{ count($alerts) }}</div>
                <div style="font-size: 12px; color: var(--text-secondary); font-weight: 500;">Kendaraan Perlu Service</div>
            </div>
        </div>
    </div>
</div>

@if(empty($alerts))
<div class="card" style="text-align: center; padding: 60px 20px;">
    <i class="fas fa-check-circle" style="font-size:48px;color:var(--success);margin-bottom:16px;"></i>
    <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 6px; color: var(--success);">Semua Kendaraan Aman!</h3>
    <p style="font-size: 13px; color: var(--text-secondary);">Tidak ada komponen yang perlu di-service saat ini.</p>
</div>
@else

{{-- Alerts per vehicle --}}
@foreach($alerts as $alert)
@php $vehicle = $alert['vehicle']; @endphp
<div class="card" style="margin-bottom: 16px;">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="vehicle-type-icon {{ $vehicle->type }}">
                @if($vehicle->type === 'motor')
                <i class="fas fa-motorcycle" style="font-size:16px;"></i>
                @else
                <i class="fas fa-car" style="font-size:16px;"></i>
                @endif
            </div>
            <div>
                <div style="font-weight: 600; font-size: 15px;">{{ $vehicle->name }}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    <span class="vehicle-type-badge {{ $vehicle->type }}" style="font-size: 10px; padding: 2px 8px;">{{ ucfirst($vehicle->type) }}</span>
                    @if($vehicle->plate_number) &bull; {{ $vehicle->plate_number }} @endif
                    &bull; {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
                </div>
            </div>
        </div>
        <a href="/vehicles/{{ $vehicle->id }}/service/create" class="btn btn-primary btn-sm">
            <i class="fas fa-wrench"></i>
            Service Sekarang
        </a>
    </div>

    <div class="notif-items-list">
        @foreach($alert['items'] as $item)
        <div class="notif-item {{ $item['status'] }}">
            <div class="notif-item-left">
                <div class="notif-status-icon {{ $item['status'] }}">
                    @if($item['status'] === 'danger')
                    <i class="fas fa-exclamation-triangle" style="font-size:14px;"></i>
                    @else
                    <i class="fas fa-clock" style="font-size:14px;"></i>
                    @endif
                </div>
                <div>
                    <div class="notif-component-name">{{ $item['component'] }}</div>
                    <div class="notif-component-desc">{{ $item['description'] }} &bull; Interval {{ number_format($item['interval_km'], 0, ',', '.') }} KM</div>
                </div>
            </div>
            <div class="notif-item-right">
                @if($item['km_remaining'] <= 0)
                    <span class="km-remaining danger">Terlambat {{ number_format(abs($item['km_remaining']), 0, ',', '.') }} KM</span>
                    @else
                    <span class="km-remaining warning">Sisa {{ number_format($item['km_remaining'], 0, ',', '.') }} KM</span>
                    @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endif
@endsection