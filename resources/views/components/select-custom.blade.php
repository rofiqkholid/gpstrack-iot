{{-- Custom Select Component with Alpine.js --}}
{{-- Usage: @include('components.select-custom', ['id' => 'device-select', 'placeholder' => 'Pilih Perangkat', 'options' => []]) --}}

@props(['id' => 'custom-select', 'placeholder' => 'Pilih opsi', 'options' => [], 'selected' => null])

<div
    x-data="{ 
        open: false, 
        selected: '{{ $selected }}',
        selectedLabel: '',
        options: [],
        init() {
            // Options can be passed via Alpine or loaded dynamically
        },
        selectOption(value, label) {
            this.selected = value;
            this.selectedLabel = label;
            this.open = false;
            // Dispatch event for parent components
            this.$dispatch('select-change', { id: '{{ $id }}', value: value, label: label });
        }
    }"
    class="custom-select-wrapper"
    @click.away="open = false">
    {{-- Select Button --}}
    <button
        type="button"
        @click="open = !open"
        class="custom-select-btn"
        :class="{ 'active': open }">
        <span x-text="selectedLabel || '{{ $placeholder }}'"></span>
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="select-arrow"
            :class="{ 'rotate': open }">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </button>

    {{-- Dropdown Options --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="custom-select-dropdown">
        <div class="custom-select-options" id="{{ $id }}-options">
            {{ $slot }}
        </div>
    </div>

    {{-- Hidden input for form submission --}}
    <input type="hidden" name="{{ $id }}" :value="selected" id="{{ $id }}">
</div>