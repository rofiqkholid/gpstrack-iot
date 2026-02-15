@extends('layouts.app')

@section('title', 'Daftar Kendaraan - IoT GPS Tracker')
@section('header-title', 'Daftar Kendaraan')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kendaraan Terdaftar</h2>
        <a href="/vehicles/create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Tambah Kendaraan
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($vehicles->isEmpty())
    <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
        <i class="fas fa-wrench" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
        <p style="font-size: 15px; font-weight: 500;">Belum ada kendaraan terdaftar</p>
        <p style="font-size: 13px; margin-top: 4px;">Klik tombol "Tambah Kendaraan" untuk menambahkan kendaraan baru.</p>
    </div>
    @else
    <div class="vehicle-grid">
        @foreach($vehicles as $vehicle)
        <a href="/vehicles/{{ $vehicle->id }}" class="vehicle-card">
            <div class="vehicle-card-header">
                <div class="vehicle-type-icon {{ $vehicle->type }}">
                    @if($vehicle->type === 'motor')
                    <i class="fas fa-motorcycle"></i>
                    @else
                    <i class="fas fa-car"></i>
                    @endif
                </div>
                <div class="vehicle-type-badge {{ $vehicle->type }}">
                    {{ ucfirst($vehicle->type) }}
                </div>
            </div>

            <div class="vehicle-card-body">
                <h3 class="vehicle-card-name">{{ $vehicle->name }}</h3>
                @if($vehicle->plate_number)
                <div class="vehicle-plate">{{ $vehicle->plate_number }}</div>
                @endif
                <div class="vehicle-card-meta">
                    @if($vehicle->brand || $vehicle->model)
                    <span>{{ $vehicle->brand }} {{ $vehicle->model }}</span>
                    @endif
                    @if($vehicle->year)
                    <span>{{ $vehicle->year }}</span>
                    @endif
                </div>
            </div>

            <div class="vehicle-card-footer">
                <div class="vehicle-odometer">
                    <i class="fas fa-tachometer-alt" style="font-size:13px;"></i>
                    {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
                </div>
                @php $urgentCount = $vehicle->getUrgentCount(); @endphp
                @if($urgentCount > 0)
                <div class="vehicle-warning-badge">
                    <i class="fas fa-exclamation-triangle" style="font-size:11px;"></i>
                    {{ $urgentCount }} perlu service
                </div>
                @elseif($vehicle->needsService())
                <div class="vehicle-caution-badge">
                    <i class="fas fa-exclamation-circle" style="font-size:11px;"></i>
                    Mendekati service
                </div>
                @else
                <div class="vehicle-ok-badge">
                    <i class="fas fa-check-circle" style="font-size:11px;"></i>
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