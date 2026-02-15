@extends('layouts.app')

@section('title', $vehicle->name . ' - Service Kendaraan')
@section('header-title', 'Detail Kendaraan')

@section('content')
@if(session('success'))
<div class="alert alert-success" style="margin-bottom: 20px;">
    <i class="fas fa-check-circle"></i>
    {{ session('success') }}
</div>
@endif

{{-- Vehicle Info Card --}}
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div class="vehicle-type-icon {{ $vehicle->type }}" style="width: 48px; height: 48px;">
                @if($vehicle->type === 'motor')
                <i class="fas fa-motorcycle" style="font-size: 20px;"></i>
                @else
                <i class="fas fa-car" style="font-size: 20px;"></i>
                @endif
            </div>
            <div>
                <h2 class="card-title" style="margin-bottom: 2px;">{{ $vehicle->name }}</h2>
                <span class="vehicle-type-badge {{ $vehicle->type }}" style="font-size: 11px;">{{ ucfirst($vehicle->type) }}</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/vehicles/{{ $vehicle->id }}/edit" class="btn btn-secondary">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <a href="/vehicles/{{ $vehicle->id }}/service/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Tambah Riwayat Service
            </a>
            <form method="POST" action="/vehicles/{{ $vehicle->id }}" onsubmit="return confirm('Yakin ingin menghapus kendaraan {{ $vehicle->name }}? Semua riwayat service juga akan terhapus.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="vehicle-info-grid">
        @if($vehicle->plate_number)
        <div class="info-item">
            <div class="info-label">Plat Nomor</div>
            <div class="info-value">{{ $vehicle->plate_number }}</div>
        </div>
        @endif
        @if($vehicle->brand)
        <div class="info-item">
            <div class="info-label">Merk</div>
            <div class="info-value">{{ $vehicle->brand }}</div>
        </div>
        @endif
        @if($vehicle->model)
        <div class="info-item">
            <div class="info-label">Model</div>
            <div class="info-value">{{ $vehicle->model }}</div>
        </div>
        @endif
        @if($vehicle->year)
        <div class="info-item">
            <div class="info-label">Tahun</div>
            <div class="info-value">{{ $vehicle->year }}</div>
        </div>
        @endif
        <div class="info-item">
            <div class="info-label">Odometer Saat Ini
                @if($vehicle->hasGps())
                <span class="gps-auto-badge">
                    <i class="fas fa-satellite" style="font-size:9px;"></i>
                    GPS Auto
                </span>
                @endif
            </div>
            <div class="info-value" style="font-size: 18px; font-weight: 700; color: var(--accent);">{{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM</div>
        </div>
        @if($vehicle->device_id)
        <div class="info-item">
            <div class="info-label">GPS Device</div>
            <div class="info-value" style="display:flex;align-items:center;gap:6px;">
                <span style="width:8px;height:8px;background:var(--success);border-radius:50%;display:inline-block;"></span>
                {{ $vehicle->device_id }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Service Status Card --}}
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h2 class="card-title">Status Service</h2>
        <span style="font-size: 13px; color: var(--text-secondary);">
            Berdasarkan odometer {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
            @if($vehicle->hasGps())
            <span class="gps-auto-badge" style="margin-left: 4px;">
                <i class="fas fa-satellite" style="font-size:9px;"></i>
                dari GPS
            </span>
            @endif
        </span>
    </div>

    <div class="service-status-list">
        @foreach($serviceStatus as $status)
        <div class="service-status-item">
            <div class="service-status-header">
                <div class="service-status-name">
                    <div class="service-status-dot {{ $status['status'] }}"></div>
                    {{ $status['component'] }}
                </div>
                <div class="service-status-km">
                    @if($status['km_remaining'] > 0)
                    <span class="km-remaining {{ $status['status'] }}">Sisa {{ number_format($status['km_remaining'], 0, ',', '.') }} KM</span>
                    @else
                    <span class="km-remaining danger">Terlambat {{ number_format(abs($status['km_remaining']), 0, ',', '.') }} KM</span>
                    @endif
                </div>
            </div>
            <div class="service-progress-bar">
                <div class="service-progress-fill {{ $status['status'] }}" style="width: <?php echo $status['progress']; ?>%"></div>
            </div>
            <div class="service-status-meta">
                <span>Interval: {{ number_format($status['interval_km'], 0, ',', '.') }} KM</span>
                <span>Terakhir service: {{ $status['last_service_km'] > 0 ? number_format($status['last_service_km'], 0, ',', '.') . ' KM' : 'Belum pernah' }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Service History Card --}}
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Riwayat Service</h2>
        <span style="font-size: 13px; color: var(--text-secondary);">{{ $serviceRecords->count() }} catatan</span>
    </div>

    @if($serviceRecords->isEmpty())
    <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
        <p style="font-size: 14px;">Belum ada riwayat service untuk kendaraan ini.</p>
        <a href="/vehicles/{{ $vehicle->id }}/service/create" class="btn btn-primary" style="margin-top: 12px;">
            Tambah Riwayat Service Pertama
        </a>
    </div>
    @else
    <div class="service-history-list">
        @foreach($serviceRecords as $record)
        <div class="service-history-item">
            <div class="service-history-header">
                <div class="service-history-date">
                    <div class="service-date-badge">
                        <div class="date-day">{{ $record->service_date->format('d') }}</div>
                        <div class="date-month">{{ $record->service_date->format('M Y') }}</div>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">
                            {{ $record->workshop_name ?? 'Bengkel tidak dicatat' }}
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                            @if($record->technician_name)
                            Teknisi: {{ $record->technician_name }} &bull;
                            @endif
                            {{ number_format($record->odometer_at_service, 0, ',', '.') }} KM
                        </div>
                    </div>
                </div>
                <div class="service-cost">
                    Rp {{ number_format($record->total_cost, 0, ',', '.') }}
                </div>
            </div>

            <div class="service-items-list">
                @foreach($record->items as $item)
                <div class="service-item-row">
                    <div class="service-item-name">
                        <i class="fas fa-check-circle" style="font-size:13px;color:var(--accent);flex-shrink:0;"></i>
                        <span>{{ $item->component_name }}</span>
                        @if($item->description)
                        <span style="color: var(--text-secondary); font-size: 12px;">— {{ $item->description }}</span>
                        @endif
                    </div>
                    <div class="service-item-cost">
                        Rp {{ number_format($item->cost, 0, ',', '.') }}
                    </div>
                </div>
                @endforeach
            </div>

            @if($record->notes)
            <div class="service-notes">
                <strong>Catatan:</strong> {{ $record->notes }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

<div style="margin-top: 16px;">
    <a href="/vehicles" class="btn btn-secondary">
        <i class="fas fa-chevron-left"></i>
        Kembali ke Daftar Kendaraan
    </a>
</div>
@endsection