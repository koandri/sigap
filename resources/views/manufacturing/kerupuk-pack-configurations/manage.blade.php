@extends('layouts.app')

@section('title', 'Manage Pack Configurations')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/tabler/libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" />
<style>
    .pack-row {
        transition: background-color 0.2s;
    }
    .pack-row:hover {
        background-color: #f8f9fa;
    }
    
    /* Ensure all table cells are vertically aligned */
    #packs-table td {
        vertical-align: middle !important;
    }
    
    /* Ensure select elements have proper width before TomSelect initializes */
    .pack-select {
        width: 100% !important;
        min-width: 300px !important;
    }
    
    /* Fix Tom Select sizing to match Bootstrap form controls - same height as input-group */
    .ts-control {
        min-height: calc(1.5em + 0.75rem + 2px) !important;
        height: calc(1.5em + 0.75rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
        border: 1px solid #dadce0 !important;
        border-radius: 4px !important;
        background-color: #fff !important;
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }
    
    /* Ensure input-group has same height */
    .input-group > .form-control {
        height: calc(1.5em + 0.75rem + 2px) !important;
    }
    
    .input-group-text {
        height: calc(1.5em + 0.75rem + 2px) !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .ts-control.single .ts-control-input {
        height: auto !important;
        flex: 1 !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .ts-control.single .ts-control-input input {
        height: auto !important;
        line-height: 1.5 !important;
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        font-size: 0.875rem !important;
    }
    
    .ts-wrapper {
        width: 100% !important;
        display: block !important;
    }
    
    /* Ensure the original select is properly hidden when TomSelect is initialized */
    select.tomselected.ts-hidden-accessible {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        clip: rect(0, 0, 0, 0) !important;
        -webkit-clip-path: inset(50%) !important;
        clip-path: inset(50%) !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    
    /* Ensure table cells properly contain TomSelect wrappers */
    #packs-table td {
        position: relative;
        vertical-align: middle;
        overflow: visible !important;
    }
    
    #packs-table td .ts-wrapper {
        position: relative;
        width: 100%;
        z-index: 1;
    }
    
    /* Ensure table-responsive doesn't clip dropdown */
    #packs-table {
        position: relative;
    }
    
    /* When dropdown is appended to body, ensure proper positioning */
    body > .ts-dropdown {
        z-index: 99999 !important;
        position: absolute !important;
    }
    
    /* Fix Tom Select dropdown background and readability */
    .ts-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #dadce0 !important;
        border-radius: 4px !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        opacity: 1 !important;
        max-height: 300px !important;
        overflow-y: auto !important;
        z-index: 99999 !important;
        position: absolute !important;
    }
    
    .ts-dropdown .ts-dropdown-content {
        background-color: #ffffff !important;
        max-height: 300px !important;
        overflow-y: auto !important;
    }
    
    .ts-dropdown .option {
        background-color: #ffffff !important;
        color: #212529 !important;
        padding: 0.375rem 0.75rem !important;
        cursor: pointer !important;
    }
    
    .ts-dropdown .option:hover,
    .ts-dropdown .option.selected {
        background-color: #e9ecef !important;
        color: #212529 !important;
    }
    
    .ts-dropdown .option.active {
        background-color: #0d6efd !important;
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.dashboard') }}">Manufacturing</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manufacturing.kerupuk-pack-configurations.index') }}">Kerupuk Pack Configurations</a></li>
                        <li class="breadcrumb-item active">Manage</li>
                    </ol>
                </nav>
                <h2 class="page-title">{{ $item->label }}</h2>
                <p class="text-muted mb-0">
                    Define which Pack SKUs can be used for this Kerupuk Kg item
                    @if($item->itemCategory)
                    • <span class="badge bg-secondary text-white">{{ $item->itemCategory->name }}</span>
                    @endif
                </p>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('manufacturing.kerupuk-pack-configurations.index') }}" class="btn btn-outline-secondary">
                    <i class="far fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @include('layouts.alerts')

        <form method="POST" action="{{ route('manufacturing.kerupuk-pack-configurations.update', $item) }}">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pack SKUs</h3>
                    <div class="col-auto ms-auto">
                        <button type="button" class="btn btn-sm btn-primary" onclick="addPackRow()">
                            <i class="far fa-plus"></i> Add Pack SKU
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="far fa-info-circle me-2"></i>
                        <strong>Configuration Guide:</strong> Specify how many kilograms of <strong>{{ $item->label }}</strong> are required to produce one pack of each Pack SKU.
                        <br><small>Example: If 5 kg of {{ $item->label }} produces 1 pack of "Surya Bintang Kancing Kuning (Bal)", enter 5.00 in the Kg per Pack field.</small>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-vcenter" id="packs-table">
                            <thead>
                                <tr>
                                    <th style="width: 60%;">Pack SKU</th>
                                    <th style="width: 30%;">Kg per Pack</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->kerupukPackConfigurations as $index => $config)
                                <tr class="pack-row">
                                    <td>
                                        <select name="configurations[{{ $index }}][pack_item_id]" class="form-select pack-select" required>
                                            <option value="">Select pack SKU...</option>
                                            @foreach($packItems as $id => $label)
                                            <option value="{{ $id }}" {{ $config->pack_item_id == $id ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" 
                                                name="configurations[{{ $index }}][qty_kg_per_pack]" 
                                                class="form-control text-end" 
                                                value="{{ number_format((float) $config->qty_kg_per_pack, 2, '.', '') }}" 
                                                step="0.01" 
                                                min="0.01" 
                                                max="999999.99"
                                                required
                                                placeholder="e.g., 5.00">
                                            <span class="input-group-text">kg</span>
                                        </div>
                                        <small class="text-muted">Kg of {{ $item->name }} needed for 1 pack</small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePackRow(this)">
                                            <i class="far fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr class="pack-row">
                                    <td>
                                        <select name="configurations[0][pack_item_id]" class="form-select pack-select" required>
                                            <option value="">Select pack SKU...</option>
                                            @foreach($packItems as $id => $label)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" 
                                                name="configurations[0][qty_kg_per_pack]" 
                                                class="form-control text-end" 
                                                value="1.00" 
                                                step="0.01" 
                                                min="0.01" 
                                                max="999999.99"
                                                required
                                                placeholder="e.g., 5.00">
                                            <span class="input-group-text">kg</span>
                                        </div>
                                        
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePackRow(this)">
                                            <i class="far fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="far fa-save"></i> Save Configuration
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/tabler/libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
const packOptions = {!! json_encode(
    collect($packItems)->map(function ($label, $id) {
        return [
            'id' => $id,
            'label' => $label,
        ];
    })->values()
) !!};
const kerupukKgName = "{{ $item->label }}";
let configIndex = {{ $item->kerupukPackConfigurations->count() > 0 ? $item->kerupukPackConfigurations->count() : 1 }};

document.addEventListener('DOMContentLoaded', function() {
    initializeAllSelects();
});

function initializeAllSelects() {
    document.querySelectorAll('.pack-select').forEach(function(select) {
        if (!select.tomselect) {
            initializeTomSelect(select);
        }
    });
}

function initializeTomSelect(selectElement) {
    new TomSelect(selectElement, {
        placeholder: 'Select pack SKU...',
        sortField: {
            field: 'text',
            direction: 'asc'
        },
        dropdownParent: 'body'
    });
}

function addPackRow() {
    const tbody = document.querySelector('#packs-table tbody');
    const newRow = document.createElement('tr');
    newRow.className = 'pack-row';
    
    let optionsHtml = '<option value="">Select pack SKU...</option>';
    packOptions.forEach(function(pack) {
        optionsHtml += `<option value="${pack.id}">
            ${pack.label}
        </option>`;
    });
    
    newRow.innerHTML = `
        <td>
            <select name="configurations[${configIndex}][pack_item_id]" class="form-select pack-select" required>
                ${optionsHtml}
            </select>
        </td>
        <td>
            <div class="input-group">
                <input type="number" 
                    name="configurations[${configIndex}][qty_kg_per_pack]" 
                    class="form-control text-end" 
                    value="1.00" 
                    step="0.01" 
                    min="0.01" 
                    max="999999.99"
                    required
                    placeholder="e.g., 5.00">
                <span class="input-group-text">kg</span>
            </div>
            <small class="text-muted">Kg of ${kerupukKgName} needed for 1 pack</small>
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePackRow(this)">
                <i class="far fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    const newSelect = newRow.querySelector('.pack-select');
    initializeTomSelect(newSelect);
    
    configIndex++;
}

function removePackRow(button) {
    const tbody = document.querySelector('#packs-table tbody');
    const rows = tbody.querySelectorAll('.pack-row');
    
    if (rows.length <= 1) {
        alert('At least one pack SKU is required.');
        return;
    }
    
    button.closest('tr').remove();
}
</script>
@endpush

