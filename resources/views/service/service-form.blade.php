@extends('layouts.app')

@section('title', 'Tambah Riwayat Service - ' . $vehicle->name)
@section('header-title', 'Tambah Riwayat Service')

@section('content')
<div class="card" style="margin-bottom: 20px;">
    <div style="display: flex; align-items: center; gap: 14px; padding: 4px 0;">
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
                {{ ucfirst($vehicle->type) }}
                @if($vehicle->plate_number) &bull; {{ $vehicle->plate_number }} @endif
                &bull; Odometer: {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Form Riwayat Service</h2>
        <span style="font-size: 12px; color: var(--text-secondary);">Diisi oleh teknisi bengkel</span>
    </div>

    <form method="POST" action="/vehicles/{{ $vehicle->id }}/service" class="service-form" id="service-form">
        @csrf

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
            <div class="form-group">
                <label class="form-label" for="service_date">Tanggal Service <span class="required">*</span></label>
                <input type="date" id="service_date" name="service_date" class="form-input" value="{{ old('service_date', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="odometer_at_service">
                    Odometer Saat Service (KM) <span class="required">*</span>
                    @if($vehicle->hasGps())
                    <span class="gps-auto-badge">
                        <i class="fas fa-satellite" style="font-size:10px;"></i>
                        Dari GPS Track
                    </span>
                    @endif
                </label>
                <input type="number" id="odometer_at_service" name="odometer_at_service" class="form-input" min="0" value="{{ old('odometer_at_service', $vehicle->current_odometer) }}" {{ $vehicle->hasGps() ? 'readonly style=background:var(--bg-tertiary);cursor:not-allowed;' : '' }} required>
                @if($vehicle->hasGps())
                <span style="font-size: 11px; color: var(--accent);">
                    <i class="fas fa-info-circle" style="font-size:10px;"></i>
                    Dihitung otomatis dari riwayat perjalanan GPS ({{ $vehicle->device_id }})
                </span>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label" for="workshop_name">Nama Bengkel</label>
                <input type="text" id="workshop_name" name="workshop_name" class="form-input" placeholder="Contoh: Bengkel Jaya Motor" value="{{ old('workshop_name') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="technician_name">Nama Teknisi</label>
                <input type="text" id="technician_name" name="technician_name" class="form-input" placeholder="Contoh: Budi Santoso" value="{{ old('technician_name') }}">
            </div>

            <div class="form-group full-width">
                <label class="form-label" for="notes">Catatan Tambahan</label>
                <textarea id="notes" name="notes" class="form-input" rows="3" placeholder="Catatan tambahan mengenai kondisi kendaraan, saran perawatan, dll.">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Service Items Section --}}
        <div class="service-items-section">
            <div class="service-items-header">
                <h3 style="font-size: 15px; font-weight: 600;">Item Service / Pergantian</h3>
                <button type="button" class="btn btn-primary btn-sm" onclick="addServiceItem()">
                    <i class="fas fa-plus"></i>
                    Tambah Item
                </button>
            </div>

            {{-- Quick add from schedule --}}
            <div class="quick-add-section">
                <label class="form-label" style="font-size: 12px; color: var(--text-secondary);">Pilih cepat dari jadwal service:</label>
                <div class="quick-add-buttons">
                    @foreach($schedules as $schedule)
                    <button type="button" class="quick-add-btn" onclick="addScheduleItem('{{ $schedule->component }}', '{{ $schedule->description }}')">
                        {{ $schedule->component }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div id="service-items-container">
                {{-- Items will be added here dynamically --}}
            </div>

            <div class="total-cost-section">
                <span style="font-weight: 600;">Total Biaya:</span>
                <span id="total-cost" style="font-size: 18px; font-weight: 700; color: var(--accent);">Rp 0</span>
            </div>
        </div>

        <div class="form-actions">
            <a href="/vehicles/{{ $vehicle->id }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Simpan Riwayat Service
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let itemCount = 0;

    function addServiceItem(name = '', description = '', cost = '') {
        itemCount++;
        const container = document.getElementById('service-items-container');
        const itemHtml = `
            <div class="service-item-entry" id="item-${itemCount}">
                <div class="service-item-entry-header">
                    <span class="service-item-number">#${itemCount}</span>
                    <button type="button" class="btn-remove" onclick="removeServiceItem(${itemCount})">
                        <i class="fas fa-times" style="font-size:14px;"></i>
                    </button>
                </div>
                <div class="form-grid" style="gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Nama Komponen <span class="required">*</span></label>
                        <input type="text" name="items[${itemCount}][component_name]" class="form-input" placeholder="Contoh: Ganti Oli Mesin" value="${name}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Biaya (Rp) <span class="required">*</span></label>
                        <input type="number" name="items[${itemCount}][cost]" class="form-input cost-input" placeholder="0" min="0" value="${cost}" required onchange="calculateTotal()" oninput="calculateTotal()">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="items[${itemCount}][description]" class="form-input" placeholder="Detail pekerjaan..." value="${description}">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
        calculateTotal();
    }

    function addScheduleItem(name, description) {
        addServiceItem(name, description, '');
    }

    function removeServiceItem(id) {
        const item = document.getElementById('item-' + id);
        if (item) {
            item.remove();
            calculateTotal();
        }
    }

    function calculateTotal() {
        const inputs = document.querySelectorAll('.cost-input');
        let total = 0;
        inputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });
        document.getElementById('total-cost').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    // Add first item by default
    addServiceItem();
</script>
@endpush