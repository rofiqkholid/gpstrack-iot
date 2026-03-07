@extends('layouts.app')

@section('title', isset($vehicle) ? 'Edit Kendaraan' : 'Tambah Kendaraan' . ' - IoT GPS Tracker')
@section('header-title', isset($vehicle) ? 'Edit Kendaraan' : 'Tambah Kendaraan Baru')

@section('content')
<div class="bg-bg-secondary border border-border-color rounded-xs p-5">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[18px] font-semibold m-0 text-text-primary">{{ isset($vehicle) ? 'Edit Kendaraan' : 'Form Kendaraan Baru' }}</h2>
    </div>

    <form method="POST" action="{{ isset($vehicle) ? '/vehicles/' . $vehicle->id : '/vehicles' }}">
        @csrf
        @if(isset($vehicle))
        @method('PUT')
        @endif

        @if($errors->any())
        <div class="flex items-start gap-2 p-3.5 mb-5 bg-danger-light border border-danger/30 text-[13px] font-medium text-danger rounded-xs">
            <ul class="m-0 pl-5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
            <div class="flex flex-col gap-1.5 md:col-span-2 lg:col-span-3">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="name"><span>Nama Kendaraan <span class="text-danger ml-1">*</span></span></label>
                <input type="text" id="name" name="name" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-xs text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="Contoh: Honda Beat 2024" value="{{ old('name', $vehicle->name ?? '') }}" required>
            </div>

            <div class="flex flex-col gap-1.5 md:row-span-2 lg:row-span-2">
                <label class="font-semibold text-[13px] text-text-primary">Tipe Kendaraan <span class="text-danger ml-1">*</span></label>
                <div class="flex gap-3 h-full">
                    <label class="flex-1 cursor-pointer group h-full">
                        <input type="radio" name="type" value="motor" class="peer sr-only" {{ old('type', $vehicle->type ?? '') === 'motor' ? 'checked' : '' }} required>
                        <div class="flex flex-col items-center justify-center h-full gap-2 py-3 px-2 border border-border-color rounded-xs text-text-secondary transition-all group-hover:bg-bg-tertiary group-hover:border-text-secondary peer-checked:border-accent peer-checked:bg-accent-light/30 peer-checked:text-accent">
                            <i class="fas fa-motorcycle text-[22px]"></i>
                            <span class="text-[14px] font-medium">Motor</span>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer group h-full">
                        <input type="radio" name="type" value="mobil" class="peer sr-only" {{ old('type', $vehicle->type ?? '') === 'mobil' ? 'checked' : '' }}>
                        <div class="flex flex-col items-center justify-center h-full gap-2 py-3 px-2 border border-border-color rounded-xs text-text-secondary transition-all group-hover:bg-bg-tertiary group-hover:border-text-secondary peer-checked:border-accent peer-checked:bg-accent-light/30 peer-checked:text-accent">
                            <i class="fas fa-car text-[22px]"></i>
                            <span class="text-[14px] font-medium">Mobil</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 lg:col-span-2">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="plate_number">Plat Nomor</label>
                <input type="text" id="plate_number" name="plate_number" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-xs text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="Contoh: B 1234 XYZ" value="{{ old('plate_number', $vehicle->plate_number ?? '') }}">
            </div>

            <div class="flex flex-col gap-1.5 lg:col-span-2">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="brand">Merk</label>
                <input type="text" id="brand" name="brand" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-xs text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="Contoh: Honda, Toyota" value="{{ old('brand', $vehicle->brand ?? '') }}">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="model">Model</label>
                <input type="text" id="model" name="model" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-xs text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="Contoh: Beat, Avanza" value="{{ old('model', $vehicle->model ?? '') }}">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="year">Tahun</label>
                <input type="number" id="year" name="year" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-xs text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="2024" min="1900" max="2099" value="{{ old('year', $vehicle->year ?? '') }}">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="device_id">GPS Device</label>
                <select id="device_id" name="device_id" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-xs text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" onchange="toggleOdometerField()">
                    <option value="">Tidak terhubung (input manual)</option>
                    @foreach($devices as $deviceId)
                    <option value="{{ $deviceId }}" {{ old('device_id', $vehicle->device_id ?? '') === $deviceId ? 'selected' : '' }}>
                        {{ $deviceId }}
                    </option>
                    @endforeach
                </select>
                <span class="text-[11px] text-text-secondary">Jika terhubung GPS, odometer dihitung otomatis dari perjalanan GPS.</span>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="current_odometer">
                    <span>Odometer Awal (KM)</span>
                    <span id="odometer-auto-badge" class="inline-flex items-center gap-1 bg-accent-light text-accent text-[10px] font-bold py-0.5 px-1.5 rounded-xs" style="display: none;">
                        <i class="fas fa-satellite text-[10px]"></i>
                        Auto dari GPS
                    </span>
                </label>
                <input type="number" id="current_odometer" name="current_odometer" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-xs text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="0" min="0" value="{{ old('current_odometer', $vehicle->current_odometer ?? 0) }}">
                <span id="odometer-hint" class="text-[11px] text-text-secondary">
                    Odometer awal sebelum GPS dipasang. Setelah GPS terhubung, jarak akan dihitung otomatis.
                </span>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border-color">
            <a href="/vehicles" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[13px] font-medium text-text-primary bg-bg-tertiary border border-border-color cursor-pointer transition-all hover:bg-border-color/50 no-underline">Batal</a>
            <button type="submit" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-xs text-[13px] font-medium text-white bg-accent border-none cursor-pointer transition-all hover:bg-blue-600 no-underline">
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