@extends('layouts.app')

@section('title', isset($vehicle) ? 'Edit Kendaraan' : 'Tambah Kendaraan' . ' - IoT GPS Tracker')
@section('header-title', isset($vehicle) ? 'Edit Kendaraan' : 'Tambah Kendaraan Baru')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">{{ isset($vehicle) ? 'Edit Kendaraan' : 'Form Kendaraan Baru' }}</h2>
    </div>

    <form method="POST" action="{{ isset($vehicle) ? '/vehicles/' . $vehicle->id : '/vehicles' }}" class="service-form">
        @csrf
        @if(isset($vehicle))
        @method('PUT')
        @endif

        @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label" for="name">Nama Kendaraan <span class="required">*</span></label>
                <input type="text" id="name" name="name" class="form-input" placeholder="Contoh: Honda Beat 2024" value="{{ old('name', $vehicle->name ?? '') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Tipe Kendaraan <span class="required">*</span></label>
                <div class="type-selector">
                    <label class="type-option">
                        <input type="radio" name="type" value="motor" {{ old('type', $vehicle->type ?? '') === 'motor' ? 'checked' : '' }} required>
                        <div class="type-option-content">
                            <i class="fas fa-motorcycle" style="font-size: 22px;"></i>
                            <span>Motor</span>
                        </div>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="mobil" {{ old('type', $vehicle->type ?? '') === 'mobil' ? 'checked' : '' }}>
                        <div class="type-option-content">
                            <i class="fas fa-car" style="font-size: 22px;"></i>
                            <span>Mobil</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="plate_number">Plat Nomor</label>
                <input type="text" id="plate_number" name="plate_number" class="form-input" placeholder="Contoh: B 1234 XYZ" value="{{ old('plate_number', $vehicle->plate_number ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="brand">Merk</label>
                <input type="text" id="brand" name="brand" class="form-input" placeholder="Contoh: Honda, Toyota" value="{{ old('brand', $vehicle->brand ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="model">Model</label>
                <input type="text" id="model" name="model" class="form-input" placeholder="Contoh: Beat, Avanza" value="{{ old('model', $vehicle->model ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="year">Tahun</label>
                <input type="number" id="year" name="year" class="form-input" placeholder="2024" min="1900" max="2099" value="{{ old('year', $vehicle->year ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="device_id">GPS Device</label>
                <select id="device_id" name="device_id" class="form-input" onchange="toggleOdometerField()">
                    <option value="">-- Tidak terhubung (input manual) --</option>
                    @foreach($devices as $deviceId)
                    <option value="{{ $deviceId }}" {{ old('device_id', $vehicle->device_id ?? '') === $deviceId ? 'selected' : '' }}>
                        {{ $deviceId }}
                    </option>
                    @endforeach
                </select>
                <span style="font-size: 11px; color: var(--text-secondary);">Jika terhubung GPS, odometer dihitung otomatis dari perjalanan GPS.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="current_odometer">
                    Odometer Awal (KM)
                    <span id="odometer-auto-badge" class="gps-auto-badge" style="display: none;">
                        <i class="fas fa-satellite" style="font-size:10px;"></i>
                        Auto dari GPS
                    </span>
                </label>
                <input type="number" id="current_odometer" name="current_odometer" class="form-input" placeholder="0" min="0" value="{{ old('current_odometer', $vehicle->current_odometer ?? 0) }}">
                <span id="odometer-hint" style="font-size: 11px; color: var(--text-secondary);">
                    Odometer awal sebelum GPS dipasang. Setelah GPS terhubung, jarak akan dihitung otomatis.
                </span>
            </div>
        </div>

        <div class="form-actions">
            <a href="/vehicles" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                {{ isset($vehicle) ? 'Simpan Perubahan' : 'Simpan Kendaraan' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleOdometerField() {
        const deviceSelect = document.getElementById('device_id');
        const odometerInput = document.getElementById('current_odometer');
        const autoBadge = document.getElementById('odometer-auto-badge');
        const hint = document.getElementById('odometer-hint');

        if (deviceSelect.value) {
            autoBadge.style.display = 'inline-flex';
            hint.textContent = 'Odometer awal sebelum GPS dipasang. Jarak GPS akan ditambahkan di atas nilai ini.';
        } else {
            autoBadge.style.display = 'none';
            hint.textContent = 'Masukkan odometer manual jika tidak menggunakan GPS.';
        }
    }

    // Run on page load
    toggleOdometerField();
</script>
@endpush