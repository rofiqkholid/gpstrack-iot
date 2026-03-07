@extends('layouts.app')

@section('title', 'Tambah Riwayat Service - ' . $vehicle->name)
@section('header-title', 'Tambah Riwayat Service')

@section('content')
<div class="bg-bg-secondary border border-border-color rounded-custom p-5 mb-5">
    <div class="flex items-center gap-3.5 py-1">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-[20px] shadow-sm shrink-0 {{ $vehicle->type === 'motor' ? 'bg-blue-500' : 'bg-purple-500' }}">
            @if($vehicle->type === 'motor')
            <i class="fas fa-motorcycle text-[16px]"></i>
            @else
            <i class="fas fa-car text-[16px]"></i>
            @endif
        </div>
        <div>
            <div class="font-semibold text-[15px] text-text-primary">{{ $vehicle->name }}</div>
            <div class="text-[12px] text-text-secondary">
                <span class="text-[10px] font-bold uppercase tracking-[0.5px] py-0.5 px-2.5 rounded-full border border-current text-current inline-block {{ $vehicle->type === 'motor' ? 'text-blue-500' : 'text-purple-500' }}">{{ ucfirst($vehicle->type) }}</span>
                @if($vehicle->plate_number) &bull; {{ $vehicle->plate_number }} @endif
                &bull; Odometer: {{ number_format($vehicle->current_odometer, 0, ',', '.') }} KM
            </div>
        </div>
    </div>
</div>

<div class="bg-bg-secondary border border-border-color rounded-custom p-5">
    <div class="flex flex-col gap-1 mb-4 pb-4 border-b border-border-color">
        <h2 class="text-[18px] font-semibold m-0 text-text-primary">Form Riwayat Service</h2>
        <span class="text-[12px] text-text-secondary">Diisi oleh teknisi bengkel</span>
    </div>

    <form method="POST" action="/vehicles/{{ $vehicle->id }}/service" id="service-form">
        @csrf

        @if($errors->any())
        <div class="flex items-start gap-2 p-3.5 mb-5 bg-danger-light border border-danger/30 text-[13px] font-medium text-danger rounded-md">
            <ul class="m-0 pl-5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="service_date"><span>Tanggal Service <span class="text-danger ml-1">*</span></span></label>
                <input type="date" id="service_date" name="service_date" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-md text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" value="{{ old('service_date', date('Y-m-d')) }}" required>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="odometer_at_service">
                    <span>Odometer Saat Service (KM) <span class="text-danger ml-1">*</span></span>
                    @if($vehicle->hasGps())
                    <span class="inline-flex items-center gap-1 bg-accent-light text-accent text-[10px] font-bold py-0.5 px-1.5 rounded uppercase tracking-wider">
                        <i class="fas fa-satellite text-[10px]"></i>
                        Dari GPS Track
                    </span>
                    @endif
                </label>
                <input type="number" id="odometer_at_service" name="odometer_at_service" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-md text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent {{ $vehicle->hasGps() ? '!bg-bg-tertiary/50 !cursor-not-allowed' : '' }}" min="0" value="{{ old('odometer_at_service', $vehicle->current_odometer) }}" {{ $vehicle->hasGps() ? 'readonly' : '' }} required>
                @if($vehicle->hasGps())
                <span class="text-[11px] text-accent flex items-center gap-1">
                    <i class="fas fa-info-circle text-[10px]"></i>
                    Dihitung otomatis dari riwayat perjalanan GPS ({{ $vehicle->device_id }})
                </span>
                @endif
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="workshop_name">Nama Bengkel</label>
                <input type="text" id="workshop_name" name="workshop_name" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-md text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="Contoh: Bengkel Jaya Motor" value="{{ old('workshop_name') }}">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="technician_name">Nama Teknisi</label>
                <input type="text" id="technician_name" name="technician_name" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-md text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="Contoh: Budi Santoso" value="{{ old('technician_name') }}">
            </div>

            <div class="flex flex-col gap-1.5 md:col-span-2 lg:col-span-3">
                <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between" for="notes">Catatan Tambahan</label>
                <textarea id="notes" name="notes" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-md text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" rows="3" placeholder="Catatan tambahan mengenai kondisi kendaraan, saran perawatan, dll.">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Service Items Section --}}
        <div class="mt-6 pt-5 border-t border-border-color">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-semibold m-0 text-text-primary">Item Service / Pergantian</h3>
                <button type="button" class="inline-flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-md text-[12px] font-medium text-white bg-accent border-none cursor-pointer transition-all duration-150 hover:bg-blue-600 no-underline" onclick="addServiceItem()">
                    <i class="fas fa-plus"></i>
                    Tambah Item
                </button>
            </div>

            {{-- Quick add from schedule --}}
            <div class="bg-bg-tertiary border border-border-color rounded-lg p-3.5 mb-5 flex flex-col gap-2">
                <label class="text-[12px] text-text-secondary m-0">Pilih cepat dari jadwal service:</label>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($schedules as $schedule)
                    <button type="button" class="bg-bg-secondary border border-border-color text-text-primary rounded-md py-1.5 px-3 text-[12px] font-medium cursor-pointer transition-all hover:border-accent hover:text-accent" onclick="addScheduleItem('{{ $schedule->component }}', '{{ $schedule->description }}')">
                        {{ $schedule->component }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div id="service-items-container">
                {{-- Items will be added here dynamically --}}
            </div>

            <div class="flex items-center justify-between mt-5 pt-4 border-t border-border-color border-dashed">
                <span class="font-semibold text-text-primary">Total Biaya:</span>
                <span id="total-cost" class="text-[18px] font-bold text-accent">Rp 0</span>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-border-color">
            <a href="/vehicles/{{ $vehicle->id }}" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-md text-[13px] font-medium text-text-primary bg-bg-tertiary border border-border-color cursor-pointer transition-all hover:bg-border-color/50 no-underline">Batal</a>
            <button type="submit" class="inline-flex items-center justify-center gap-1.5 py-2 px-4 rounded-md text-[13px] font-medium text-white bg-accent border-none cursor-pointer transition-all hover:bg-blue-600 no-underline">
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
            <div class="bg-bg-tertiary border border-border-color rounded-lg p-4 mb-4 relative" id="item-${itemCount}">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-border-color/50">
                    <span class="font-bold text-[13px] text-text-secondary">#${itemCount}</span>
                    <button type="button" class="bg-danger-light text-danger border-none w-7 h-7 rounded-md flex items-center justify-center cursor-pointer transition-colors hover:bg-danger hover:text-white" onclick="removeServiceItem(${itemCount})">
                        <i class="fas fa-times text-[14px]"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pb-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between"><span>Nama Komponen <span class="text-danger ml-1">*</span></span></label>
                        <input type="text" name="items[${itemCount}][component_name]" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-md text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="Contoh: Ganti Oli Mesin" value="${name}" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between"><span>Biaya (Rp) <span class="text-danger ml-1">*</span></span></label>
                        <input type="number" name="items[${itemCount}][cost]" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-md text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent cost-input" placeholder="0" min="0" value="${cost}" required onchange="calculateTotal()" oninput="calculateTotal()">
                    </div>
                    <div class="flex flex-col gap-1.5 md:col-span-2">
                        <label class="font-semibold text-[13px] text-text-primary flex items-center justify-between"><span>Deskripsi</span></label>
                        <input type="text" name="items[${itemCount}][description]" class="w-full py-2 px-3 bg-bg-secondary border border-border-color rounded-md text-[14px] text-text-primary transition-colors focus:outline-none focus:border-accent" placeholder="Detail pekerjaan..." value="${description}">
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